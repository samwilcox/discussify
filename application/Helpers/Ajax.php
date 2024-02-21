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

namespace Discussify\Helpers;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Helpers methods for AJAX requests.
 * 
 * @package \Discussify\Helpers
 */
class Ajax extends \Discussify\Application {
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
     * Sends the given JSON back to the client.
     * 
     * @param array $out - Array collection of data.
     */
    public static function send($out) {
        $json = \json_encode($out);
        self::output()->renderSource($json, \Discussify\Type\ContentType::JSON);
        exit();
    }

    /**
     * Returns the incoming data from the PHP input string.
     * 
     * @return object  - Input collection.
     */
    public static function getInput() {
        $obj = new \stdClass();
        $input = \file_get_contents('php://input');
        \parse_str($input, $out);

        foreach ($out as $key => $value) {
            $obj->$key = $value;
        }

        return $obj;
    }

    /**
     * Returns the button for loading more topics.
     * No button will be returned if there is not enough topics that
     * loading is neccessary.
     * 
     * @param int $forumId - Optional specific forum ID.
     * @return mixed - Load more button source.
     */
    public static function topicsLoadMore($forumId = null) {
        $data = self::cache()->getData('topics');
        $limit = self::user()->entryLimit('topics');
        $button = '';

        if (\count($data) > $limit) {
            $button = self::output()->getPartial(
                'AjaxHelper',
                'LoadTopics',
                'Button'
            );
        }

        return $button;
    }
}