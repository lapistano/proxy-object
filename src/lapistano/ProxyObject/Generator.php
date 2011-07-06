<?php
/**
 * Class used by the ProxyObject to generate the actual proxy pbject.
 *
 * NOTICE:
 * This class should not be used directly. Always use ProxyObject or ProxyObjectBuilder to
 * create a proxy of your class.
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
 * @author Thomas Weinert <thomas@weinert.info>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 * @package Unittests
 * @subpackage ProxyObject
 */

namespace lapistano\ProxyObject;

/**
 * Path to this module.
 * @var string
 */
$modulePath = __DIR__;

if (version_compare(\PHPUnit_Runner_Version::id(), '3.5', '<')) {
    include_once('PHPUnit/Util/Class.php');
    include_once('PHPUnit/Util/Filter.php');
    include_once('PHPUnit/Framework/Exception.php');
}

/**
 * Class used by the ProxyObject to generate the actual proxy pbject.
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author Bastian Feder <github@bastian-feder.de>
 * @author Thomas Weinert <thomas@weinert.info>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 * @package Unittests
 * @subpackage ProxyObject
 *
 */
class Generator
{
    /**
     * Contain elements worth caching.
     * @var array
     */
    protected static $cache = array();

    /**
     * List of methods not to be reflected.
     * @var array
     */
    protected static $blacklistedMethodNames = array(
        '__clone' => true,
        '__destruct' => true,
        'abstract' => true,
        'and' => true,
        'array' => true,
        'as' => true,
        'break' => true,
        'case' => true,
        'catch' => true,
        'class' => true,
        'clone' => true,
        'const' => true,
        'continue' => true,
        'declare' => true,
        'default' => true,
        'do' => true,
        'else' => true,
        'elseif' => true,
        'enddeclare' => true,
        'endfor' => true,
        'endforeach' => true,
        'endif' => true,
        'endswitch' => true,
        'endwhile' => true,
        'extends' => true,
        'final' => true,
        'for' => true,
        'foreach' => true,
        'function' => true,
        'global' => true,
        'goto' => true,
        'if' => true,
        'implements' => true,
        'interface' => true,
        'instanceof' => true,
        'namespace' => true,
        'new' => true,
        'or' => true,
        'private' => true,
        'protected' => true,
        'public' => true,
        'static' => true,
        'switch' => true,
        'throw' => true,
        'try' => true,
        'use' => true,
        'var' => true,
        'while' => true,
        'xor' => true
    );

    /**
     * Gets the data to be used for the actual reflection.
     *
     * If the class has already been reflected in the same configuration
     * it will be fetched from the local cache.
     *
     * @param  string  $originalClassName Name of the class to be reflected.
     * @param  array   $methods List of methods to be exposed.
     * @param  string  $proxyClassName Name to be used for the reflected class.
     * @param boolean $callAutoload Switch to run the autoloader.
     * @return array The data to be used for the actual reflection.
     */
    public static function generate($originalClassName, array $methods = null, array $properties = null,
                                    $proxyClassName = '', $callAutoload = false)
    {

        if ($proxyClassName == '') {
            $key = md5(
                $originalClassName.
                serialize($methods).
                serialize($properties)
            );

            if (isset(self::$cache[$key])) {
                return self::$cache[$key];
            }
        }

        $proxy = self::generateProxy(
            $originalClassName,
            $methods,
            $properties,
            $proxyClassName,
            $callAutoload
        );

        if (isset($key)) {
            self::$cache[$key] = $proxy;
        }

        return $proxy;
    }

    /**
     * Gets the arguments the proxied method expectes.
     *
     * @param \ReflectionMethod $method
     * @return array List of parameters to be passed to the proxied method.
     */
    public static function getMethodCallParameters($method)
    {
        $parameters = array();
        foreach ($method->getParameters() as $i => $parameter) {
            $parameters[] = '$'.$parameter->getName();
        }
        return join(', ', $parameters);
    }

