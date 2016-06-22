<?php

namespace Skibish\SimpleRestAcl\Tests;

use Skibish\SimpleRestAcl\ACL;
use Skibish\SimpleRestAcl\Validator;

class ACLTest extends \PHPUnit_Framework_TestCase {

    public function testAclConstructorThrowsException()
    {
        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException',
            'File for ACL was not found by path ohno');

        $acl = new ACL('ohno', new Validator());
    }

    public function testAclThrowsExceptionIfResourceNotFound()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]));

        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException',
            'Not found ACL rules for resource /dead');

        $acl->got($acl::METHOD_GET, '/dead')->verify();
    }

    public function testAclThrowsExceptionIfMethodNotFound()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]));

        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException',
            'Not found ACL rules for method DELETE of resource /users');

        $acl->got($acl::METHOD_DELETE, '/users')->verify();
    }

    public function testAclThrowsExceptionIfResourceRolesIsNotArray()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]));

        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException');

        $acl->got($acl::METHOD_GET, '/phantoms')->verify();
    }

    public function testAclThrowsExceptionIfMethodRolesIsNotArray()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]));

        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException');

        $acl->got($acl::METHOD_GET, '/dragulas')->verify();
    }

    public function testAclVerifiesTrue()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]));

        $this->assertTrue($acl->got($acl::METHOD_GET, '/users')->verify());
        $this->assertTrue($acl->got($acl::METHOD_POST, '/users')->verify());
        $this->assertTrue($acl->got($acl::METHOD_GET, '/zombies')->verify());

        $this->assertTrue($acl->got($acl::METHOD_GET, '/photos')->verify());
        $this->assertTrue($acl->got($acl::METHOD_GET, '/photos/new')->verify());
        $this->assertTrue($acl->got($acl::METHOD_POST, '/photos')->verify());
        $this->assertTrue($acl->got($acl::METHOD_GET, '/photos/42')->verify());
        $this->assertTrue($acl->got($acl::METHOD_GET, '/photos/42/edit')->verify());
        $this->assertTrue($acl->got($acl::METHOD_PUT, '/photos/42')->verify());
        $this->assertTrue($acl->got($acl::METHOD_DELETE, '/photos/42')->verify());
        $this->assertTrue($acl->got($acl::METHOD_GET, '/strict/42')->verify());
    }

    public function testAclVerifiesFalse()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([3]));

        $this->assertFalse($acl->got($acl::METHOD_GET, '/users')->verify());
        $this->assertFalse($acl->got($acl::METHOD_PUT, '/users')->verify());

        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([4]));
        $this->assertFalse($acl->got($acl::METHOD_POST, '/users')->verify());
    }

    public function testAclThrowsExceptionIfFileCantBeParsed()
    {
        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException');

        $acl = new ACL(__DIR__.'/test-bad.yml', new Validator([1]));
    }

    public function testGetMissingRoleOnResource()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([4]));
        $acl->got($acl::METHOD_GET, '/users')->verify();
        $this->assertEquals($acl->getMissingRoles(), [1,2,3]);
    }

    public function testGetMissingRolesOnMethod()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([3]));
        $acl->got($acl::METHOD_GET, '/users')->verify();
        $this->assertEquals($acl->getMissingRoles(), [1,2]);
    }

    public function testAclThrowsExceptionIfBadTypeIsSpecified()
    {
        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException');

        $acl = new ACL(__DIR__.'/test-bad-type.yml', new Validator([3]));
    }

    public function testAclWorksWithCash()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]), [
            'cacheFile' => __DIR__ . '/_data/cache.test',
        ]);

        $this->assertTrue($acl->got($acl::METHOD_GET, '/photos/new')->verify());
    }

    public function testAclThrowsExceptionIfCacheIsBad()
    {
        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException', 'Invalid cache file "' . __DIR__ . '/bad.cache"');

        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]), [
            'cacheFile' => __DIR__ . '/bad.cache',
        ]);
    }

    public function testChangeRegexForResource()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]), [
            'resourceRegex' => '[/{id:\d+|create}[/bubble]]',
        ]);

        $this->assertTrue($acl->got($acl::METHOD_GET, '/photos/create')->verify());
        $this->assertTrue($acl->got($acl::METHOD_GET, '/photos/12/bubble')->verify());

    }

    public function testGetMissingRolesIsEmpty()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', new Validator([1]));
        $acl->got($acl::METHOD_GET, '/users')->verify();
        $this->assertEquals($acl->getMissingRoles(), []);
    }

}
