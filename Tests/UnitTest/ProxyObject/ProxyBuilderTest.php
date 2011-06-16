<?php
/**
 * Unittest suite for the ProxyBuilder class.
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

use lapistano\ProxyObject\ProxyBuilder;
use lapistano\ProxyObject\ProxyObject;

/**
 *
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author Bastian Feder <github@bastian-feder.de>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 */
class ProxyBuilderTest extends \PHPUnit_Framework_TestCase
{

    /*************************************************************************/
    /* Fixtures
    /*************************************************************************/

    /**
     * Provides an instance of the ProxyObject.
     *
     * @param string $className
     * @return \lapistano\ProxyObject\ProxyBuilder
     */
    public function getProxyBuilderObject($className)
    {
        $proxyObject = new ProxyObject();
        $pb = new ProxyBuilder($proxyObject, $className);
        return $pb->setMethods(array('getArm', 'getArmNS'));
    }

    /*************************************************************************/
    /* Tests
    /*************************************************************************/

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::__construct
     */
    public function testConstruct()
    {
        $className = 'myProxyObject';
        $proxyObject = $this->getMock('lapistano\ProxyObject\ProxyObject');
        $proxyBuilder = new ProxyBuilder($proxyObject, $className);

        $this->assertAttributeSame($proxyObject, 'proxyObject', $proxyBuilder);
        $this->assertAttributeEquals($className, 'className', $proxyBuilder);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::getProxy
     */
    public function testGetProxyPlain()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');
        $dummyProxy = $proxyBuilder->getProxy();
        $this->assertInstanceOf('\lapistano\Tests\ProxyObject\DummyNS', $dummyProxy);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::getProxy
     * @covers \lapistano\ProxyObject\ProxyBuilder::setMethods
     */
    public function testGetProxyMethod()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');

        $dummyProxy = $proxyBuilder
            ->setMethods(array('getArm'))
            ->getProxy();

        $this->assertEquals('right arm', $dummyProxy->getArm('right'));
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::getProxy
     * @covers \lapistano\ProxyObject\ProxyBuilder::setProperties
     */
    public function testGetProxyProperty()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');

        $dummyProxy = $proxyBuilder
            ->setProperties(array('myPrivate'))
            ->getProxy();

        $dummyProxy->myPrivate = 'beastie';
        $this->assertEquals('beastie', $dummyProxy->myPrivate);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::getProxy
     * @covers \lapistano\ProxyObject\ProxyBuilder::setMethods
     */
    public function testGetProxyNamspacedMethod()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');

        $dummyProxy = $proxyBuilder
            ->setMethods(array('getArmNS'))
            ->getProxy();

        $dummyProxy->getArmNS(new \stdClass);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::setConstructorArgs
     */
    public function testSetConstructorArgs()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');
        $args = array('beastie', 'tux');
        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $proxyBuilder->setConstructorArgs($args));
        $this->assertAttributeEquals($args, 'constructorArgs', $proxyBuilder);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::setProxyClassName
     */
    public function testSetProxyClassName()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');
        $classname = 'CustomClassNameProxy';
        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $proxyBuilder->setProxyClassName($classname));
        $this->assertAttributeEquals($classname, 'mockClassName', $proxyBuilder);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::disableOriginalConstructor
     */
    public function testDisableOriginalConstructor()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');
        $this->assertAttributeSame(true, 'originalConstructor', $proxyBuilder);
        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $proxyBuilder->disableOriginalConstructor());
        $this->assertAttributeSame(false, 'originalConstructor', $proxyBuilder);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::disableOriginalClone
     */
    public function testDisableOriginalClone()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');
        $this->assertAttributeSame(true, 'originalClone', $proxyBuilder);
        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $proxyBuilder->disableOriginalClone());
        $this->assertAttributeSame(false, 'originalClone', $proxyBuilder);
    }

    /**
     * @covers \lapistano\ProxyObject\ProxyBuilder::disableAutoload
     */
    public function testDisableAutoload()
    {
        $proxyBuilder = $this->getProxyBuilderObject('\lapistano\Tests\ProxyObject\DummyNS');
        $this->assertAttributeSame(true, 'autoload', $proxyBuilder);
        $this->assertInstanceOf('\lapistano\ProxyObject\ProxyBuilder', $proxyBuilder->disableAutoload());
        $this->assertAttributeSame(false, 'autoload', $proxyBuilder);
    }

}