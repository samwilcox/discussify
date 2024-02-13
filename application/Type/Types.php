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
 * This class is responsible for retrieving the constants from the
 * extending class.
 * 
 * @package \Discussify\Type
 */
abstract class Types {
    /**
     * Array holding all the constants from the extending class.
     * @var array
     */
    protected static $constCacheArray = null;

    /**
     * Returns the constants from the extending class.
     * 
     * @return array - Constants array.
     */
    public static function getConstants() {
        if (self::$constCacheArray === null) self::$constCacheArray = [];
        $calledClass = \get_called_class();

        if (\array_key_exists($calledClass, self::$constCacheArray)) {
            $reflect = new \ReflectionClass($calledClass);
            self::$constCacheArray[$calledClass] = $reflect->getConstants();
        }

        return self::$constCacheArray[$calledClass];
    }

    /**
     * Returns whether the given name is a valid constant name.
     * 
     * @param string $name - Constant name.
     * @param bool $strict - Optional enable strict mode; set to true to enable.
     * @return bool - True if valid, false otherwise.
     */
    public static function isValidName($name, $strict = false) {
        $constants = self::getConstants();

        if ($strict) return \array_key_exists($name, $constants);
        $keys = \array_map('strtolower', \array_keys($constants));
        
        return \in_array(\strtolower($name), $keys);
    }

    /**
     * Returns whether the constant value is valid.
     * 
     * @param int $value - The value of the constant.
     * @param bool $strict - Optional enable strict mode; set to true to enable.
     * @return bool - True is valid value, false otherwise.
     */
    public static function isValidValue($value, $strict = false) {
        $values = \array_values(self::getConstants());
        return \in_array($value, $values, $strict);
    }
}