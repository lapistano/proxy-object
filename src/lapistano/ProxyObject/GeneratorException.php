<?php
/**
 * Class used by the ProxyObject to generate the actual proxy pbject.
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

class GeneratorException extends \PHPUnit_Framework_Exception
{
    const NO_PROTECTED_METHOD_DEFINED = 100;
    const CANNOT_PROXY_METHOD = 101;
    const CLASS_NOT_FOUND = 102;
    const CLASS_IS_FINAL = 103;
    const IS_INTERFACE = 104;
    const NO_PROTECTED_OR_PRIVATE_PROPERTY_DEFINED = 105;

}
