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

namespace Discussify\Core;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class with useful utilities for managing Date and Time routines.
 * 
 * @package Discussify\Core
 */
class DateTime extends \Discussify\Application {
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
     * Calculates the time difference between a given timestamp anmd the current time
     * and returns a human-readable representation of the difference.
     * 
     * If the time difference is less than 1 second, it returns "Just now".
     * If the time difference is less than 60 seconds, it returns the number of seconds ago.
     * If the time difference is less than 1 hour, it returns the number of minutes ago.
     * If the time difference is less than 24 hours, it returns the number of hours ago.
     * Otherwise, it returns a formatted date.
     * 
     * @param int $timestamp - The timestamp to compare against the current time.
     * @return string - A human-readable representation of the time difference. Null if more than 24 hours.
     */
    public static function getTimeDifference($timestamp) {
        $difference = \time() - $timestamp;

        if ($difference < 1) {
            return self::localization()->getWords('global', 'just_now');
        } elseif ($difference < 60) {
            return self::localization()->quickReplace('global', 'second' . $difference != 1 ? 's' : '' . '_ago', 'total', $difference);
        } elseif ($difference < 3600) {
            $minutes = \floor($difference / 60);
            return self::localization()->quickReplace('global', 'minute' . $minutes != 1 ? 's' : '' . '_ago', 'total', $minutes);
        } elseif ($difference < 86400) {
            $hours = \floor($difference . 3600);
            return self::localization()->quickReplace('global', 'hour' . $hours != 1 ? 's' : '' . '_ago', 'total', $hours);
        }

        return null;
    }

    /**
     * Converts the given string to the approriate timestamp.
     * 
     * @param string $string - String to conver to a timestamp.
     * @return int - Resulting timestamp value.
     */
    public static function convertStringToTimestamp($string) {
        $timeIntervals = [
            '1day' => '1 day ago',
            '2days' => '2 days ago',
            '3days' => '3 days ago',
            '4days' => '4 days ago',
            '5days' => '5 days ago',
            '6days' => '6 days ago',
            '1week' => '1 week ago',
            '2weeks' => '2 weeks ago',
            '3weeks' => '3 weeks ago',
            '1month' => '1 month ago',
            '3months' => '3 months ago',
            '6months' => '6 months ago',
            '9months' => '9 months ago',
            '1year' => '1 year ago',
            '2years' => '2 years ago'
        ];

        return isset($timeIntervals[$string]) ? \strtotime($timeIntervals[$string]) : null;
    }

    /**
     * Parses the given timestamp and returns a human-readable representation.
     * 
     * @param mixed $timestamp - Timestamp to parse (can be unix time or SQL timestamp format string).
     * @param array $options - Options to use when parsing the timestamp. Options are:
     *                         [dateOnly]: Return just the date itself.
     *                         [timeOnly]: Return just the time itself.
     *                         [timeAgo]: Return human-readable "time ago" if in range.
     *                         [memberId]: Use a specific member's timestamp settings.
     * 
     * @return string - Resulting representation of the given timestamp.
     */
    public static function parse($timestamp, $options = []) {
        if (!is_int($timestamp)) $timestamp = \strtotime($timestamp);

        $dateOnly = $timeOnly = $timeAgo = false;
        $specificMember = null;

        foreach ($options as $k => $v) {
            if ($k === 'dateOnly' && $v) {
                $dateOnly = true;
            } elseif ($k === 'timeOnly' && $v) {
                $timeOnly = true;
            } elseif ($k === 'timeAgo' && $v) {
                $timeAgo = true;
            } elseif ($k === 'memberId') {
                $specificMember = $v;
            }
        }

        $format = $dateOnly ? self::user()->getDateFormat($specificMember) :
            ($timeOnly ? self::user()->getTimeFormat($specificMember) :
            self::user()->getDateTimeFormat($specificMember));

        $memberTimeAgo = $specificMember === null ? self::user()->timeAgo() : self::user()->timeAgo($specificMember);

        if ($timeAgo) {
            $timeDifference = self::getTimeDifference($timestamp);
            return ($memberTimeAgo && $timeDifference !== null) ? $timeDifference : \date($format, $timestamp);
        }

        return \date($format, $timestamp);
    }
}