<?php
/**
 * Class to create a proxy object to be used in a test szenario
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
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author Bastian Feder <github@bastian-feder.de>
 * @author Thomas Weinert <thomas@weinert.info>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 * @package Unittests
 * @subpackage ProxyObject
 */
namespace lapistano\ProxyObject;


/**
 * The proxy class wrapps the original class to expose protected or private methods or attributes.
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author Bastian Feder <github@bastian-feder.de>
 * @author Thomas Weinert <thomas@weinert.info>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-objects
 * @package Unittests
 * @subpackage ProxyObject
 *
 */
class ProxyObject
{
    /**
     * Wraps the class identified by its classname to expose invisible methods and attributes.
     *
     * @param string $originalClassName Name of the class to be proxied.
     * @param array $methods List of methodnames to be exposed.
     * @param array $properties List of properynames to be exposed.
     * @param array $arguments List of arguments to be passed to the constructor of the wrapped class.
     * @param string $proxyClassName Name to be used for the reflected class.
     * @param boolean $callOriginalConstructor
     * @param boolean $callAutoload Switch to run the autoloader.
     *
     * @return object Instance of the proxied class exposing the configured attributes and methods.
     */
    public function getProxy($originalClassName, array $methods = null, array $properties = null,
                             array $arguments = array(), $proxyClassName = '', $callOriginalConstructor = true,
                             $callAutoload = false)
    {
        include_once(__DIR__.'/Generator.php');

        $proxyClass = Generator::generate(
            $originalClassName, $methods, $properties, $proxyClassName, $callAutoload
        );

        if (!empty($proxyClass['namespaceName'])) {
            $classname = $proxyClass['namespaceName'].'\\'.$proxyClass['proxyClassName'];
        } else {
            $classname = $proxyClass['proxyClassName'];
        }

        if (!class_exists($classname, false)) {
            eval($proxyClass['code']);
        }

        if ($callOriginalConstructor
            && !interface_exists($originalClassName, $callAutoload)) {

            if (empty($arguments)) {
                return new $classname();
            } else {
                $proxy = new \ReflectionClass($classname);
                return $proxy->newInstanceArgs($arguments);
            }
        } else {
            return $this->getInstanceOf($classname);
        }
    }

    /**
     * Returns a builder object to create proxy objects using a fluent interface.
     *
     * @param  string $className
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function getProxyBuilder($className)
    {
        return new ProxyBuilder(
            $this, $className
        );
    }

    /**
     * Provides an instance of the given class without invoking it's constructor
     *
     * @param string $classname
     * @return object 
     */
    protected function getInstanceOf($classname) 
    {
        // As of PHP5.4 the reflection api provides a way to get an instance 
        // of a class without invoking the constructor.
        if (method_exists('ReflectionClass', 'newInstanceWithoutConstructor')) {
            $class = new \ReflectionClass($classname);
            return $class->newInstanceWithoutConstructor();
        }

        // Use a trick to create a new object of a class
        // without invoking its constructor.
        return unserialize(
            sprintf(
                'O:%d:"%s":0:{}',
                strlen($classname),
                $classname
            )
        );
    }
}
