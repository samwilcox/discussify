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

namespace Discussify\Helpers;

// Include the 3rd party library HTML Purifier.
require (APP_PATH . 'Vendors/htmlpurifier/HTMLPurifier.standalone.php');
use HTMLPurifier;
use HTMLPurifier_Config;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * This class is responsible for sanitation of various strings, such
 * as HTML.
 * 
 * @package Discussify\Helpers
 */
class Sanitizer extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * HTML Purifier configuration object.
     * @var object
     */
    protected static $config;

    /**
     * HTML Purifier instance object.
     * @var object
     */
    protected static $purifier;

    /**
     * Constructor that creates a new configuration object.
     */
    public function __construct() {
        self::$config = HTMLPurifier_Config::createDefault();
        self::$config->set('Core.Encoding', 'UTF-8');
        self::$config->set('HTML.Doctype', 'HTML 5');
        self::$config->set('HTML.Allowed', '');

        self::$purifier = new HTMLPurifier(self::$config);
    }

    /**
     * Returns singleton instance of this class.
     * 
     * @return object - Singleton instance.
     */
    public static function i() {
        if (!self::$instance) self::$instance = new self;
        return self::$instance;
    }

    /**
     * Using HTML Purifier, we sanitize the HTML before we place it
     * into the database for security reasons.
     * 
     * @param string $html - The HTML source to sanitize.
     * @return string - Sanitized HTML source.
     */
    public static function sanitize($html) {
        return self::$purifier->purify($html);
    }
}