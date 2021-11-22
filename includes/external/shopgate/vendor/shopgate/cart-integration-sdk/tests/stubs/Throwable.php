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
 * A copy of the Throwable interface available since PHP 7.
 *
 * It _cannot_ be used in production, nor will it make Exceptions implement this, it's only just for mocking. Also, it
 * cannot be named "Throwable", because in PHP 7 "Throwable" cannot be implemented, you'll have to extend "Exception" or
 * "Error" instead.
 *
 * @see http://php.net/manual/en/class.throwable.php
 */
interface ThrowableStub
{
    /**
     * @return string
     */
    public function getMessage();

    /**
     * @return int
     */
    public function getCode();

    /**
     * @return string
     */
    public function getFile();

    /**
     * @return int
     */
    public function getLine();

    /**
     * @return array
     */
    public function getTrace();

    /**
     * @return string
     */
    public function getTraceAsString();

    /**
     * @return Throwable
     */
    public function getPrevious();

    /**
     * @return string
     */
    public function __toString();
}
