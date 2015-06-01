<?php

namespace Skibish\SimpleRestAcl;

use Skibish\SimpleRestAcl\Exceptions\AclException;
use Symfony\Component\Yaml\Exception\ParseException;
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
    const ROLE_PUBLIC = 'public';

    /**
     * @var string
     */
    const ACCESS_ALL = 'all';

    /**
     * @var string
     */
    const ACCESS_NONE = 'none';

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
     * ACL configuration
     * @var array
     */
    private $config;

    /**
     * List of available roles for current user
     * @var array
     */
    private $currentUserRoles;

    /**
     * Resource, example '/users'
     * @var string
     */
    private $resource;

    /**
     * GET, POST, PUT, DELETE etc.
     * @var string
     */
    private $method;

    /**
     * Array of missing roles
     * @var array
     */
    private $missingRoles;

    /**
     * Get missing roles
     *
     * @return array
     */
    public function getMissingRoles()
    {
        return $this->missingRoles;
    }

    /**
     * Constructor
     *
     * @param string $path - path to yml config file
     * @param array $currentUserRoles - array of roles, example: [1, 2, 3]
     * @throws AclException
     */
    public function __construct($path, array $currentUserRoles = [])
    {
        if (@file_get_contents($path) === FALSE) {
            throw new AclException('File for ACL was not found by path ' . $path);
        }

        try {
            $this->config = YAML::parse(file_get_contents($path));
        } catch (ParseException $e) {
            throw new AclException($e->getMessage());
        }

        $this->currentUserRoles = $currentUserRoles;
    }

    /**
     * @param string $method - example: 'GET'
     * @param string $resource - example: '/users'
     *
     * @return $this
     */
    public function got($method, $resource)
    {
        $this->method   = $method;
        $this->resource = $resource;

        return $this;
    }

    /**
     * Verify access for current user
     */
    public function verify()
    {
        if ($this->hasRoleAccess() && $this->hasMethodAccess()) {
            $this->missingRoles = [];

            return true;
        }

        return false;
    }

    /**
     * Check if user have access to resource
     *
     * @return bool
     * @throws AclException - if resource not found in ACL configuration
     */
    private function hasRoleAccess()
    {
        if (!isset($this->config[$this->resource])) {
            throw new AclException('Not found ACL rules for resource ' . $this->resource);
        }

        $roles = $this->config[$this->resource]['roles'];

        if ($roles === self::ROLE_PUBLIC) {

            return true;
        }

        $this->isArray($roles);

        $this->collectMissingRoles($roles);

        return count(array_intersect($this->currentUserRoles, $roles)) > 0;
    }

    /**
     * Check if user have method access to resource
     *
     * @return bool
     * @throws AclException
     */
    private function hasMethodAccess()
    {
        if (!isset($this->config[$this->resource][$this->method])) {
            throw new AclException('Not found ACL rules for method ' . $this->method . ' of resource ' . $this->resource);
        }

        $roles = $this->config[$this->resource][$this->method];

        if ($roles === self::ACCESS_ALL) {

            return true;
        }

        if ($roles === self::ACCESS_NONE) {

            return false;
        }

        $this->isArray($roles);

        $this->collectMissingRoles($roles);

        return count(array_intersect($this->currentUserRoles, $roles)) > 0;
    }

    /**
     * Check if roles are array
     *
     * @param $roles
     * @throws AclException
     */
    private function isArray($roles)
    {
        if (!is_array($roles)) {
            throw new AclException('Expected an array of roles, not a ' . json_encode($roles));
        }
    }

    /**
     * Collect missing roles
     *
     * @param $roles
     */
    private function collectMissingRoles($roles)
    {
        $this->missingRoles = [];
        foreach ($roles as $role) {
            if (!in_array($role, $this->currentUserRoles)) {
                $this->missingRoles[] = $role;
            }
        }
    }
}
