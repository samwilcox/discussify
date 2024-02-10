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

namespace Discussify\Controllers;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Discussify base controller class.
 * All controllers shall inherit from this class.
 * 
 * @package Discussify\Controllers
 */
class BaseController {

    /**
     * Key value pairs for replacing tags.
     * @var array
     */
    protected static $vars = [];

    /**
     * Sets the key value pairs class property.
     * @var array
     */
    protected static function set($arr) {
        self::$vars = \array_merge(self::$vars, $arr);
    }

    /**
     * Outputs the source for the given controller and action.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     */
    protected static function output($controller, $action) {
        \Discussify\Output\Output::i()->render($controller, $action, self::$vars);
    }

    /**
     * Outputs the given source.
     * 
     * @param mixed $source - The source to output.
     * @param string $contentType - The content type of the source.
     */
    protected static function outputSource($source, $contentType = 'application/json') {
        \Discussify\Output\Output::i()->renderSource($source, $contentType);
    }

    /**
     * Outputs the specified partial to the web browser.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     * @param string $partial - The partial name.
     */
    protected static function outputPartial($controller, $action, $partial) {
        \Discussify\Output\Output::i()->renderPartial($controller, $action, $partial, self::$vars);
    }

    /**
     * Outputs the given source raw without modifications to the
     * web browser.
     * 
     * @param mixed $source - The source to output.
     */
    protected static function outputRaw($source) {
        print $source;
    }
}