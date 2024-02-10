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
    
    // Singleton Instances Of Libraries
    public static function agent() { return \Discussify\Core\Agent::i(); }
    public static function cookies() { return \Discussify\Core\Cookies::i(); }
    public static function registry() { return \Discussify\Core\Registry::i(); }
    public static function request() { return \Discussify\Core\Request::i(); }
    public static function session() { return \Discussify\Core\Session::i(); }
    public static function settings() { return \Discussify\Core\Settings::i(); }
    public static function vars() { return \Discussify\Core\Vars::i(); }
    public static function db() { return \Discussify\Data\Database::i(); }
    public static function queries() { return \Discussify\Data\Queries::i(); }
    public static function cache() { return \Discussify\Data\Cache::i(); }
    public static function localization() { return \Discussify\Localization\Localization::i(); }
    public static function user() { return \Discussify\Users\User::i(); }
    public static function dateTime() { return \Discussify\Core\DateTime::i(); }
    public static function file() { return \Discussify\Files\File::i(); }
    public static function math() { return \Discussify\Math\Math::i(); }
    public static function utils() { return \Discussify\Helpers\Utils::i(); }
    public static function apiForums() { return \Discussify\Api\Forums::i(); }
    public static function apiUsers() { return \Discussify\Api\Users::i(); }
    public static function globals() { return \Discussify\Core\Globals::i(); }

}