    /**
     * Generates the data to be used for the actual reflection.
     *
     * @param  string  $originalClassName Name of the class to be reflected.
     * @param  array   $methods List of methods to be exposed.
     * @param  array   $properties List of properties to be exposed.
     * @param  string  $proxyClassName Name to be used for the reflected class.
     * @param boolean $callAutoload Switch to run the autoloader.
     * @return array The data to be used for the actual reflection.
     */
    protected static function generateProxy($originalClassName, array $methods = null,
                                            array $properties = null, $proxyClassName = '',
                                            $callAutoload = false)
    {
        $templateDir = __DIR__.DIRECTORY_SEPARATOR.'Generator'.DIRECTORY_SEPARATOR;
        $classTemplate = self::createTemplateObject(
            $templateDir.'proxied_class.tpl'
        );

        $proxyClassName = self::generateProxyClassName(
            $originalClassName, $proxyClassName
        );

        if (interface_exists($proxyClassName['fullClassName'], $callAutoload)) {
            throw new \PHPUnit_Framework_Exception(
                sprintf(
                    '"%s" is an interface.',
                    $proxyClassName['fullClassName']
                )
            );
        }

        if (!class_exists($proxyClassName['fullClassName'], $callAutoload)) {
            throw new \PHPUnit_Framework_Exception(
                sprintf(
                    'Class "%s" does not exists.',
                    $proxyClassName['fullClassName']
                )
            );
        }

        $class = new \ReflectionClass($proxyClassName['fullClassName']);

        if ($class->isFinal()) {
            throw new \PHPUnit_Framework_Exception(
                sprintf(
                    'Class "%s" is declared "final". Cannot create proxy.',
                    $proxyClassName['fullClassName']
                )
            );
        }

        if (!empty($proxyClassName['namespaceName'])) {
            $prologue = 'namespace '.$proxyClassName['namespaceName'].";\n\n";
        }

        $classTemplate->setVar(
            array(
                'prologue' => isset($prologue) ? $prologue : '',
                'class_declaration' => $proxyClassName['proxyClassName'].' extends '.$originalClassName,
                'methods' => self::getProxiedMethods($proxyClassName['fullClassName'], $class, $methods),
                'properties' => self::getProxiedProperties($proxyClassName['fullClassName'], $class, $properties),
            )
        );

        return array(
            'code' => $classTemplate->render(),
            'proxyClassName' => $proxyClassName['proxyClassName'],
            'namespaceName' => $proxyClassName['namespaceName']
        );
    }

    /**
     * Generate string representing the set of properties to be reflected.
     *
     * @param string $fullClassName
     * @param \ReflectionClass $class
     * @param array $properties
     * @return string
     */
    protected static function getProxiedProperties($fullClassName, \ReflectionClass $class, array $properties = null)
    {
        $proxiedProperties = '';
        $templateDir = __DIR__.DIRECTORY_SEPARATOR.'Generator'.DIRECTORY_SEPARATOR;
        $proxyProperties = $class->getProperties(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PRIVATE);

        if (empty($properties)) {
            foreach ($proxyProperties as $property) {
                $proxiedProperties .= self::generateProxiedPropertyDefinition($templateDir, $property, $class);
            }
        } else {
            foreach ($properties as $propertyName) {
                if ($class->hasProperty($propertyName)) {
                    $property = $class->getProperty($propertyName);
                    if (self::canProxyProperty($property)) {
                        $proxiedProperties .= self::generateProxiedPropertyDefinition($templateDir, $property, $class);
                    } else {
                        throw new \PHPUnit_Framework_Exception(
                            sprintf(
                                'Can not proxy property "%s" of class "%s".',
                                $propertyName,
                                $fullClassName
                            )
                        );
                    }
                } else {
                    throw new \PHPUnit_Framework_Exception(
                        sprintf(
                            'Class "%s" has no protected or private property "%s".',
                            $fullClassName,
                            $propertyName
                        )
                    );
                }
            }
        }
        return $proxiedProperties;
    }

