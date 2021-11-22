<?php

/**
 * Copyright Shopgate Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author    Shopgate Inc, 804 Congress Ave, Austin, Texas 78701 <interfaces@shopgate.com>
 * @copyright Shopgate Inc
 * @license   http://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 */

/**
 * Generates a stack trace with obfuscation of arguments and flattening of objects or arrays.
 *
 * In the stack trace, function calls will be presented with arguments, unless those are obfuscated or filtered by the
 * Shopgate_Helper_Logging_Obfuscator passed in the constructor.
 *
 * An argument that is an object will be converted to 'Object'.
 * An argument that is an array will be converted to 'Array'.
 * An argument that is boolean true / false will be converted to 'true' / 'false'.
 */
interface Shopgate_Helper_Logging_Stack_Trace_NamedParameterProviderInterface
{
    /**
     * Maps numerically indexed arguments to a function or method to its named parameters if it is available and
     * callable.
     *
     * @param string   $className    The name of the class or an empty string if not referring to a method.
     * @param string   $functionName The name of the function.
     * @param string[] $arguments    The arguments the function was called with.
     *
     * @return array [int|string, string] An array with the argument names as keys or the untouched $arguments if names
     *               could not be determined.
     */
    public function get($className, $functionName, array $arguments);
}
