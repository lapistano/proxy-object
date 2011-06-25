===========
ProxyObject
===========
Initiated by Thomas Weinert back in 2008 I picked up his work and completed, extended, and tested it.
The outcome is this little library making it much easier to generate a proxy of your system under test (SUT).
Another thought on this library was, that it should be very easy to use if you know the way to mock classes and methods
in PHPUnit. Proxy-object has almost the same API, but does not change the behavior of the proxied class/method.
The only purpose is to expose hidden methods and memebers. 


Installation
============
Thanks to the feedback of beberlei the source is now PSR-0 compatible. There is no specific installation routine to be followed. 
Just clone or checkout the source into to your project and use it. 

Usecases
========

1. Exposing invisible Methods
-----------------------------
One of the main purpose of this library is to expose invisible (private or protected) methods to the SUT. 
To do so use just create a new ProxyBuilder object and pass the method to be exposed.

    $proxy = new \lapistano\ProxyObject();

    // generate and configure proxied object

    $proxiedObject = $proxy
        ->getProxyBuilder('myClass')
        ->setConstructorAgrs(array('Argument1', 'Argument2'))
        ->setMethods(array('myMethod'))
        ->getProxy();

    // invoke proxied method

    $proxieObject->myMethod();

2. Exposing invisible Members
-----------------------------
Another purpose of this library is to expose invisible members not reachable via a setter. This is to prevent you 
from writing setter methods just for the purpose of unit testing. 
Use the `setProperties()` method to archieve.

    $proxy = new \lapistano\ProxyObject();

    // generate and configure proxied object

    $proxiedObject = $proxy
        ->getProxyBuilder('myClass')
        ->setProperties(array('myMember'))
        ->getProxy();

    // invoke proxied method

    $proxieObject->myMember = 'another value';

Despite the fact that it is possible to expose private members by naming them in the setProperties array, generating a proxy object
without the property declaration will only expose protected members. This is because I am not a big fan of exposing to much from a 
class necessary. If someone thinks this should be changed, I would be more than happy to discuss this topic. 


3. Creating a proxied object without calling the constructor
------------------------------------------------------------
Sometimes it is necessary to supress the invokation of the defined constructor. 
Therefore I followed the API of PHPunits MockBuilder and added the `disableOriginalConstructor()` method.


    $proxy = new \lapistano\ProxyObject();

    // generate and configure proxied object

    $proxiedObject = $proxy
        ->getProxyBuilder('myClass')
        ->disableOriginalConstructor()
        ->getProxy();

    // invoke proxied method

    $proxieObject->myMember = 'another value';


Documentation
=============
Since there is a exhausting documentation of the API in the source code, I decided not to write a separate one.
Use phpDocumentor (http://phpdoc.org) to extract and generate your own documentation. 
I added a phpdoc.example.ini in the doc/config folder. Follow the instructions in the doc/config/README to setup 
the generation of the documentation.
  

Limitations
===========
Every powerful library has one limitation. If there is logic in the constructor of the class to be proxied
depending on the content of a mandatory parameter, the proxy generation will fail due to a not existing value of the 
constructor parameters. This is because if you want to expose invisible members the Relection API forces you to pass an 
instance of the class the members shall be opened for. So imagine you verify the content of a parameter to be set, it is 
not possible to automatically set the content to be verified -the proxy generation will fail.


 
