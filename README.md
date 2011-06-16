ProxyObject
===========
Initiated by Thomas Weinert back in 2008 I picked up his work and completed, extended, and tested it.
The outcome is this little library making it much easier to generate a proxy of your system under test (SUT).


Limitations
===========
Every powerful library has one limitation. If there is logic in the constructor of the class to be proxied
depending on the content of a mandatory parameter, the proxy generation will fail due to a not existing value of the 
constructor parameters. This is because if you want to expose invisible members the Relection API forces you to pass an 
instance of the class the members shall be opened for. So imagine you verify the content of a parameter to be set, it is 
not possible to automatically set the content to be verified -the proxy generation will fail.


 