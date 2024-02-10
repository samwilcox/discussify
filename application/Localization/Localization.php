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

namespace Discussify\Localization;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class for management of all localization for the application.
 * 
 * @package Discussify\Localization
 */
class Localization extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Collection of all the localization for the configured localization package.
     * @var array
     */
    protected static $localization = [];

    /**
     * Constructor that initiates the loading of the localization.
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
     * Loads the localization for the configured localization package into the
     * localization array collection.
     */
    private function load() {
        $data = self::cache()->getData('localization');

        foreach ($data as $local) {
            if ($local->package_id === self::user()->languagepack_id()) {
                self::$localization[$local->category][$local->string_id] = $local->string_data;
            }
        }
    }

    /**
     * Returns the entire category of localization for the given category.
     * 
     * @param string $category - Category to get localization for.
     * @return array - Collection of localization for given category.
     */
    public static function getFullCategory($category) {
        return self::$localization[$category];
    }

    /**
     * Returns the words for the given category and string identifier.
     * 
     * @param string $category - Cateogry to get words from.
     * @param string $stringId - String identifier.
     * @return string - Localization string for given category and string identifier.
     */
    public static function getWords($category, $stringId) {
        return self::$localization[$category][$stringId];
    }

    /**
     * Replaces the given replacement string with a given replacement.
     * 
     * @param string $words - The localization string containing strings to be replaced.
     * @param string $toReplace - The item to replace.
     * @param string $replacement - The replacement for the replacement item.
     * @return string - Resulting localization with the item replaced.
     */
    public static function wordReplace($words, $toReplace, $replacement) {
        return \str_replace('{{' . $toReplace . '}}', $replacement, $words);
    }

    /**
     * Performs a quick replacement by including the category and string identifier to
     * eliminate the need to grab the words first and then replace.
     * 
     * @param string $category - Category to get words from.
     * @param string $stringId - String identifier.
     * @param string $toReplace - The item to replace.
     * @param string $replacement - The replacement for the replacement item.
     * @return string - Resulting localization with the item replaced.
     */
    public static function quickReplace($category, $stringId, $toReplace, $replacement) {
        return self::wordReplace(self::$localization[$category][$stringId], $toReplace, $replacement);
    }

    /**
     * Performs a quick replacement by including the category and string identifier to
     * eliminate the need to grab the words first and then replace. This method grabs the
     * localization from the specific language pack identifier.
     * 
     * @param int $languagepackId - Language pack identifier.
     * @param string $category - Category to get words from.
     * @param string $stringId - String identifier.
     * @param string $toReplace - The item to replace.
     * @param string $replacement - The replacement for the replacement item.
     * @return string - Resulting localization with the item replaced.
     */
    public static function quickReplaceSpecificId($languagepackId, $category, $stringId, $toReplace, $replacement) {
        self::wordReplace(self::getWordsSpecificId($category, $stringId, $languagepackId), $toReplace, $replacement);
    }

    /**
     * Performs multiple quick replacements for multiple replacement strings without the need
     * to grab the words first and then replace. This method grabs the localization from the
     * specific language pack identifier.
     * 
     * @param int $languagepackId - Language pack identifier.
     * @param string $category - Category to get words from.
     * @param string $stringId - String identifier.
     * @param array $replacementList - Associate array collection of items to replace.
     * @return string - Resulting localization with the item replaced.
     */
    public static function quickMultiWordsReplaceSpecificId($languagepackId, $category, $stringId, $replacementList = []) {
        $retVal = self::getWordsSpecificId($category, $stringId, $languagepackId);

        foreach ($replacementList as $k => $v) {
            $retVal = self::wordReplace($retVal, $k, $v);
        }

        return $retVal;
    }

    /**
     * Performs multiple quick replacements for multiple replacement strings without the need
     * to grab the words first and then replace.
     * 
     * @param string $category - Category to get words from.
     * @param string $stringId - String identifier.
     * @param array $replacementList - Associate array collection of items to replace.
     * @return string - Resulting localization with the item replaced.
     */
    public static function quickMultiWordReplace($category, $stringId, $replacementList = []) {
        $retVal = self::$localization[$category][$stringId];

        foreach ($replacementList as $k => $v) {
            $retVal = self::wordReplace($retVal, $k, $v);
        }

        return $retVal;
    }

    /**
     * Performs multiple word replacements on the given localization string.
     * 
     * @param string $words - The localization string to replace items within.
     * @param array $replacementList - Associate array collection of items to replace.
     * @return string - Resulting localization with the item replaced.
     */
    public static function multiWordReplace($words, $replacementList = []) {
        $retVal = $words;

        foreach ($replacementList as $k => $v) {
            $retVal = self::wordReplace($retVal, $k, $v);
        }

        return $retVal;
    }

    /**
     * Returns the localization words for a specific language pack identifier.
     * 
     * @param string $category - Category to get words from.
     * @param string $stringId - String identifier.
     * @param int $languagepackId - Language pack identifier.
     * @return string - Resulting localization.
     */
    public static function getWordsSpecificId($category, $stringId, $languagepackId) {
        $data = self::cache()->getData('localization');
        $words = [];

        foreach ($data as $local) {
            if ($local->package_id === $languagepackId) {
                $words[$local->category][$local->string_id] = $local->string_data;
            }
        }

        return $words[$category][$stringId];
    }

    /**
     * Takes in the given HTML or string and replaces all localization tags
     * with the proper localization words.
     * 
     * @param string $output - HTML or string.
     * @return string - HTML or string with replacements.
     */
    public static function outputWordsReplacement(&$output) {
        if (!$output) return;

        \preg_match_all('/\${\[([^]]+)\]\[([^]]+)\]}/', $output, $matches, PREG_SET_ORDER);
        $newMatch = [];

        foreach ($matches as $match) {
            $newMatch[] = [$match[1], $match[2]];
        }

        foreach ($newMatch as $m) {
            $output = \str_replace($m, self::getWords($m[0], $m[1]), $output);
        }
    }

    /**
     * Takes the given content and replaces all localization tags
     * with the proper localization words from the given language pack ID.
     * 
     * @param string $content - Content to replace tags within.
     * @param int $id - Language pack ID to use.
     * @return string - Content with replacements.
     */
    public static function outputWordsReplacementWithId(&$content, $id = null) {
        if (!$content) return;

        \preg_match_all('/\${\[([^]]+)\]\[([^]]+)\]}/', $content, $matches, PREG_SET_ORDER);
        $newMatch = [];

        foreach ($matches as $match) {
            $newMatch[] = [$match[1], $match[2]];
        }

        foreach ($newMatch as $m) {
            $content = \str_replace($m, self::getWordsSpecificId($m[0], $M[1]), $content);
        }
    }
}