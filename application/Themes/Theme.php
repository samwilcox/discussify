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

namespace Discussify\Themes;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * This class is the endpoint for theme related content and management.
 * 
 * @package \Discussify\Themes
 */
class Theme extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Populates the array collection of various CSS classes.
     */
    public function __construct() {
        self::user()->populateVarsUsingOutput([
            'noPhotoClass' => self::getThemePartial('Global', 'Class', 'NoPhoto'),
            'noPhotoThumbnailClass' => self::getThemePartial('Global', 'Class', 'NoPhotoThumbnail'),
            'photoClass' => self::getThemePartial('Global', 'Class', 'Photo'),
            'photoThumbnailClass' => self::getThemePartial('Global', 'Class', 'PhotoThumbnail'),
            'primaryButtonClass' => self::getThemePartial('Global', 'Class', 'PrimaryButton'),
            'secondaryButtonClass' => self::getThemePartial('Global', 'Class', 'SecondaryButton'),
            'tertiaryButtonClass' => self::getThemePartial('Global', 'Class', 'TertiaryButton'),
            'liteButtonClass' => self::getThemePartial('Global', 'Class', 'LiteButton')
        ]);
    }

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
     * Returns the theme base HTML source.
     * 
     * @return mixed - Base source.
     */
    public static function getThemeBase() {
        return self::file()->readFile(self::user()->themePath() . 'html/Base.html');
    }

    /**
     * Returns the theme print HTML source.
     * 
     * @return mixed - Print source.
     */
    public static function getThemePrint() {
        return self::file()->readFile(self::user()->themePath() . 'html/PrintBase.html');
    }

    /**
     * Returns the correct theme for the specified controller and action.
     * 
     * @param string $controller - Name of the controller.
     * @param string $action - Name of the action
     * @return mixed - Theme source.
     */
    public static function getTheme($controller, $action) {
        return self::file()->readFile(self::user()->themePath() . 'html/' . $controller . '/' . $controller . '-' . $action . '.html');
    }

    /**
     * Returns the correct theme for the specified controller, action, ans partial.
     * 
     * @param string $controller - Name of the controller.
     * @param string $action - Name of the action
     * @param string $partial - Name of the partial.
     * @return mixed - Theme source.
     */
    public static function getThemePartial($controller, $action, $partial) {
        return self::file()->readFile(self::user()->themePath() . 'html/' . $controller . '/' . $controller . '-' . $action . '-' . $partial . '.html');
    }
}