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

namespace Discussify\Math;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class for various math related calculations and other math related routines.
 * 
 * @package Discussify\Math
 */
class Math extends \Discussify\Application {
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
     * Calculates the person's age using the given month, day, and year.
     * 
     * @param int $month - Month number.
     * @param int $day - Day number.
     * @param int $year - Year number.
     * @return int - Calculated age.
     */
    public static function calculateAge($month, $day, $year) {
        $time = \mktime(0, 0, 0, $month, $day, $year);
        $calc = ($time < 0) ? (\time() + ($time * -1)) : \time() - $time;
        $year = 60 * 60 * 24 * 365;

        return \floor($calc / $year);
    } 

    /**
     * Formats a given number to a more human-readable number.
     * For example, 1,354 would become 1.35K.
     * 
     * @param int $number - The number to format.
     * @param int $decimals - The total decimal places (default: 2).
     * @return string - Formatted number.
     */
    public static function formatNumber($number, $decimals = 2) {
        if ($number < 1000) {
            return $number;
        } elseif ($number < 1000000) {
            return self::formatWithDecimalPlaces($number / 1000, $decimals, 1) . 'K';
        } elseif ($number < 1000000000) {
            return self::formatWithDecimalPlaces($number / 1000000, $decimals, 1) . 'M';
        } elseif ($number < 1000000000000) {
            return self::formatWithDecimalPlaces($number / 1000000000, $decimals, 1) . 'B';
        } else {
            return self::formatWithDecimalPlaces($number / 1000000000000, $decimals, 1) . 'T';
        }
    }

    /**
     * Formats a number with the given decimal places, but if decimal places is 2
     * and the second decimal place is 0, formats it with 1 decimal place instead.
     * 
     * @param float $number - The number to format.
     * @param int $decimals - The number of decimal places to use.
     * @param int $minDecimalPlaces - The minimum number of places to use.
     * @return string The formatted number.
     */
    private static function formatWithDecimalPlaces($number, $decimals, $minDecimalPlaces) {
        $formattedNumber = \number_format($number, $decimals);

        if ($decimals === 2 && \substr($formattedNumber, -1) === '0') {
            return \number_format($number, $minDecimalPlaces);
        }

        return $formattedNumber;
    }

    /**
     * Calculates the total number of entries to load for the next request.
     * 
     * @param array|int $total - The array containing the entries or a total integer.
     * @param int $limit - The limit of how many entries to load.
     * @param int $index - The last index.
     * @return int - The total number of entries to load.
     */
    public static function calculateEntriesToLoad($total, $limit, $index) {
        if (\is_array($total)) {
            $totalEntries = \count($total);
        } else {
            $totalEntries = $total;
        }

        if ($index < $totalEntries) {
            $remainingEntries = $totalEntries - $index;
            $entriesToLoad = \min($remainingEntries, $limit);
        } else {
            $entriesToLoad = 0;
        }

        return $entriesToLoad;
    }
}