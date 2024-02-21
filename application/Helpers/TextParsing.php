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
 * The class is responsible for various text-related parsing routines.
 * 
 * @package \Discussify\Helpers
 */
class TextParsing extends \Discussify\Application {
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
     * Returns the value before the hyphen in an URL that includes the ID and the name.
     * For example: http://www.example.com/index.php?/members/view/id/(21-john-smith)
     * Hence the part before the parenthesis shown above.
     * 
     * @param string $haystack - The string to get the data before the hyphen.
     * @return string - The value before the hyphen.
     */
    public static function beforeHyphen($haystack) {
        return \substr($haystack, 0, \strpos($haystack, '-'));
    }

    /**
     * Censors any bad words found within the given source.
     * This is dependent upon forum and user related settings.
     * 
     * @param mixed $source - The source to censor words within.
     * @param string $within - Optional of where the source is located within, such as forum.
     * @param int $id - Optional ID of the forum, topic, etc.
     * @param bool $force - Optional flag indicating whether to force censor ignoring other settings.
     * @return mixed - Source with censored or uncensored content dependent on settings.
     */
    public static function censor($source, $within = null, $id = false, $force = false) {
        $censor = false;
        $forceUsers = false;

        if (self::settings()->word_censoring_enabled) {
            if (self::settings()->word_censoring_force_users) {
                $forceUsers = true;
            }

            $censor = true;
        }

        if ($within != null) {
            if ($id != false) {
                switch ($within) {
                    case 'forums':
                        $data = self::cache()->getData('forums');

                        foreach ($data as $forum) {
                            if ($forum->id == $id) {
                                if ($forum->censoring_enabled == 1) {
                                    $censor = true;
                                }
                            }
                        }

                        if (self::user()->censoring('forums')) {
                            $censor = true;
                        } else {
                            $censor = false;
                        }
                        break;
                }
            }
        }

        if ($force) {
            $censor = true;
        }

        if ($censor) {
            $wordsToCensor = self::settings()->censored_words_list;
            $replacementChar = self::user()->censoring('replacementChar');
            $pattern = '/' . \implode('|', array_map('preg_quote', $wordsToCensor, array_fill(0, \count($wordsToCensor), '/'))) . '/i';

            $source = \preg_replace_callback($pattern, function($match) use ($replacementChar) {
                return \str_repeat($replacementChar, \mb_strlen($match[0]));
            }, $source);
        }

        return $source;
    }
}