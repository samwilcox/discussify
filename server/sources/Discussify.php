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

namespace Discussify;

/**
 * The main application class responsible for initializing the application.
 */
class Application {

    /**
     * Initializes the application.
     */
    public static function run() {
        require_once ('Static.php');

        \ignore_user_abort(true);
        \date_default_timezone_set('UTC');
        \spl_autoload_register('self::autoloader', true, true);


    }

    /**
     * Autoloader function to load classes automatically.
     * @param string $className The name of the class to autoload.
     */
    public static function autoloader($className) {
        $bits = \explode('\\', $className);
        $class = \array_pop($bits);

        if ($bits[0] != 'Discussify') return;

        \array_shift($bits);

        $path = \realpath(\dirname(__FILE__) . '/' . \str_replace('\\', '/', implode('\\', $bits)) . '/' . '.php');

        if (\strlen($path) > 0) {
            require_once ($path);
        }
    }
}