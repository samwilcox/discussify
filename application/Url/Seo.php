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
 * This class is responsible for handling Search Engine Optimization (SEO)
 * routines.
 * 
 * @package Discussify\Url
 */
class Seo extends \Discussify\Application {
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
     * Takes the given data and contructs a valid SEO URL and
     * then returns it.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     * @param array $params - Optional key value parameters.
     * @param bool $includeCSRF - Whether to include CSRF token.
     * @param bool $noneSeo - Whether to return just the normal hyperlink.
     * @return string - Valid SEO URL for the given parameters.
     */
    public static function url($controller, $action = null, $params = [], $includeCSRF = false, $noneSeo = false) {
        $seo = '';

        $seoEnabled = $noneSeo ? false : self::settings()->seo_enabled;

        switch ($seoEnabled) {
            case true:
                $url = \sprintf('/%s', $controller);

                if ($action !== null) $url .= \sprintf('/%s', $action);

                if (isset($params) && \count($params) > 0) {
                    foreach ($params as $k => $v) {
                        $url .= \sprintf('/%s/%s', $k, $v);
                    }
                }

                if ($includeCSRF && self::settings()->csrf_enabled) {
                    $url .= \sprintf('/token/%s', self::security()->getToken());
                }

                $seo = \sprintf('%s?%s', self::vars()->wrapper, $url);
                break;

            case false:
                $url = \sprintf('?controller=%s', $controller);

                if ($action !== null) $url .= \sprintf('&action=%s', $action);

                if (isset($params) && \count($params) > 0) {
                    foreach ($params as $k => $v) {
                        $url .= \sprintf('&%s=%s', $k, $v);
                    }
                }

                if ($includeCSRF && self::settings()->csrf_enabled) {
                    $url .= \sprintf('&token=%s', self::security()->getToken());
                }

                $seo = \sprintf('%s%s', self::vars()->wrapper, $url);
                break;
        }

        return $seo;
    }
}