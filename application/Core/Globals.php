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

namespace Discussify\Core;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that handles all global variables for the application.
 * 
 * @package Discussify\Core
 */
class Globals extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;
    
    /**
     * Global variables collection.
     * @var array
     */
    protected static $vars = [];

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
     * Builds al the various global variables for the application and then
     * returns them all.
     * 
     * @return array - Collection of global variables.
     */
    public static function get() {
        self::$vars['communityTitle'] = self::settings()->community_title;
        self::$vars['imagesetUrl'] = self::user()->imagesetUrl();
        self::$vars['themeUrl'] = self::user()->themeUrl();
        self::$vars['timestamp'] = \time();
        self::$vars['baseUrl'] = self::vars()->baseUrl;
        self::$vars['wrapper'] = self::vars()->wrapper;

        if (self::settings()->community_logo_type === 'image') {
            self::$vars['communityLogo'] = self::output()->getPartial(
                'Global',
                'Globals',
                'LogoImage', [
                    'communityLogo' => self::settings()->community_logo,
                    'imagesetUrl' => self::user()->imagesetUrl(),
                    'communityTitle' => self::settings()->community_title,
                    'url' => self::seo()->url('index')
                ]);
        } else {
            self::$vars['communityLogo'] = self::output()->getPartial(
                'Global',
                'Globals',
                'LogoText',
                [
                    'url' => self::seo()->url('index'),
                    'communityTitle' => self::settings()->community_title
                ]
            );
        }

        return self::$vars;
    }
}