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

namespace Discussify\Output;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * This class handles all output to the user's web browser.
 * 
 * @package Discussify\Output
 */
class Output extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Collection of various HTTP status strings and codes.
     * @var array
     */
    protected static $httpStatusLegend = [];

    /**
     * Contructor that triggers the enumeration of the HTTP
     * status codes array.
     */
    public function __construct() {
        self::enumerateCodes();
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
     * Enumerates the HTTP status codes into the array.
     */
    private static function enumerateCodes() {
        self::$httpStatusLegend = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported'
        ];
    }

    /**
     * Outputs the given output data to the user's web browser using the
     * content type given.
     * 
     * @param mixed $output - The output to send to the web browser.
     * @param array $vars - Optional parameters for replacing tags.
     * @param string $contentType - The content type of the output.
     * @param int $httpStatusCode - The HTTP status code.
     * @param array $httpHeaders - Collection of headers for the request.
     */
    public static function output($output = '', $vars = [], $contentType = 'text/html', $httpStatusCode = 200, $httpHeaders = []) {
        self::localization()->outputWordsReplacement($output);

        if (\count($vars) > 0) {
            foreach ($vars as $k => $v) {
                $output = \str_replace('${' . $k . '}', $v, $output);
            }
        }

        @ob_end_clean();
        $output = \ltrim($output);

        \header('HTTP/1.0 ' . $httpStatusCode . ' ' . self::$httpStatusLegend[$httpStatusCode]);
        \header('Access-Control-Allow-Origin: *');
        \header('X-Discussify-SignIn: ' . self::user()->id());

        if (self::settings()->gzip_compression_enabled) {
            if (\substr_count($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip')) {
                \ob_start();
                self::vars()->gzip = true;
            } else {
                \ob_start();
                self::vars()->gzip = false;
            }
        } else {
            \ob_start();
            self::vars()->gzip = false;
        }

        if (!self::settings()->page_caching_enabled) {
            \header('Cache-Control: no-store, no-cache, must-revalidate');
            \header('Cache-Control: post-check=0, pre-check=0', false);
            \header('Pragma: no-cache');
            \header('Expires: 0');
        }

        print $output;

        \header('Content-Type: ' . $contentType . ';charset=UTF-8');

        foreach ($httpHeaders as $k => $v) {
            \header($k . ': ' . $v);
        }

        \header('Connection: close');

        @ob_end_flush();
        @flush();

        if (\function_exists('fastcgi_finish_request')) \fastcgi_finish_request();
    }

    /**
     * Renders the specified controller and action combination.
     * 
     * @param string $controller - The controller name.
     * @param string $action - The action name.
     * @param array $vars - Optional key value pairs collection.
     */
    public static function render($controller, $action, $vars = []) {
        $base = self::theme()->getThemeBase();
        $output = self::theme()->getTheme($controller, $action);
        $base = \str_replace('${body}', $output, $base);
        $globals = self::globals()->get();

        if (\count($globals) > 0) $vars = \array_merge($vars, $globals);

        $vars = \array_merge($vars, self::blocksHelper()->getBlocksOutputVars(\strtolower($controller), \strtolower($action)));

        self::output($base, $vars);
    }

    /**
     * Renders the specified controller, action and partial combination.
     * 
     * @param string $controller - The controller name.
     * @param string $action - The action name.
     * @param string $partial - The partial name.
     * @param array $vars - Optional key value pairs collection.
     */
    public static function renderAlt($controller, $action, $partial, $vars = []) {
        $base = self::theme()->getThemeBase();
        $output = self::theme()->getThemePartial($controller, $action, $partial);
        $base = \str_replace('${body}', $output, $base);
        $globals = self::globals()->get();

        if (\count($globals) > 0) $vars = \array_merge($vars, $globals);

        $vars = \array_merge($vars, self::blocksHelper()->getBlocksOutputVars(\strtolower($controller), \strtolower($action)));

        self::output($base, $vars);
    }

    /**
     * Renders the generic application error page.
     * 
     * @param array $vars - Optional key value pairs collection.
     */
    public static function renderError($vars = []) {
        $base = self::theme()->getThemeBase();
        $output = self::theme()->getThemePartial('Error', 'Error', 'Message');
        $base = \str_replace('${body}', $output, $base);
        $globals = self::globals()->get();

        if (\count($globals) > 0) $vars = \array_merge($vars, $globals);

        $vars = \array_merge($vars, self::blocksHelper()->getBlocksOutputVars(\strtolower($controller), \strtolower($action)));

        self::output($base, $vars);
    }

    /**
     * Renders the specified controller, action and partial combination.
     * 
     * @param string $controller - The controller name.
     * @param string $action - The action name.
     * @param string $partial - The partial name.
     * @param array $vars - Optional key value pairs collection.
     */
    public static function renderPartial($controller, $action, $partial, $vars = []) {
        $output = self::theme()->getThemePartial($controller, $action, $partial);
        $globals = self::globals()->get();

        if (\count($globals) > 0) $vars = \array_merge($vars, $globals);

        $vars = \array_merge($vars, self::blocksHelper()->getBlocksOutputVars(\strtolower($controller), \strtolower($action)));

        self::output($output, $vars);
    }

    /**
     * Renders the print-friendly page.
     * 
     * @param string $controller - The controller name.
     * @param string $action - The action name.
     * @param string $partial - The partial name.
     * @param array $vars - Optional key value pair collection.
     */
    public static function renderPrint($controller, $action, $partial, $vars = []) {
        $base = self::theme()->getThemePrint();
        $output = self::theme()->getThemePartial($controller, $action, $partial);
        $base = \str_replace('${body}', $output, $base);
        $globals = self::globals()->get();

        if (\count($globals) > 0) $vars = \array_merge($vars, $globals);

        $vars = \array_merge($vars, self::blocksHelper()->getBlocksOutputVars(\strtolower($controller), \strtolower($action)));

        self::output($base, $vars);
    }

    /**
     * Renders the given source.
     * 
     * @param mixed $source - The source to output.
     * @param string $contentType - The source content type string.
     */
    public static function renderSource($source, $contentType) {
        self::output($source, [], $contentType);
    }

    /**
     * Returns the given partial theme source.
     * 
     * @param string $controller - The controller name.
     * @param string $action - The action name.
     * @param string $partial - The partial name.
     * @param array $vars - Optional key value pair collection.
     * @return mixed - Partial theme source.
     */
    public static function getPartial($controller, $action, $partial, $vars = []) {
        $output = self::theme()->getThemePartial($controller, $action, $partial);

        if (\count($vars) > 0) {
            foreach ($vars as $k => $v) {
                $output = \str_replace('${' . $k . '}', $v, $output);
            }
        }

        self::localization()->outputWordsReplacement($output);

        return $output;
    }
}