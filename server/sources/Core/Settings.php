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
 * Class that manages all the application settings.
 * 
 * @package Discussify\Core
 */
class Settings extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Collection of application settings.
     * @var array
     */
    protected static $settings = [];

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
     * Loads the application settings from the database.
     */
    public static function loadSettings() {
        $data = self::cache()->getData('application_settings');

        foreach ($data as $setting) {
            switch ($setting->value_type) {
                case 'bool':
                    self::$settings[$setting->key] = (\strtolower($setting->value) == 'true') ? true : false;
                    break;

                case 'int':
                    self::$settings[$setting->key] = (int)$setting->value;
                    break;

                case 'json':
                    self::$settings[$setting->key] = \strlen($setting->value) > 0 ? \json_decode($setting->value) : '';
                    break;

                case 'serialized':
                    self::$settings[$setting->key] = \strlen($setting->value) > 0 ? \unserialize($setting->value) : '';
                    break;

                default:
                    self::$settings[$setting->key] = $setting->value;
                    break;
            }
        }
    }

    /**
     * Magic function that returns the value for the given key.
     * 
     * @param string $key - Key of setting.
     * @return mixed - value for given key, null is key does not exist.
     */
    public function __get($key) {
        if (\array_key_exists($key, self::$settings)) return self::$settings[$key];
        return null;
    }

    /**
     * Magic function that returns whether the given key exists.
     * 
     * @param string $key - Key of setting to check.
     * @return bool - true if exists, false otherwise.
     */
    public function __isset($key) {
        if (\array_key_exists($key, self::$settings)) {
            return true;
        }

        return false;
    }
}