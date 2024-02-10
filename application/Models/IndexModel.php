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

namespace Discussify\Models;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * This class is the model for the index controller.
 * 
 * @package Discussify\Models
 */
class IndexModel extends \Discussify\Models\BaseModel {
    
    /**
     * Key value pair collection for tag replacements.
     * @var array
     */
    private static $vars = [];

    /**
     * Responsible for handling operations for the:
     * Controller: index
     * Action: index
     * 
     * @return $vars
     */
    public function appIndex() {
        return self::$vars;
    }
}