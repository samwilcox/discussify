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

namespace Discussify\Type;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Constant class for access to various content types.
 * 
 * @package \Discussify\Type
 */
class ContentType extends \Discussify\Type\Types {
    const JSON = 'application/json';
    const HTML = 'text/html';
    const CSS = 'text/css';
    const JAVASCRIPT = 'text/javascript';
    const PNG = 'image/png';
    const TEXT = 'text';
    const RSS = 'application/xml';
}