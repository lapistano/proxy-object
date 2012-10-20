<?php
/**
 * Class to create a proxy object to be used in a test szenario.
 *
 * Mainly the ProxyObjectBuilder provides a fluent interface for the ProxyObject.
 *
 * Copyright (c) 2010-2011, Bastian Feder <github@bastian-feder.de>.
 * All rights reserved.
 * @license    http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0, January 2004
 *             Licensed under the Apache License, Version 2.0 (the "License");
 *             you may not use this file except in compliance with the License.
 *             You may obtain a copy of the License at
 *
 *       http://www.apache.org/licenses/LICENSE-2.0
 *
 *   Unless required by applicable law or agreed to in writing, software
 *   distributed under the License is distributed on an "AS IS" BASIS,
 *   WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *   See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author     Bastian Feder <github@bastian-feder.de>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @link       https://github.com/lapistano/proxy-object
 * @package    Unittests
 * @subpackage ProxyObject
 */
namespace lapistano\ProxyObject;

/**
 * Implementation of the Builder pattern for Proxy objects.
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author     Bastian Feder <github@bastian-feder.de>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @link       https://github.com/lapistano/proxy-object
 * @package    Unittests
 * @subpackage ProxyObject
 */
class ProxyBuilder
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var array
     */
    protected $methods = array();

    /**
     * @var array
     */
    protected $properties = array();

    /**
     * @var string
     */
    protected $proxyClassName = '';

    /**
     * @var array
     */
    protected $constructorArgs = array();

    /**
     * @var boolean
     */
    protected $autoload = true;

    /**
     * @var boolean
     */
    protected $invokeOriginalConstructor = true;

    /**
     * @var string
     */
    protected $mockClassName;

    /**
     * @param string $className
     */
    public function __construct($className)
    {
        $this->className = $className;
    }

    /**
     * Creates a proxy object using a fluent interface.
     *
     * @return object Instance of the proxied class exposing the configured attributes and methods.
     */
    public function getProxy()
    {
        include_once(__DIR__ . '/Generator.php');

        $proxyClass = Generator::generate(
            $this->className, $this->methods, $this->properties, $this->proxyClassName, $this->autoload
        );

        $classname = $proxyClass['proxyClassName'];

        if (!empty($proxyClass['namespaceName'])) {
            $classname = $proxyClass['namespaceName'] . '\\' . $proxyClass['proxyClassName'];
        }

        if (!class_exists($classname, false)) {
            eval($proxyClass['code']);
        }

        if ($this->invokeOriginalConstructor && !interface_exists($this->className, $this->autoload)) {

            if (empty($this->constructorArgs)) {
                return new $classname();
            }

            $proxy = new \ReflectionClass($classname);

            return $proxy->newInstanceArgs($this->constructorArgs);
        }

        return $this->getInstanceOf($classname);
    }

    /**
     * Specifies the subset of methods to proxy. Default is to proxy all of them.
     *
     * @param  array $methods
     *
     * @return ProxyBuilder
     */
    public function setMethods(array $methods)
    {
        $this->methods = $methods;

        return $this;
    }

    /**
     * Specifies the subset of properties to expose. Default is to proxy all of them.
     *
     * @param  array $properties
     *
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function setProperties(array $properties)
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Specifies the arguments for the constructor.
     *
     * @param  array $args
     *
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function setConstructorArgs(array $args)
    {
        $this->constructorArgs = $args;

        return $this;
    }

    /**
     * Specifies the name for the proxy class.
     *
     * @param string $name
     *
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function setProxyClassName($name)
    {
        $this->mockClassName = $name;

        return $this;
    }

    /**
     * Suppresses the invocation of the original constructor.
     *
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function disableOriginalConstructor()
    {
        $this->invokeOriginalConstructor = false;

        return $this;
    }

    /**
     * Suppresses the use of class autoloading while creating the proxy object.
     *
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function disableAutoload()
    {
        $this->autoload = false;

        return $this;
    }

    /**
     * Provides an instance of the given class without invoking it's constructor
     *
     * @param string $classname
     *
     * @return object
     */
    protected function getInstanceOf($classname)
    {
        // As of PHP5.4 the reflection api provides a way to get an instance
        // of a class without invoking the constructor.
        if (method_exists('ReflectionClass', 'newInstanceWithoutConstructor')) {
            $reflectedClass = new \ReflectionClass($classname);

            return $reflectedClass->newInstanceWithoutConstructor();
        }

        // Use a trick to create a new object of a class
        // without invoking its constructor.
        return unserialize(
            sprintf(
                'O:%d:"%s":0:{}', strlen($classname), $classname
            )
        );
    }
}
