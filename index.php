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

// error_reporting(E_ALL);
// ini_set('display_errors', true);
error_reporting(0);

define('ROOT_PATH', dirname(__FILE__) . '/');

require_once (ROOT_PATH . 'application/Discussify.php');
\Discussify\Application::run();