    /**
     * Generates the definition of a method to be proxied.
     *
     * @param string $templateDir Location of the templates to be used to create the proxy.
     * @param \ReflectionProperty $property Name of the property to be reflected.
     * @param \ReflectionClass $class Name of the class to be reflected.
     * @return array Information about the method to be proxied.
     */
    protected static function generateProxiedPropertyDefinition($templateDir, \ReflectionProperty $property,
                                                                \ReflectionClass $class)
    {
        $template = self::createTemplateObject(
            $templateDir . 'proxied_property.tpl'
        );

        $property->setAccessible(true);
        $value = $property->getValue(self::getInstance($class));

        if (is_array($value)) {
            $value = ' = array()';
        } else if (is_string($value)) {
            $value = sprintf(' = \'%s\'', $value);
        } else if (is_object($value)) {
            $value = '';
        } else if (is_scalar($value)) {
            $value = sprintf(' = %f', $value);
        }

        $template->setVar(
            array(
                'keyword'        => $property->isStatic() ? 'static ' : '',
                'property_name'  => $property->getName(),
                'property_value' => $value
            )
        );
        return $template->render();
    }

    /**
     * Creates an instance of the current proxied class.
     *
     * @param \ReflectionClass $class
     * @return object
     */
    protected static function getInstance(\ReflectionClass $class)
    {
        if ($constructor = $class->getConstructor()) {
            $parameters = $constructor->getParameters();

            if (!empty($parameters)) {
                $args = array();

                foreach ($parameters as $parameter) {
                    if ($parameter->isOptional()) {
                        continue;
                    }
                    if ($parameter->isArray()) {
                        $args[] = array();
                        continue;
                    }

                    $classParameter = $parameter->getClass();
                    if ($classParameter) {
                        $args[] = self::getInstance($classParameter);
                        continue;
                    }
                    $args[] = '';
                }
                return $class->newInstanceArgs($args);
            }
        }
        return $class->newInstance();
    }

    /**
     * Generate string representing the set of methods to be reflected.
     *
     * @param array $fullClassName
     * @param \ReflectionClass $class
     * @param array $methods
     * @return array Information about the method to be proxied.
     *
     * @throws \PHPUnit_Framework_Exception
     */
    protected static function getProxiedMethods($fullClassName, \ReflectionClass $class, array $methods = null)
    {
        $proxiedMethods = '';
        $templateDir = __DIR__.DIRECTORY_SEPARATOR.'Generator'.DIRECTORY_SEPARATOR;

        if (is_array($methods) && count($methods) > 0) {
            foreach ($methods as $methodName) {
                if ($class->hasMethod($methodName)) {
                    $method = $class->getMethod($methodName);
                    if (self::canProxyMethod($method)) {
                        $proxyMethods[] = $method;
                    } else {
                        throw new \PHPUnit_Framework_Exception(
                            sprintf(
                                'Can not proxy method "%s" of class "%s".',
                                $methodName,
                                $fullClassName
                            )
                        );
                    }
                } else {
                    throw new \PHPUnit_Framework_Exception(
                        sprintf(
                            'Class "%s" has no protected method "%s".',
                            $fullClassName,
                            $methodName
                        )
                    );
                }
            }
        } else {
            $proxyMethods = $class->getMethods(\ReflectionMethod::IS_PROTECTED);
            if (!(is_array($proxyMethods) && count($proxyMethods) > 0)) {
                throw new \PHPUnit_Framework_Exception(
                    sprintf(
                        'Class "%s" has no protected methods.',
                        $fullClassName
                    )
                );
            }
        }

        foreach ($proxyMethods as $method) {
            $proxiedMethods .= self::generateProxiedMethodDefinition($templateDir, $method);
        }
        return $proxiedMethods;
    }

