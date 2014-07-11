<?php
/**
 * Unittest suite for the Generator class.
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

namespace lapistano\Tests\UnitTest\ProxyObject;

use lapistano\ProxyObject\Generator;

/**
 *
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author     Bastian Feder <github@bastian-feder.de>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @link       https://github.com/lapistano/proxy-object
 */
class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $data
     *
     * @return mixed|string
     */
    protected function slag($data)
    {
        return str_replace(
            array(
                "\r",
                "\r\n",
                "\n",
                "  "
            ), "", $data
        );
    }

    public function testGenerateWithPredefinedProxyName()
    {
        $expected = array(
            'code' => "class DummyProxy extends Dummy\n"
                . "{\n\n" . "public \$nervs = array();\n"
                . "public \$mascotts = array(0 => 'Tux', 1 => 'Beastie', 2 => 'Gnu', );\n\n\n"
                . "public function getArm(\$position)\n"
                . "    {\n"
                . "        return parent::getArm(\$position);\n"
                . "    }\n\n\n}\n",
            'proxyClassName' => 'DummyProxy',
            'namespaceName' => ''
        );

        $generator = new Generator();
        $result = $generator::generate('Dummy', null, null, 'DummyProxy');

        $this->assertArrayHasKey('code', $result);
        $this->assertArrayHasKey('proxyClassName', $result);
        $this->assertArrayHasKey('namespaceName', $result);

        $this->assertEquals('DummyProxy', $result['proxyClassName']);

        $this->assertEquals(
            $this->slag($expected['code']), $this->slag($result['code'])
        );
    }

    public function testGenerate()
    {
        $generator = new Generator();
        $proxy = $generator::generate('Dummy');

        $this->assertRegExp('/^Proxy_Dummy_\w+/', $proxy['proxyClassName']);
        $this->assertEmpty($proxy['namespaceName']);
    }

    public function testGenerateProxyFromCache()
    {
        $generator = new Generator();
        $proxy = $generator::generate('Dummy');
        $actual = $generator::generate('Dummy');

        $this->assertRegExp('/^Proxy_Dummy_\w+/', $actual['proxyClassName']);
        $this->assertEmpty($actual['namespaceName']);
    }

    public function testGetMethodCallParameters()
    {
        $class = new \ReflectionClass('\lapistano\Tests\ProxyObject\DummyNS');
        $proxy = new GeneratorProxy();
        $method = $class->getMethod('getArm');

        $this->assertEquals('$position, $foo', $proxy::getMethodCallParameters($method));
    }

    /**
     * @expectedException \lapistano\ProxyObject\GeneratorException
     */
    public function testGenerateProxyExpectingPHPUnit_Framework_ExceptionUnableToProxyMethod()
    {
        $expected = array(
            "namespaceName" => "\lapistano\Tests\ProxyObject"
        );

        $actual = GeneratorProxy::generateProxy(
            '\lapistano\Tests\ProxyObject\DummyNS', array('arm')
        );
    }

    public function testGenerateProxyAllProtectedMethods()
    {
        $expected = array(
            "namespaceName" => "\lapistano\Tests\ProxyObject"
        );

        $actual = GeneratorProxy::generateProxy('Dummy');

        $this->assertRegExp('/^Proxy_Dummy_\w+/', $actual['proxyClassName']);
    }

    /**
     * @expectedException \lapistano\ProxyObject\GeneratorException
     */
    public function testGenerateProxyExpectingPHPUnit_Framework_ExceptionUnableToProxyMethods()
    {
        $actual = GeneratorProxy::generateProxy('\lapistano\Tests\ProxyObject\DummyNS', array('armsFinal'));
    }

    /**
     * @expectedException \lapistano\ProxyObject\GeneratorException
     * @dataProvider generateProxyExpectingExceptionDataprovider
     */
    public function testGenerateProxyExpectingPHPUnit_Framework_Exception($class, $method)
    {
        $actual = GeneratorProxy::generateProxy($class, array($method));
    }

    /**
     * @dataProvider getArgumentDeclarationDataprovider
     */
    public function testGetArgumentDeclaration($expected, $method)
    {
        $class = new \ReflectionClass('\lapistano\Tests\ProxyObject\DummyNS');
        $proxy = new GeneratorProxy();
        $method = $class->getMethod($method);

        $this->assertEquals($expected, $proxy::getArgumentDeclaration($method));
    }

    /**
     * @dataProvider getProxiedPropertiesDataprovider
     */
    public function testGetProxiedProperties($expected, $className)
    {
        $class = new \ReflectionClass($className);
        $proxy = new GeneratorProxy();

        $this->assertEquals(
            $this->slag($expected), $this->slag($proxy::getProxiedProperties('DummyNS', $class))
        );
    }

    public function testGetProxiedPropertiesSelectedProperty()
    {
        $class = new \ReflectionClass('\lapistano\Tests\ProxyObject\DummyNS');
        $proxy = new GeneratorProxy();

        $this->assertEquals(
            $this->slag('public $myPrivate;' . "\n"),
            $this->slag($proxy::getProxiedProperties('DummyNS', $class, array('myPrivate')))
        );
    }

    public function testGetProxiedPropertiesSelectedStaticProperty()
    {
        $class = new \ReflectionClass('\lapistano\Tests\ProxyObject\DummyNSwithStatic');
        $proxy = new GeneratorProxy();

        $this->assertEquals(
            $this->slag('public static $myStatic = \'tux\';' . "\n"),
            $this->slag($proxy::getProxiedProperties('DummyNSwithStatic', $class, array('myStatic')))
        );
    }

    /**
     * @dataProvider getProxiedPropertiesExceptionDataprovider
     * @expectedException \lapistano\ProxyObject\GeneratorException
     */
    public function testGetProxiedPropertiesExpectingPHPUnit_Framework_Exception($property)
    {
        $class = new \ReflectionClass('\lapistano\Tests\ProxyObject\DummyNSwithStatic');
        $proxy = new GeneratorProxy();
        $proxy::getProxiedProperties('DummyNS', $class, array($property));
    }

    /**
     * @dataProvider arrayToStringDataprovider
     */
    public function testArrayToString($expected, $array)
    {
        $this->assertEquals($expected, GeneratorProxy::arrayToString($array));
    }

    /**
     * @dataProvider canProxyMethodDataprovider
     */
    public function testCanProxyMethod($method)
    {
        $reflected = new \ReflectionMethod('\lapistano\Tests\ProxyObject\DummyNS', $method);
        $this->assertFalse(GeneratorProxy::canProxyMethod($reflected));
    }

    public function testCanProxyMethods()
    {
        $class = new \ReflectionClass('\lapistano\Tests\ProxyObject\DummyNS');
        $methods = $class->getMethods(\ReflectionMethod::IS_PROTECTED);

        $this->assertCount(3, GeneratorProxy::canProxyMethods($methods));

    }


    /*************************************************************************/
    /* Dataprovider
    /*************************************************************************/

    public static function canProxyMethodDataprovider()
    {
        return array(
            'final method' => array('armsFinal'),
            'static method' => array('arms'),
            'public method' => array('getArms'),
        );
    }

    public static function arrayToStringDataprovider()
    {
        return array(
            'one level' => array(
                "0 => 'Tux', 1 => 'Beastie', ",
                array(
                    'Tux',
                    'Beastie'
                )
            ),
            'two level' => array(
                "'mascotts' => array (0 => 'Tux', 1 => 'Beastie', ), 0 => 'Foo', ",
                array(
                    'mascotts' => array(
                        'Tux',
                        'Beastie'
                    ),
                    'Foo'
                )
            ),
            'mixed level' => array(
                "'mascotts' => array (0 => 'Tux', 1 => 'Beastie', ), 0 => array (0 => 'Foo', ), ",
                array(
                    'mascotts' => array(
                        'Tux',
                        'Beastie'
                    ),
                    array('Foo')
                )
            ),
        );
    }

    public static function getProxiedPropertiesExceptionDataprovider()
    {
        return array(
            'Unknown Property' => array('Unknown Property'),
        );
    }

    public static function getProxiedPropertiesDataprovider()
    {
        return array(
            'with constructor' => array(
                "public \$nervs = array();\npublic \$mascotts = array(0 => 'Tux', 1 => 'Beastie', 2 => 'Gnu', );\npublic \$myPrivate;\n",
                '\lapistano\Tests\ProxyObject\DummyNS'
            ),
            'without constructor' => array(
                "public \$nervs = array();\npublic \$myPrivate = array();\n",
                '\lapistano\Tests\ProxyObject\DummyNSnoConstruct'
            ),
        );
    }

    public static function getArgumentDeclarationDataprovider()
    {
        return array(
            'array' => array(
                "array \$arms = array (\n)",
                'setArms'
            ),
            'interface/class' => array(
                '\stdClass $dom',
                'getArmNS'
            ),
            'plain parameter' => array(
                '$position, $foo = \'\'',
                'getArm'
            ),
        );
    }

    public static function generateProxyExpectingExceptionDataprovider()
    {
        return array(
            'not existing class' => array(
                'NotExistingClass',
                'getArm'
            ),
            'final class' => array(
                'finalDummy',
                'getArm'
            ),
            'interface' => array(
                'DummyInterface',
                'test'
            ),
            'final method' => array(
                '\lapistano\Tests\ProxyObject\DummyNS',
                'armsFinal'
            ),
        );
    }
}

class GeneratorProxy extends \lapistano\ProxyObject\Generator
{
    public static function getProxiedProperties($fullClassName, \ReflectionClass $class, array $properties = null)
    {
        return parent::getProxiedProperties($fullClassName, $class, $properties);
    }

    public static function canProxyMethod(\ReflectionMethod $method)
    {
        return parent::canProxyMethod($method);
    }

    public static function canProxyMethods(array $methods)
    {
        return parent::canProxyMethods($methods);
    }

    public function reflectMethods(array $methods, \ReflectionClass $class, $originalClassName)
    {
        return parent::reflectMethods($methods, $class, $originalClassName);
    }

    public static function generateProxy($originalClassName, array $methods = null, array $properties = null,
                                         $proxyClassName = '', $callAutoload = false)
    {
        return parent::generateProxy($originalClassName, $methods, $properties, $proxyClassName, $callAutoload);
    }

    public static function getArgumentDeclaration(\ReflectionMethod $method)
    {
        return parent::getArgumentDeclaration($method);
    }

    public static function getInstance(\ReflectionClass $class)
    {
        return parent::getInstance($class);
    }

    public static function arrayToString(array $array)
    {
        return parent::arrayToString($array);
    }
}
