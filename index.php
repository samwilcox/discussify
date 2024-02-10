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

define('ROOT_PATH', dirname(__FILE__) . '/');

require_once (ROOT_PATH . 'application/Discussify.php');
\Discussify\Application::run();