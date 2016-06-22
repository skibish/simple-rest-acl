# Simple REST ACL
> Simplest ACL build, ever.

[![Build Status](https://travis-ci.org/skibish/simple-rest-acl.svg)](https://travis-ci.org/skibish/simple-rest-acl)
[![Latest Stable Version](https://poser.pugx.org/skibish/simple-rest-acl/v/stable.svg)](https://packagist.org/packages/skibish/simple-rest-acl)
[![Total Downloads](https://poser.pugx.org/skibish/simple-rest-acl/downloads.svg)](https://packagist.org/packages/skibish/simple-rest-acl)
[![Coverage Status](https://coveralls.io/repos/skibish/simple-rest-acl/badge.svg)](https://coveralls.io/r/skibish/simple-rest-acl)
[![License](https://poser.pugx.org/skibish/simple-rest-acl/license.svg)](https://packagist.org/packages/skibish/simple-rest-acl)

## How to install

Run `composer require skibish/simple-rest-acl`

## Idea and motivation

Configure ACL by routes and methods as simply as possible.

## How to use it

First, you will need to create `acl.yml` file. Which can look like this:

```yml
/users:
  roles: ['role1' ,'role2' ,'role3']
  GET: ['role1' ,'role2']
  POST: all
  PUT: none
```

In your code you start ACL as follows:

```php

$availableListOfRolesToUser = ['role1' ,'role2' ,'role3'];
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$acl = new \Skibish\SimpleRestAcl\ACL(__DIR__ . '/config/acl.yml', new \Skibish\SimpleRestAcl\Validator($availableListOfRolesToUser));

$acl->got($httpMethod, $uri)->verify();
```

And you are ready to go!

## acl.yml file configuration

File has following possibilities:

```yml
/users:                   -- route as for resource or regex (see explanation below).
  roles:                  -- array of roles available for current resource or 'public' string (mandatory).
  type:                   -- 'resource' (default, see explanation below) or 'strict' string. If strict is set - only if route is matched it will check methods.
  GET: ['role1' ,'role2'] -- method and array of roles that can access it
  POST: all               -- or string, that all roles defined in 'roles' will apply for current method
  PUT: none               -- or this route is not accessible with any role by current method
  DELETE: ['role3'] 
```

Better way to understand thing is using examples. 

## Examples

### Example #1
Assume, we have following configuration:
```yml
/photos:
  roles: ['role1' ,'role2' ,'role3']
  GET: ['role1' ,'role2']
  POST: all
  PUT: none
```

And we have request `GET /photos/12` and available role is `role1`. It will match, because default `type` is `resource`. If `type` is `resource` it will match following routes:
- /photos
- /photos/new
- /photos/{id}
- /photos/{id}/edit
- /photos/{id}

And in `GET` we specified array of two roles `role1` and `role2`. Available role is `role1` and it is in array. So, method `verify()` in this case will return `true`.

### Example #2
Assume, we have following configuration:
```yml
/strict/{route:\d+}:
  type: strict
  roles: [1, 2, 3]
  GET: [1]
```

Behind the scenes ACL uses [nikic/FastRoute](http://github.com/nikic/FastRoute) to match the routes. Thus you can use regex in route definition. But in this case **don't forget** to set `type` to `strict`.

In this case only routes that have digit after `/strict` part will match. If we pass `GET /strict/foo`, method `verify()` will return `false`. If `GET /strict/42` - it will be `true`.

## Options

Third parameter in `ACL` constructor is array of options. Currently there are two options:

- `cacheFile` - path to cache file. Example: `__DIR__.'/cache/acl-cache.php'`. This configuration will cache your configuration. If you need to update cache, just delete the cache file.
- `resourceRegex` - regex for `type` `resource`. By default regex is `[/{id:\d+|new}[/edit]]`. If you want it to match [RESTful Resource Controllers](https://laravel.com/docs/5.1/controllers#restful-resource-controllers), as example, overwrite this option with `[/{id:\d+|create}[/edit]]`.

Code snippet:

```php

$availableListOfRolesToUser = ['role1' ,'role2' ,'role3'];
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$acl = new \Skibish\SimpleRestAcl\ACL(__DIR__ . '/config/acl.yml', new \Skibish\SimpleRestAcl\Validator($availableListOfRolesToUser, [
    'cacheFile'     => __DIR__ . '/cache/acl-cache.php',
    'resourceRegex' => '[/{id:\d+|create}[/edit]]',
]));

$acl->got($httpMethod, $uri)->verify();
```

## Logging

If you need to log something from this library, you can use `PSR-3` compatible loggers.

```php

$availableListOfRolesToUser = ['role1' ,'role2' ,'role3'];
$httpMethod = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

$acl = new \Skibish\SimpleRestAcl\ACL(__DIR__ . '/config/acl.yml', new \Skibish\SimpleRestAcl\Validator($availableListOfRolesToUser));

$acl->setLogger(new Logger());

$acl->got($httpMethod, $uri)->verify();
```

## Missing roles

If you need to know, what roles are missing, use `$acl->getMissingRoles()`. It will return array of missing roles.

# Contribution

If you see, that something can be improved, feel free to submit a pull request.
