===========
ProxyObject
===========
Initiated by Thomas Weinert back in 2008 I picked up his work and completed, extended, and tested it.
The outcome is this little library making it much easier to generate a proxy of your system under test (SUT).
Another thought on this library was, that it should be very easy to use if you know the way to mock classes and methods
in PHPUnit. Proxy-object has almost the same API, but does not change the behavior of the proxied class/method.
The only purpose is to expose hidden (protected & private) methods and members. 

Current travis status: [![Build Status](https://secure.travis-ci.org/lapistano/proxy-object.png?branch=master)](http://travis-ci.org/lapistano/proxy-object)

Installation
============
Thanks to the feedback of [beberlei](https://github.com/beberlei) the source is now PSR-0 compatible. 
There is no specific installation routine to be followed. Just clone or checkout the source into to your project 
and use it.
In case you don't use a [PSR-0](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) compatible autoloader, you only have to add the `bootstrap.php` into your bootstrap or 
autoloader.

Composer
--------
Add the following lines to your `composer.json` file and update your project's composer installation.

```json
{
    "require-dev": {
        "lapistano/proxy-object": "2.*"
    }
}
```

This composer configuration will checkout the sources tagged as the 2nd release. In case your want the 'cutting eadge' version
replace '2.*' by 'dev-master'. But be alarmed that this might be broken sometimes.

**NOTE:**
In case you do not know what this means the [composer project website](http://getcomposer.org) is a good place to start.


Github
------
Thus I recommend the composer way to make proxy-object a dependency to your project. 
The sources are also available via github. Just clone it as you might be familiar with.

```bash
$ git clone git://github.com/lapistano/proxy-object.git
```

Usecases
========

1. Exposing invisible Methods
-----------------------------
One of the main purpose of this library is to expose invisible (private or protected) methods to the SUT. 
To do so use just create a new ProxyBuilder object and pass the method to be exposed.

```php

$proxy = new \lapistano\ProxyObject\ProxyBuilder('myClass');

// generate and configure proxied object
$proxiedObject = $proxy
    ->setConstructorArgs(array('Argument1', 'Argument2'))
    ->setMethods(array('myMethod'))
    ->getProxy();

// invoke proxied method
$proxiedObject->myMethod();

```

2. Exposing invisible Members
-----------------------------
Another purpose of this library is to expose invisible members not reachable via a setter. This is to prevent you 
from writing setter methods just for the purpose of unit testing. 
Use the `setProperties()` method to archieve.

```php
$proxy = new \lapistano\ProxyObject\ProxyBuilder('myClass');

// generate and configure proxied object
$proxiedObject = $proxy
    ->setProperties(array('myMember'))
    ->getProxy();

// change content proxied member
$proxiedObject->myMember = 'another value';

```

Despite the fact that it is possible to expose private members by naming them in the setProperties array, generating a 
proxy object without the property declaration will only expose protected members. This is because I am not a big fan of 
exposing too much from a class. If someone thinks this should be changed, I would be more than happy to discuss this topic. 

3. Creating a proxied object without calling the constructor
------------------------------------------------------------
Sometimes it is necessary to supress the invokation of the defined constructor. 
Therefore I followed the API of PHPunits MockBuilder and added the `disableOriginalConstructor()` method.

```php

$proxy = new \lapistano\ProxyObject\ProxyBuilder('myClass');

// generate and configure proxied object
$proxiedObject = $proxy
    ->disableOriginalConstructor()
    ->getProxy();

// change value of proxied member
$proxiedObject->myMember = 'another value';

```

Ease access to the proxy-object in your test suite
===================================================
Since I am really lazy ;) and I really like convenience I extended the `PHPUnit_Framework_TestCase` class and 
added the following method.

```php

/**
 * Provides a ProxyBuilder object.
 *
 * @param string $classname
 * @return lapistano\ProxyObject\ProxyBuilder
 */
protected function getProxyBuilder($classname) {
    return new \lapistano\ProxyObject\ProxyBuilder($classname);
}

```

Every of your test cases should now extend your own extended test case class so you can create a new proxy builder 
by just calling `$this->getProxyBuilder('\\my\\namespace\\myclass');`. Used in one of the examples above it will look like this.

```php

// generate and configure proxied object
$proxiedObject = $this->getProxyBuilder('myClass')
    ->disableOriginalConstructor()
    ->getProxy();

// change value of proxied member
$proxiedObject->myMember = 'another value';

```


Documentation
=============
Since there is a exhausting documentation of the API in the source code, I decided not to write a separate one.
Use [phpDocumentor](http://phpdoc.org) to extract and generate your own documentation. 
I added a phpdoc.example.ini in the doc/config folder. Follow the instructions in the `doc/config/README` to setup 
the generation of the documentation.

Limitations
===========
As you might expect there are also some limitations this library has to deal with. This limitations are not introduced
by this implementation, but are limitations which come from PHP. So it is not possible to expose methods marked as 
final or static.

Future stuff
============
- Improve error messages (e.g. by telling why a method/member could not be exposed)
