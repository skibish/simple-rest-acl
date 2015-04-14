<?php

namespace Skibish\SimpleRestAcl\Tests;

use Skibish\SimpleRestAcl\ACL;

class ACLTest extends \PHPUnit_Framework_TestCase {

    public function testAclConstructorThrowsException()
    {
        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException',
            'File for ACL was not found by path ohno');

        $acl = new ACL('ohno');
    }

    public function testAclThrowsExceptionIfResourceNotFound()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', [1]);

        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException',
            'Not found ACL rules for resource /dead');

        $acl->got($acl::METHOD_GET, '/dead')->verify();
    }

    public function testAclThrowsExceptionIfMethodNotFound()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', [1]);

        $this->setExpectedException('\Skibish\SimpleRestAcl\Exceptions\AclException',
            'Not found ACL rules for method DELETE of resource /users');

        $acl->got($acl::METHOD_DELETE, '/users')->verify();
    }

    public function testAclVerifiesTrue()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', [1]);

        $this->assertTrue($acl->got($acl::METHOD_GET, '/users')->verify());
        $this->assertTrue($acl->got($acl::METHOD_POST, '/users')->verify());
    }

    public function testAclVerifiesFalse()
    {
        $acl = new ACL(__DIR__.'/test-acl.yml', [3]);

        $this->assertFalse($acl->got($acl::METHOD_GET, '/users')->verify());
        $this->assertFalse($acl->got($acl::METHOD_PUT, '/users')->verify());

        $acl = new ACL(__DIR__.'/test-acl.yml', [4]);
        $this->assertFalse($acl->got($acl::METHOD_POST, '/users')->verify());
    }
}
