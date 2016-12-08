<?php

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