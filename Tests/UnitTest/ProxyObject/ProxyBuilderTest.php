<?php
/**
 * Unittest suite for the ProxyBuilder class.
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

use lapistano\ProxyObject\ProxyBuilder;

/**
 *
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author     Bastian Feder <github@bastian-feder.de>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @link       https://github.com/lapistano/proxy-object
 */
class ProxyBuilderTest extends \PHPUnit_Framework_TestCase
{

    /*************************************************************************/
    /* Fixtures
    /*************************************************************************/

    /**
     * Provides an instance of the ProxyBuilder.
     *
     * @param string $className
     *
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function getProxyBuilderObject($className = '\lapistano\Tests\ProxyObject\DummyNS')
    {
        $pb = new ProxyBuilder($className);

        return $pb->setMethods(
            array(
                'getArm',
                'getArmNS'
            )
        );
    }

    /*************************************************************************/
    /* Tests
    /*************************************************************************/

    public function testGetProxy()
    {
        $this->assertInstanceOf(
            '\lapistano\Tests\ProxyObject\DummyNS',
            $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS')->getProxy()
        );
    }

    public function testGetProxyNoNamespace()
    {
        $proxy = new ProxyBuilder('Dummy');
        $this->assertInstanceOf('Dummy', $proxy->getProxy());
    }

    public function testGetProxyBuilderNamespaced()
    {
        $this->assertInstanceOf(
            '\lapistano\Tests\ProxyObject\DummyNS',
            $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS')->getProxy()
        );
    }

    public function testGetProxyWithConstructorArguments()
    {
        $actual = $this->getProxyBuilderObject()->setConstructorArgs(array(array()))->getProxy();
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $actual);
    }

    public function testGetProxyByReflection()
    {
        $actual = $this->getProxyBuilderObject()->setProperties(
            array(
                'myPrivate',
                'nervs'
            )
        )->getProxy();
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $actual);
    }

    public function testGetProxyDisableOriginalConstructor()
    {
        $actual = $this->getProxyBuilderObject()
            ->disableOriginalConstructor()
            ->getProxy();
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $actual);
    }

    public function testExposeInheritedMember()
    {
        $proxy = new ProxyBuilder('\ExtendsDummy');
        $actual = $proxy
            ->setProperties(array('mascotts'))
            ->getProxy();

        $this->assertEquals(
            array(
                'Tux',
                'Beastie',
                'Gnu'
            ), $actual->mascotts
        );
    }

    public function testConstruct()
    {
        $proxyBuilder = new ProxyBuilder('myProxyObject');
        $this->assertAttributeEquals('myProxyObject', 'className', $proxyBuilder);
    }

    public function testGetProxyMethod()
    {
        $proxy = $this->getProxyBuilderObject()
            ->setMethods(array('getArm'))
            ->getProxy();

        $this->assertEquals('right arm', $proxy->getArm('right'));
    }

    public function testGetProxyProperty()
    {
        $proxy = $this->getProxyBuilderObject()
            ->setProperties(array('myPrivate'))
            ->getProxy();

        $proxy->myPrivate = 'beastie';
        $this->assertEquals('beastie', $proxy->myPrivate);
    }

    public function testGetProxyNamespacedMethod()
    {
        $proxy = $this
            ->getProxyBuilderObject()
            ->getProxy();
        $this->assertEquals('left arm', $proxy->getArmNS(new \stdClass));
    }

    public function testSetConstructorArgs()
    {
        $args = array(
            'beastie',
            'tux'
        );

        $actual = $this->getProxyBuilderObject()->setConstructorArgs($args);

        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $actual);
        $this->assertAttributeEquals($args, 'constructorArgs', $actual);
    }

    public function testSetProxyClassName()
    {
        $classname = 'CustomClassNameProxy';
        $actual = $this->getProxyBuilderObject()->setProxyClassName($classname);

        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $actual);
        $this->assertAttributeEquals($classname, 'mockClassName', $actual);
    }

    public function testDisableOriginalConstructor()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');

        $this->assertAttributeSame(true, 'invokeOriginalConstructor', $proxyBuilder);
        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $proxyBuilder->disableOriginalConstructor());
        $this->assertAttributeSame(false, 'invokeOriginalConstructor', $proxyBuilder);
    }

    public function testDisableAutoload()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');

        $this->assertAttributeSame(true, 'autoload', $proxyBuilder);
        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $proxyBuilder->disableAutoload());
        $this->assertAttributeSame(false, 'autoload', $proxyBuilder);
    }

    public function testGetInstanceOf()
    {
        $proxyBuilder = new ProxyBuilder('\lapistano\ProxyObject\ProxyBuilder');
        $proxy = $proxyBuilder
            ->disableOriginalConstructor()
            ->setMethods(array('getInstanceOf'))
            ->getProxy();

        $this->assertInstanceOf('stdClass', $proxy->getInstanceOf('stdClass'));
    }
}
