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
 * Controller for all incoming AJAX requests.
 * 
 * @package Discussify\Controllers
 */
class AjaxController extends \Discussify\Controllers\BaseController {

    /**
     * Object reference for the model.
     * @var object
     */
    protected static $model;

    /**
     * Constructor that instantiates the model object.
     */
    public function __construct() {
        self::$model = new \Discussify\Models\AjaxModel();
    }

    /**
     * Request to set the current forum filter.
     */
    public function setForumFilter() {
        self::$model->setForumFilter();
    }

    /**
     * Request to load more topics.
     */
    public function loadMoreTopics() {
        self::$model->loadMoreTopics();
    }

    /**
     * Request when a user clicks on a forum from the menu.
     * We load the topics for that forum.
     */
    public function forumItemSelect() {
        self::$model->forumItemSelect();
    }
}