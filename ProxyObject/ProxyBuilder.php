<?php
/**
 * Class to create a proxy object to be used in a test szenario.
 *
 * Mainly the ProxyObjectBuilder provides a fluent interface for the ProxyObject.
 *
 * Copyright (c) 2010-2011, Bastian Feder <github@bastian-feder.de>.
 * All rights reserved.
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License Version 2.0, January 2004
 *   Licensed under the Apache License, Version 2.0 (the "License");
 *   you may not use this file except in compliance with the License.
 *   You may obtain a copy of the License at
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
 * @author Bastian Feder <github@bastian-feder.de>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 * @package Unittests
 * @subpackage ProxyObject
 */
namespace lapistano\ProxyObject;

/**
 * Implementation of the Builder pattern for Proxy objects.
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author Bastian Feder <github@bastian-feder.de>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 * @package Unittests
 * @subpackage ProxyObject
 */
class ProxyBuilder
{
    /**
     * @var \lapistano\ProxyObject\ProxyObject
     */
    protected $proxyObject;

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
    protected $originalConstructor = true;

    /**
     * @var boolean
     */
    protected $originalClone = true;

    /**
     * @param \lapistano\ProxyObject\ProxyObject $proxyObject
     * @param string $className
     */
    public function __construct(ProxyObject $proxyObject, $className)
    {
        $this->proxyObject  = $proxyObject;
        $this->className = $className;
    }

    /**
     * Creates a proxy object using a fluent interface.
     *
     * @return object Instance of the proxied class exposing the configured attributes and methods.
     */
    public function getProxy()
    {
        $a =  $this->proxyObject->getProxy(
            $this->className,
            $this->methods,
            $this->properties,
            $this->constructorArgs,
            $this->proxyClassName,
            $this->originalConstructor,
            $this->autoload
        );

        return $a;
    }

    /**
     * Specifies the subset of methods to proxy. Default is to proxy all of them.
     *
     * @param  array $methods
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
        $this->originalConstructor = false;

        return $this;
    }

    /**
     * Suppresses the invocation of the original clone constructor.
     *
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function disableOriginalClone()
    {
        $this->originalClone = false;

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
}