# Simple REST ACL
> Simplest ACL build, ever.

[![Build Status](https://travis-ci.org/skibish/simple-rest-acl.svg)](https://travis-ci.org/skibish/simple-rest-acl)
[![Latest Stable Version](https://poser.pugx.org/skibish/simple-rest-acl/v/stable.svg)](https://packagist.org/packages/skibish/simple-rest-acl)
[![Total Downloads](https://poser.pugx.org/skibish/simple-rest-acl/downloads.svg)](https://packagist.org/packages/skibish/simple-rest-acl)
[![License](https://poser.pugx.org/skibish/simple-rest-acl/license.svg)](https://packagist.org/packages/skibish/simple-rest-acl)

## How to install

Just run ``` composer require skibish/simple-rest-acl ```

## Idea

To understand, you need to know, a liitle bit, what is REST about.

Idea is simple (got ya!), you have recources, CRUD, user roles and you have such neat YAML file:

```yaml

/zombies:
  roles: [1, 2, 3]
  GET: all
  POST: [1, 2]
 
/humans:
  roles: public
  GET: all
  POST: none

```

Simple, isn't it?

## Dive in (explanation)

We have resource **zombies**. Access to this resource is given only to role 1, 2 and 3. Everyone else will be kicked off.
- If we have **GET** request, we will accept it from users who have roles 1,2,3 (so, **all** roles).
- If we have **POST** request, we will only give access to users with roles 1 and 2

For **humans** there is another story.
- **Everyone** can access this resource. Because it is **public**
- **GET** request is opened to **all** and **none** has access to POST request

Simple. Let's move on!

## How it works

In code it work like this:

```php

$acl = new ACL('path/to/your/acl.yml', $userRoles);

$acl->got('GET', '/humans')->verify(); // true or false

```

## Exceptions

There is one exception to handle, it is ``` AclExcpetion ``` (captain obvious, anyone?). If something went wrong, it will be thrown. So, catch it.