    /**
     * Generates a unique name to be used to identify the created proxyclass.
     *
     * @param  string $originalClassName Name of the class to be reflected.
     * @param  string $proxyClassName Name to be used for the reflected class.
     * @return array Information of the class to be reflected.
     */
    protected static function generateProxyClassName($originalClassName, $proxyClassName)
    {
        $classNameParts = explode('\\', $originalClassName);

        if (count($classNameParts) > 1) {
            $originalClassName = array_pop($classNameParts);
            $namespaceName = implode('\\', $classNameParts);
            $fullClassName = $namespaceName.'\\'.$originalClassName;

            // eval does identifies namespaces with leading backslash as constant.
            $namespaceName = (0 === stripos($namespaceName, '\\') ? substr($namespaceName, 1) : $namespaceName);

        } else {
            $namespaceName = '';
            $fullClassName = $originalClassName;
        }

        if ($proxyClassName == '') {
            do {
                $proxyClassName = 'Proxy_'.$originalClassName.'_'.substr(md5(microtime()), 0, 8);
            } while (class_exists($proxyClassName, false));
        }

        return array(
            'proxyClassName' => $proxyClassName,
            'className' => $originalClassName,
            'fullClassName' => $fullClassName,
            'namespaceName' => $namespaceName
        );
    }

    /**
     * Generates the definition of a method to be proxied.
     *
     * @param string $templateDir Location of the templates to be used to create the proxy.
     * @param \ReflectionMethod $method Name of the method to be reflected.
     * @return array Information about the method to be proxied.
     */
    protected static function generateProxiedMethodDefinition($templateDir, \ReflectionMethod $method)
    {
        if ($method->returnsReference()) {
            $reference = '&';
        } else {
            $reference = '';
        }

        $template = self::createTemplateObject(
            $templateDir . 'proxied_method.tpl'
        );

        $template->setVar(
            array(
                'arguments_declaration' => self::getArgumentDeclaration($method),
                'arguments' => self::getMethodCallParameters($method),
                'method_name' => $method->getName(),
                'reference'   => $reference
            )
        );
        return $template->render();
    }

    /**
     * Adds prefix for global namespace to the type hint of each parameter of the method.
     *
     * This method is only necessary 'cause PHPUnit is currently not able to detect the usage of
     * namespaces. Thus it does not add the '\' to a type hint.
     *
     * @param \ReflectionMethod $method
     * @return string
     */
    protected static function getArgumentDeclaration(\ReflectionMethod $method)
    {
        $declarations = array();
        $parameters = explode(', ', \PHPUnit_Util_Class::getMethodParameters($method));

        foreach ($parameters as $parameter) {
            if (0 < strpos(trim($parameter), ' $') && false === strpos($parameter, 'array')) {
                $declarations[] = '\\'.$parameter;
            } else {
                $declarations[] = $parameter;
            }
        }
        return implode(', ', $declarations);
    }

    /**
     * Determine if the given method may be proxied.
     *
     * Since it is not possible to reflect a
     *  - constructor
     *  - final method
     *  - static method
     * those will cause this method to return false.
     * Also methods registered in the blacklist will cause this
     * method to return false.
     *
     * @param \ReflectionMethod $method Name of the method to be reflected.
     * @return boolean True, if the given method may be reflected, else false.
     */
    protected static function canProxyMethod(\ReflectionMethod $method)
    {
        if ($method->isConstructor() ||
        $method->isFinal() ||
        $method->isStatic() ||
        isset(self::$blacklistedMethodNames[$method->getName()])) {
            return false;
        } elseif ($method->isProtected()) {
            return true;
        }
        return false;
    }

    /**
     * Determine if the given proxy may be proxied.
     *     *
     * @param \ReflectionProperty $property Name of the property to be reflected.
     * @return boolean True, if the given method may be reflected, else false.
     */
    protected static function canProxyProperty(\ReflectionProperty $property)
    {
//        if ($property->isStatic()) {
//            return false;
//        }
        return true;
    }

    /**
     * Generates the template to be used to create a proxy object.
     *
     * The return value depends on the used version of PHPUnit.
     * If a version is 3.5 of higher Text_Template, else PHPUnit_Util_Template
     * is used.
     *
     * @param string $file The location of the template file to be used.
     * @return Text_Template|PHPUnit_Util_Template The template object to create the proxy class.
     */
    protected static function createTemplateObject($file)
    {
        include_once('Text/Template.php');
        return new \Text_Template($file);
    }
}
