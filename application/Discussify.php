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

        \Discussify\Core\Settings::i();
        \Discussify\Data\Database::i()->connect();
        \Discussify\Data\Cache::i()->build();
        \Discussify\Core\Settings::i()->loadSettings();
        \Discussify\Core\Settings::i()->setupUrls();
        \Discussify\Core\Request::i();
        \Discussify\Core\Session::i()->load();
        \Discussify\Users\User::i();
        \Discussify\Localization\Localization::i();
        \Discussify\Core\Registry::i();
        \Discussify\Helpers\Utils::i()->initializeBreadcrumbs();
        \Discussify\Core\Vars::i()->parsed = [];

        $controller = isset(\Discussify\Core\Request::i()->controller) ? \ucfirst(\Discussify\Core\Request::i()->controller) : 'Index';
        $controller = $controller . 'Controller';
        $controllerNs = '\\Discussify\\Controllers\\' . $controller;
        $action = isset(\Discussify\Core\Request::i()->action) ? \ucfirst(\Discussify\Core\Request::i()->action) : 'index';

        $obj = new $controllerNs();
        $obj->$action();

        \session_write_close();
        \Discussify\Data\Database::i()->disconnect();
    }

    /**
     * Autoloader function to load classes automatically.
     * @param string $className The name of the class to autoload.
     */
    public static function autoloader($className) {
        if (\strpos($className, 'Discussify\\') !== 0) {
            return;
        }

        $class = \substr($className, \strlen('Discussify\\'));
        $classFile = \str_replace('\\', '/', $class) . '.php';
        $path = __DIR__ . '/' . $classFile;

        if (\file_exists($path)) {
            require $path;
        } else {
            throw new \Exception('Class file not found: ' . $path);
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
    public static function sanitizer() { return \Discussify\Helpers\Sanitizer::i(); }
    public static function theme() { return \Discussify\Themes\Theme::i(); }
    public static function type() { return \Discussify\Type\Types::i(); }
    public static function output() { return \Discussify\Output\Output::i(); }
    public static function seo() { return \Discussify\Url\Seo::i(); }
    public static function forumsHelper() { return \Discussify\Helpers\Forums::i(); }
    public static function block() { return \Discussify\Blocks\Block::i(); }
    public static function blocksHelper() { return \Discussify\Helpers\Blocks::i(); }
    public static function urls() { return \Discussify\Url\Url::i(); }
    public static function redirect() { return \Discussify\Url\Redirect::i(); }
    public static function textParsingHelper() { return \Discussify\Helpers\TextParsing::i(); }
    public static function buttonsHelper() { return \Discussify\Helpers\Buttons::i(); }
    public static function ajaxHelper() { return \Discussify\Helpers\Ajax::i(); }
}