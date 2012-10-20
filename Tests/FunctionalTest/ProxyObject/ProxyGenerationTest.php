<?php
    /**
     * Functionaltest suite for the generation od a proxy object.
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

namespace lapistano\Tests\FunctionalTests\ProxyObject;

use lapistano\ProxyObject\ProxyBuilder;

/**
 *
 *
 * @copyright  2010-2011 Bastian Feder <github@bastian-feder.de>
 * @author     Bastian Feder <github@bastian-feder.de>
 * @license    http://www.apache.org/licenses/LICENSE-2.0
 * @link       https://github.com/lapistano/proxy-object
 * @package    Unittests
 * @subpackage ProxyObject
 */
class ProxyGenerationTest extends \PHPUnit_Framework_TestCase
{

    public function testProxyMemberOfClassWithNoHiddenMethod()
    {
        $proxy = new ProxyBuilder('DummyNoProtectedMethods');
        $proxyDummyNS = $proxy->setProperties(array('mascotts'))->getProxy();
        $this->assertInternalType('array', $proxyDummyNS->mascotts);
    }

    public function testGetCompleteProxyFromClass()
    {
        $proxy = new ProxyBuilder('Dummy');
        $proxyDummy = $proxy->setMethods(array('getArm'))->getProxy();
        $this->assertEquals('left arm', $proxyDummy->getArm('left'));
        $this->assertEquals('right arm', $proxyDummy->getArm('right'));
    }

    public function testGetCompleteProxyFromNamespacedClass()
    {
        $proxy = new ProxyBuilder('\lapistano\Tests\ProxyObject\DummyNS');
        $proxyDummyNS = $proxy->setMethods(
            array(
                'getArm',
                'getArmNS'
            )
        )->getProxy();
        $this->assertEquals('left arm', $proxyDummyNS->getArmNS(new \stdClass));
        $this->assertEquals('left arm', $proxyDummyNS->getArm('left'));
        $this->assertEquals('right arm', $proxyDummyNS->getArm('right'));
    }

    public function testGetCompleteProxyFromNamespacedClassSelectedProperty()
    {
        $proxy = new ProxyBuilder('\lapistano\Tests\ProxyObject\DummyNS');
        $proxyDummyNS = $proxy->setMethods(
            array(
                'getArm',
                'getArmNS'
            )
        )->setProperties(array('myPrivate'))->getProxy();

        $proxyDummyNS->myPrivate = 'beastie';

        $this->assertEquals('left arm', $proxyDummyNS->getArmNS(new \stdClass));
        $this->assertEquals('left arm', $proxyDummyNS->getArm('left'));
        $this->assertEquals('right arm', $proxyDummyNS->getArm('right'));
        $this->assertEquals('beastie', $proxyDummyNS->myPrivate);
    }

    public function testGetProxyOfSingleMethodFromNamespacedClass()
    {
        $proxy = new ProxyBuilder('\lapistano\Tests\ProxyObject\DummyNSwithStatic');
        $proxyDummyNS = $proxy->setMethods(array('getArm'))->setProperties(
            array(
                'myPrivate',
                'myProtected',
                'myProtectedFloat'
            )
        )->getProxy();
        $this->assertEquals('left arm', $proxyDummyNS->getArm('left'));
        $this->assertEquals('right arm', $proxyDummyNS->getArm('right'));
    }

    /**
     * @dataProvider getProxyExpectingGeneratorException
     * @expectedException \lapistano\ProxyObject\GeneratorException
     */
    public function testGetProxyExpectingGeneratorException($class, $methods)
    {
        $proxy = new ProxyBuilder($class);
        $proxyDummyNS = $proxy->setMethods($methods)->getProxy();
    }

    public function testProxyMemberOfClassWithNoHiddenMember()
    {
        $proxy = new ProxyBuilder('DummyNoProtectedMembers');
        $proxyDummyNS = $proxy->setMethods(array('getArm'))->getProxy();
        $this->assertEquals('left arm', $proxyDummyNS->getArm('left'));
    }

    /**
     * @expectedException \lapistano\ProxyObject\GeneratorException
     */
    public function testGetProxyOfClassWithStaticProtectedMethod()
    {
        $proxy = new ProxyBuilder('\DummyWithNoProxyableProtectedMethods');
        $proxiedClass = $proxy->getProxy();
    }

    /*************************************************************************/
    /* Dataprovider & callbacks
    /*************************************************************************/

    public static function getProxyExpectingGeneratorException()
    {
        return array(
            'final method' => array(
                '\lapistano\Tests\ProxyObject\DummyNS',
                array('armsFinal')
            ),
            'no protected methods with unknown method' => array(
                '\DummyAllPublic',
                array('unknown method')
            ),
        );
    }
}
