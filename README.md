===========
ProxyObject
===========
Initiated by Thomas Weinert back in 2008 I picked up his work and completed, extended, and tested it.
The outcome is this little library making it much easier to generate a proxy of your system under test (SUT).
Another thought on this library was, that it should be very easy to use if you know the way to mock classes and methods
in PHPUnit. Proxy-object has almost the same API, but does not change the behavior of the proxied class/method.
The only purpose is to expose hidden (protected & private) methods and members. 


Installation
============
Thanks to the feedback of [beberlei](https://github.com/beberlei) the source is now PSR-0 compatible. 
There is no specific installation routine to be followed. Just clone or checkout the source into to your project 
and use it.
In case you don't use a PSR-0 compatible autoloader, you only have to add the `bootstrap.php` into your bootstrap or 
autoloader.

Usecases
========

1. Exposing invisible Methods
-----------------------------
One of the main purpose of this library is to expose invisible (private or protected) methods to the SUT. 
To do so use just create a new ProxyBuilder object and pass the method to be exposed.

    $proxy = new \lapistano\ProxyObject\ProxyBuilder('myClass');

    // generate and configure proxied object

    $proxiedObject = $proxy
        ->setConstructorAgrs(array('Argument1', 'Argument2'))
        ->setMethods(array('myMethod'))
        ->getProxy();

    // invoke proxied method

    $proxiedObject->myMethod();

2. Exposing invisible Members
-----------------------------
Another purpose of this library is to expose invisible members not reachable via a setter. This is to prevent you 
from writing setter methods just for the purpose of unit testing. 
Use the `setProperties()` method to archieve.

    $proxy = new \lapistano\ProxyObject\ProxyBuilder('myClass');

    // generate and configure proxied object

    $proxiedObject = $proxy
        ->setProperties(array('myMember'))
        ->getProxy();

    // change content proxied member

    $proxiedObject->myMember = 'another value';

Despite the fact that it is possible to expose private members by naming them in the setProperties array, generating a 
proxy object without the property declaration will only expose protected members. This is because I am not a big fan of 
exposing to much from a class necessary. If someone thinks this should be changed, I would be more than happy to 
discuss this topic. 


3. Creating a proxied object without calling the constructor
------------------------------------------------------------
Sometimes it is necessary to supress the invokation of the defined constructor. 
Therefore I followed the API of PHPunits MockBuilder and added the `disableOriginalConstructor()` method.


    $proxy = new \lapistano\ProxyObject\ProxyBuilder('myClass');

    // generate and configure proxied object

    $proxiedObject = $proxy
        ->disableOriginalConstructor()
        ->getProxy();

    // change value of proxied member

    $proxiedObject->myMember = 'another value';


Documentation
=============
Since there is a exhausting documentation of the API in the source code, I decided not to write a separate one.
Use phpDocumentor (http://phpdoc.org) to extract and generate your own documentation. 
I added a phpdoc.example.ini in the doc/config folder. Follow the instructions in the doc/config/README to setup 
the generation of the documentation.

Limitations
===========
As you might expect there are also some limitations this library has to deal with. This limitations are not introduced
by this implementation, but are limitations which caome from PHP. So it is not possible to expose methods marked as 
final or static.