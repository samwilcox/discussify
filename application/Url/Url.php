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

namespace Discussify\Url;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * The class handles everything related to web URLs.
 * 
 * @package Discussify\Urls
 */
class Url extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

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
     * Determines the correct redirect URL should be and returns it.
     * 
     * @return string - Resulting URL.
     */
    public static function getRedirectUrl() {
        $url = $_SERVER['HTTP_REFERER'];

        if (self::settings()->seo_enabled) {
            if (\stristr($url, self::vars()->baseUrl) && !\stristr($url, 'authentication')) {
                return $url;
            } else {
                return self::seo()->url('index');
            }
        } else {
            if (\stristr($url, self::vars()->wrapper) && !\stristr($url, 'authentication')) {
                return $url;
            } else {
                return self::vars()->wrapper;
            }
        }
    }

    /**
     * Returns an partial URL with the given dual items, which will
     * be in the form first-second.
     * 
     * @param string $first - The first string value.
     * @param string $second - The second string value.
     * @return string - Resulting partial URL string.
     */
    public static function getDualUrl($first, $second) {
        return \urlencode(\sprintf('%s-%s', \strtolower(\str_replace(' ', '-', $first)), \strtolower(\str_replace(' ', '-', $second))));
    }
}