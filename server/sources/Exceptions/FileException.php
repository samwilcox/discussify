<?php

/**
 * Discussify
 * 
 * Version: 0.1.0
 * 
 * by Sam Wilcox <sam@discussify.com>
 * https://www.discussify.com
 * 
 * User-End License Agreement:
 * https://license.discussify.com
 */

namespace Discussify\Exceptions;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class to handle all file IO related exceptions.
 * 
 * @package Discussify\Exceptions
 */
class FileException extends \Exception {

    /**
     * Constructor that sends the exception to the correct location.
     * 
     * @param string $message - Exception message.
     */
    public function __construct($message) {
        parent::__construct($message);
    }
}