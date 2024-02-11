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
     * Returns singleton instance of this class.
     * 
     * @return object - Singleton instance.
     */
    public static function i() {
        if (!self::$instance) self::$instance = new self;
        return self::$instance;
    }

    /**
     * Returns the source for the given theme.
     * 
     * @param array $options - Options for returning the theme.
     * @return string - Resulting theme source.
     */
    public static function get($options = null) {
        if ($options === null) return;
        
        if (isset($options['controller'])) {
            if (isset($options['action']) && isset($options['partial'])) {
                return self::getTheme([
                    'theme' => 'all',
                    'opts' => $options
                ]); 
            } else {
                $test = self::getTheme([
                    'theme' => 'two',
                    'opts' => $options
                ]); 

                var_dump($test); exit;
            }
        } else {
            if (isset($options['base']) && \strlen($options['base']) > 0) {
                return self::getTheme([
                    'theme' => 'base',
                    'opts' => $options
                ]); 
            } else {
                return;
            }
        }
    }

    /**
     * Finds the correct location of the source and then returns it.
     * 
     * @param array $options - Options for returning the theme.
     */
    private static function getTheme($options = null) {
        if ($options === null) return;

        switch ($options['theme']) {
            case 'all':
                if (self::settings()->theme_storage_method == 'db') {
                    return self::getFromDatabase($options['opts']['controller'], $options['opts']['action'], $options['opts']['partial']);
                } else {
                    return self::getFromCache($options['opts']['controller'], $options['opts']['action'], $options['opts']['partial']);
                }
                break;

            case 'two':
                if (self::settings()->theme_storage_method == 'db') {
                    return self::getFromDatabase($options['opts']['controller'], $options['opts']['action']);
                } else {
                    return self::getFromCache($options['opts']['controller'], $options['opts']['action']);
                }
                break;

            case 'base':
                if (self::settings()->theme_storage_method == 'db') {
                    return self::getFromDatabase(null, null, null, $options['opts']['base']);
                } else {
                    return self::getFromCache(null, null, null, $options['opts']['base']);
                }
                break;

            default:
                return;
        }
    }

    /**
     * Retrieves the source for the given theme from the database.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The action name.
     * @param string $partial - The name of the partial.
     * @param string $base - The name of the base (if its a base).
     * @return mixed - Theme source.
     */
    private static function getFromDatabase($controller = null, $action = null, $partial = null, $base = null) {
        if ($controller === null && $action === null && $partial === null && $base === null) return;
        
        $data = self::cache()->getData('theme_html');
        $source = '';
        
        foreach ($data as $html) {
            if ($html->theme_id === self::user()->themeId()) {
                if ($controller !== null) {
                    if ($action !== null && $partial !== null) {
                        $source = $html->html_source;
                    } elseif ($action !== null && $partial === null) {
                        $source = $html->html_source;
                    }
                } else {
                    if ($base !== null) {
                        $source = $html->html_source;
                    } else {
                        return;
                    }
                }
            }
        }

        return $source;
    }

    /**
     * Retrieves the source for the given theme from cache files.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The action name.
     * @param string $partial - The name of the partial.
     * @param string $base - The name of the base (if its a base).
     * @return mixed - Theme source.
     */
    private static function getFromCache($controller = null, $action = null, $partial = null, $base = null) {
        $cacheDir = ROOT_PATH . 'cache/' . CACHE_THEMES_DIR . '/theme-' . self::user()->themeId() . '/';
        $source = '';

        if ($controller !== null) {
            if ($action !== null && $partial !== null) {
                if (file_exists(\sprintf('%s%s-%s-%s.html', $cacheDir, $controller, $action, $base))) {
                    $source = self::file()->readFile(\sprintf('%s%s-%s-%s.html', $cacheDir, $controller, $action, $base));
                } else {
                    return;
                }
            } else {
                if (file_exists(\sprintf('%s%s-%s.html', $cacheDir, $controller, $action))) {
                    $source = self::file()->readFile(\sprintf('%s%s-%s.html', $cacheDir, $controller, $action));
                } else {
                    return;
                }
            }
        } else {
            if ($base !== null) {
                if (\strtolower($base) == 'main') {
                    $source = self::file()->readFile(\sprintf('%sBase.html', $cacheDir));
                } elseif (\strtolower($base) == 'print') {
                    $source = self::file()->readFile(\sprintf('%sPrintBase.html', $cacheDir));
                } else {
                    return;
                }
            } else {
                return;
            }
        }

        return $source;
    }
}