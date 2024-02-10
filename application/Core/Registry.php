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
 * Registry class that allows us to store key/value pairs, but are saved to
 * the database so that this data can be retreived at a later point in time.
 * 
 * @package Discussify\Core
 */
class Registry extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Collection of registry key value pairs.
     * @var array
     */
    protected static $vars = [];

    /**
     * Constructor that initializes the population of the registry key value pairs.
     */
    public function __construct() {
        self::load();
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
     * Loads the registry data from the database into our key value pairs
     * collection.
     */
    private function load() {
        $data = self::cache()->getData('registry');

        foreach ($data as $reg) {
            switch ($reg->value_type) {
                case 'bool':
                    self::$vars[$reg->name] = $reg->value == 'true' ? true : false;
                    break;

                case 'int':
                    self::$vars[$reg->name] = (int)$reg->value;
                    break;

                case 'json':
                    self::$vars[$reg->name] = \strlen($reg->value) > 0 ? \json_decode($reg->value) : null;
                    break;

                case 'serialized':
                    self::$vars[$reg->name] = \strlen($reg->value) > 0 ? \unserialize($reg->value) : null;
                    break;

                default:
                    self::$vars[$reg->name] = $reg->value;
                    break;
            }
        }
    }

    /**
     * Saves the given key value pair to the database.
     * 
     * @param string $key - Key of the pair to save.
     * @param mixed $value - Value for the key.
     * @param string $type - type of value (string, int, bool, etc.)
     */
    public static function saveKeyValuePair($key, $value, $type = 'string') {
        $data = self::cache()->getData('registry');
        $exists = false;
        $id = null;

        foreach ($data as $reg) {
            if ($reg->name == $key) {
                $exists = true;
                $id = $reg->id;
                break;
            }
        }

        if ($exists) {
            self::db()->query(self::queries()->updateRegistry(), ['id' => $id, 'value' => $value, 'type' => $type]);
        } else {
            self::db()->query(self::queries()->insertIntoRegistry(), ['name' => $key, 'value' => $value, 'type' => $type]);
        }

        self::cache()->update('registry');
    }

    /**
     * Magic function to set a key value pair in the registry.
     * 
     * @param string $key - Key name to set.
     * @param array $value - array with type and value.
     */
    public function __set($key, $value) {
        if ($value['type'] === null) $value['type'] = 'string';
        self::saveKeyValuePair($key, $value['value'], $value['type']);
    }

    /**
     * Magic function to get a key value pair from the registry.
     * 
     * @param string $key - Key to get.
     * @return mixed - Key value for given key. Null is key does not exist.
     */
    public function __get($key) {
        if (\array_key_exists($key, self::$vars)) return self::$vars[$key];
        return null;
    }

    /**
     * Magic function to return whether the given key exists.
     * 
     * @param string $key - Key to check.
     * @return bool - True if it exists, false otherwise.
     */
    public function __isset($key) {
        if (\array_key_exists($key, self::$vars)) {
            return true;
        }

        return false;
    }
}