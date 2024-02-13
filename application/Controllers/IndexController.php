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
 * Controller for the main page and other related items.
 * 
 * @package Discussify\Controllers
 */
class IndexController extends \Discussify\Controllers\BaseController {

    /**
     * Object reference for the model.
     * @var object
     */
    protected static $model;

    /**
     * Constructor that instantiates the model object.
     */
    public function __construct() {
        self::$model = new \Discussify\Models\IndexModel();
    }

    /**
     * Index page of the application.
     */
    public function index() {
        self::set(self::$model->appIndex());
        self::output('Index', 'Index');
    }
}