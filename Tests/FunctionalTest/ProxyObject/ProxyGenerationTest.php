<?php
/**
 * Functionaltest suite for the generation od a proxy object.
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

namespace lapistano\Tests\FunctionalTests\ProxyObject;

use lapistano\ProxyObject\ProxyObject;

/**
 *
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author Bastian Feder <github@bastian-feder.de>
 * @license http://www.apache.org/licenses/LICENSE-2.0
 * @link https://github.com/lapistano/proxy-object
 * @package Unittests
 * @subpackage ProxyObject
 */
class ProxyGenerationTest extends \PHPUnit_Framework_TestCase
{

    public function testGetCompleteProxyFromClass()
    {
        $proxy = new ProxyObject();
        $proxyDummy = $proxy->getProxy('Dummy', array('getArm'));
        $this->assertEquals('left arm', $proxyDummy->getArm('left'));
        $this->assertEquals('right arm', $proxyDummy->getArm('right'));
    }

    public function testGetCompleteProxyFromNamespacedClass()
    {
        $proxy = new ProxyObject();
        $proxyDummyNS = $proxy->getProxy(
            '\lapistano\Tests\ProxyObject\DummyNS',
            array('getArm', 'getArmNS')
        );
        $this->assertEquals('left arm', $proxyDummyNS->getArmNS(new \stdClass));
        $this->assertEquals('left arm', $proxyDummyNS->getArm('left'));
        $this->assertEquals('right arm', $proxyDummyNS->getArm('right'));
    }

    public function testGetCompleteProxyFromNamespacedClassSelectedProperty()
    {
        $proxy = new ProxyObject();
        $proxyDummyNS = $proxy->getProxy(
            '\lapistano\Tests\ProxyObject\DummyNS',
            array('getArm', 'getArmNS'),
            array('myPrivate')
        );

        $proxyDummyNS->myPrivate = 'beastie';

        $this->assertEquals('left arm', $proxyDummyNS->getArmNS(new \stdClass));
        $this->assertEquals('left arm', $proxyDummyNS->getArm('left'));
        $this->assertEquals('right arm', $proxyDummyNS->getArm('right'));
        $this->assertEquals('beastie', $proxyDummyNS->myPrivate);

    }

    public function testGetProxyOfSingleMethodFromNamespacedClass()
    {
        $proxy = new ProxyObject();
        $proxyDummyNS = $proxy->getProxy(
            '\lapistano\Tests\ProxyObject\DummyNSwithStatic',
            array('getArm'),
            array('myPrivate', 'myProtected', 'myProtectedFloat')
        );
        $this->assertEquals('left arm', $proxyDummyNS->getArm('left'));
        $this->assertEquals('right arm', $proxyDummyNS->getArm('right'));
    }

    /**
     * @dataProvider getProxyExpectingPHPUnit_Framework_Exception
     * @expectedException  \PHPUnit_Framework_Exception
     */
    public function testGetProxyExpectingPHPUnit_Framework_Exception($class, $methods)
    {
        $proxy = new ProxyObject();
        $proxyDummyNS = $proxy->getProxy($class, $methods);
    }


    public function testGetProxyBuilderFormClassWithUninitiableTypeHint()
    {
        //DummyWithConstructorAndUninitiableTypeHint
        $proxy = new ProxyObject();
        $proxyDummy = $proxy->getProxyBuilder('\\DummyWithConstructorAndUninitiableTypeHint')
            ->disableOriginalConstructor()
            ->getProxy();
        $this->assertEquals('Beastie', $proxyDummy->getMembers('Beastie'));
        $this->assertEquals('tux', $proxyDummy->myProtected);
    }

    /*************************************************************************/
    /* Dataprovider & callbacks
    /*************************************************************************/

    public static function getProxyExpectingPHPUnit_Framework_Exception()
    {
        return array(
            'final method' => array('\lapistano\Tests\ProxyObject\DummyNS', array('armsFinal')),
            'no protected methods' => array('\DummyAllPublic', array()),
            'no protected methods with unknown method' => array('\DummyAllPublic', array('unknown method')),
        );
    }
}