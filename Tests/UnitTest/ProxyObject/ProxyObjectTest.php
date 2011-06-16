<?php
/**
 * Unittest suite for the ProxyObject class.
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

namespace lapistano\Tests\UnitTest\ProxyObject;

use lapistano\ProxyObject\ProxyObject;

/**
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author Bastian Feder <github@bastian-feder.de>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 */
class ProxyObjectTest extends \PHPUnit_Framework_TestCase
{
    /*************************************************************************/
    /* Fixtures
    /*************************************************************************/

    private function getDummyFixture($className = '\lapistano\Tests\ProxyObject\DummyNS', array $methods = array())
    {
        if (empty($methods)) {
            $methods = array('getArm', 'getArmNS');
        }

        $proxyObject = new ProxyObject();
        return $proxyObject->getProxy($className, $methods);
    }

    /*************************************************************************/
    /* Tests
    /*************************************************************************/

    /**
     * @covers \lapistano\ProxyObject\ProxyObject::getProxy
     */
    public function testGetProxy()
    {
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $this->getDummyFixture());
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyObject::getProxy
     */
    public function testGetProxyWithConstructorArguments()
    {
        $proxyObject = new ProxyObject();
        $proxy = $proxyObject->getProxy(
            '\lapistano\Tests\ProxyObject\DummyNS',
            array('getArm', 'getArmNS'),
            array(),
            array(array())
        );
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $proxy);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyObject::getProxy
     */
    public function testGetProxyNoNamespace()
    {
        $proxyObject = new ProxyObject();

        $actual = $proxyObject->getProxy('Dummy');
        $this->assertInstanceOf('Dummy', $actual);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyObject::getProxy
     */
    public function testGetProxyByReflection()
    {
        $proxyObject = new ProxyObject();

        $tmp = $this->getDummyFixture();
        $actual = $proxyObject->getProxy(
            '\lapistano\Tests\ProxyObject\DummyNS',
            array('getArm', 'getArmNS'),
            array('myPrivate', 'nervs')
        );
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $actual);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyObject::getProxy
     */
    public function testGetProxyDisableOriginalConstructor()
    {
        $proxyObject = new ProxyObject();
        $proxy = $proxyObject->getProxy(
            '\lapistano\Tests\ProxyObject\DummyNS',
            array('getArm', 'getArmNS'),
            array(),
            array(),
            '',
            false
        );
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $proxy);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyObject::getProxyBuilder
     */
    public function testGetProxyBuilderNamespaced()
    {
        $proxyObject = new ProxyObject();
        $actual = $proxyObject
            ->getProxyBuilder('\lapistano\Tests\ProxyObject\DummyNS')
            ->setMethods(array('getArm', 'getArmNS'))
            ->getProxy();
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $actual);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyObject::getProxyBuilder
     */
    public function testGetProxyBuilder()
    {
        $proxyObject = new ProxyObject();

        $actual = $proxyObject
            ->getProxyBuilder('Dummy')
            ->getProxy();
        $this->assertInstanceOf('Dummy', $actual);
    }
}