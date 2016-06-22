<?php

namespace Skibish\SimpleRestAcl;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use Skibish\SimpleRestAcl\Exceptions\AclException;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ACL
 *
 * @package Skibish\SimpleRestAcl
 */
class ACL
{
    /**
     * @var string
     */
    const METHOD_GET = 'GET';

    /**
     * @var string
     */
    const METHOD_POST = 'POST';

    /**
     * @var string
     */
    const METHOD_PUT = 'PUT';

    /**
     * @var string
     */
    const METHOD_DELETE = 'DELETE';

    /**
     * List of all available methods
     * 
     * @var array
     */
    private $availableMethods = [
        self::METHOD_GET,
        self::METHOD_POST,
        self::METHOD_PUT,
        self::METHOD_DELETE,
    ];

    /**
     * Resource regex expression, that is added to the end (/photos has type resource, thus will be /photos[/{id:\d+|new}[/edit]])
     * You can overwrite it, but BE CAREFUL
     *
     * @var string
     */
    private $resourceRegex = '[/{id:\d+|new}[/edit]]';

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var Dispatcher
     */
    private $dispatcher;

    /**
     * ACL configuration
     * @var array
     */
    private $config;

    /**
     * Resource, example '/users'
     * @var string
     */
    private $uri;

    /**
     * GET, POST, PUT, DELETE etc.
     * @var string
     */
    private $method;

    /**
     * Route information array
     * @var array
     */
    private $routeInfo;

    public function getMissingRoles()
    {
        return $this->validator->getMissingRoles();
    }

    /**
     * Constructor
     *
     * @param string $path - path to yml config file
     * @param Validator $validator - validator object with available roles, example: [1, 2, 3]
     * @param array $options - array of options. Currently available: cacheFile (path), resourceRegex
     * @throws AclException
     */
    public function __construct($path, Validator $validator, $options = [])
    {
        $this->validator = $validator;

        try {
            // check if resourceRegex is present
            if (isset($options['resourceRegex'])) {
                $this->resourceRegex = $options['resourceRegex'];
            }

            // create anonymous function to build rules
            $buildRulesData = function (RouteCollector $r) {
                foreach ($this->config as $regex => $routeData) {
                    $routePart = '';
                    if ($this->validator->isTypeResource($routeData)) {
                        $routePart = $this->resourceRegex;
                    }

                    foreach ($routeData as $method => $data) {
                        if (in_array($method, $this->availableMethods)) {
                            $r->addRoute($method, "$regex$routePart", [$regex => $routeData]);
                        }
                    }
                }
            };

            // if cache file option is present, then use cache.
            if (isset($options['cacheFile'])) {
                if (!file_exists($options['cacheFile'])) {
                    $this->config = $this->readConfigurationFile($path);
                }

                $this->dispatcher = \FastRoute\cachedDispatcher($buildRulesData, $options);
            } else {
                $this->config = $this->readConfigurationFile($path);

                $this->dispatcher = \FastRoute\simpleDispatcher($buildRulesData);
            }
        } catch (\Exception $e) {
            throw new AclException($e->getMessage());
        }
    }

    /**
     * @param string $method - example: 'GET'
     * @param string $uri - example: '/users', '/some/{path}/[0-9]'
     * @return $this
     * @throws AclException
     */
    public function got($method, $uri)
    {
        $routeInfo = $this->dispatcher->dispatch($method, $uri);

        $this->routeInfo = $routeInfo;
        $this->method    = $method;
        $this->uri       = $uri;

        return $this;
    }

    /**
     * Verify access for current user
     */
    public function verify()
    {
        switch ($this->routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                throw new AclException('Not found ACL rules for resource ' . $this->uri);
            case Dispatcher::METHOD_NOT_ALLOWED:
                throw new AclException('Not found ACL rules for method ' . $this->method . ' of resource ' . $this->uri);
            case Dispatcher::FOUND:
                $this->validator
                    ->setMap($this->routeInfo[1])
                    ->setUri(array_keys($this->routeInfo[1])[0])
                    ->setMethod($this->method);
                break;
        }

        if ($this->validator->hasRoleAccess() && $this->validator->hasMethodAccess()) {
            $this->validator->clear();

            return true;
        }

        return false;
    }

    /**
     * Read configuration and return array
     *
     * @param string $path - read configuration file
     * @return mixed
     * @throws AclException
     */
    private function readConfigurationFile($path)
    {
        if (@file_get_contents($path) === FALSE) {
            throw new AclException('File for ACL was not found by path ' . $path);
        }

        return Yaml::parse(file_get_contents($path));
    }
}
