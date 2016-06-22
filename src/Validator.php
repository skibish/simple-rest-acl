<?php


namespace Skibish\SimpleRestAcl;


use Skibish\SimpleRestAcl\Exceptions\AclException;

/**
 * Class Validator
 * @package Skibish\SimpleRestAcl
 */
class Validator
{
    /**
     * @var array
     */
    private $map;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string
     */
    private $method;

    /**
     * @var array
     */
    private $missingRoles;

    /**
     * @var array
     */
    private $currentUserRoles;

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
    const TYPE_RESOURCE = 'resource';

    /**
     * @var string
     */
    const TYPE_STRICT = 'strict';


    /**
     * @return array
     */
    public function getMissingRoles()
    {
        return $this->missingRoles;
    }

    /**
     * @param $uri
     * @return $this
     */
    public function setUri($uri)
    {
        $this->uri = $uri;

        return $this;
    }

    /**
     * @param $method
     * @return $this
     */
    public function setMethod($method)
    {
        $this->method = $method;
        
        return $this;
    }

    /**
     * @param array $map
     * @return $this
     */
    public function setMap($map)
    {
        $this->map = $map;
        
        return $this;
    }

    /**
     * Validator constructor.
     * @param array $currentUserRoles
     */
    public function __construct($currentUserRoles = [])
    {
        $this->currentUserRoles = $currentUserRoles;
    }

    /**
     * Check if user have access to resource
     *
     * @return bool
     */
    public function hasRoleAccess()
    {
        $roles = $this->map[$this->uri]['roles'];

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
    public function hasMethodAccess()
    {
        $roles = $this->map[$this->uri][$this->method];

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
     * Check type if it exists
     *
     * @param array $routeData - array with path rule description
     * @return bool - true if resource, false if strict
     * @throws AclException - if not correct type passed
     */
    public function isTypeResource($routeData)
    {
        if (!isset($routeData['type']) || (isset($routeData['type']) && $routeData['type'] === self::TYPE_RESOURCE)) {

            return true;
        } else if (isset($routeData['type']) && $routeData['type'] !== self::TYPE_STRICT) {
            throw new AclException("Type `{$routeData['type']}` is not allowed.");
        }

        return false;
    }

    /**
     * Clear validator from intermediate data
     */
    public function clear()
    {
        $this->missingRoles = [];
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