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

namespace Discussify\Users;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class for management of all users, guests and members alike.
 * 
 * @package Discussify\Users
 */
class User extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * User data object.
     * @var object
     */
    protected static $user;

    /**
     * Constructor that calls a few initializion methods.
     */
    public function __construct() {
        self::constructData();
        self::userDiscovery();
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
     * Constructs the user class object with all the required fields.
     */
    private function constructData() {
        self::$user = (object) [
            'id' => 0,
            'signedIn' => false,
            'username' => 'Guest',
            'email' => null,
            'languagepackId' => null,
            'themeId' => null,
            'primaryGroup' => null,
            'secondaryGroups' => [],
            'timeZone' => null,
            'dateFormat' => null,
            'timeFormat' => null,
            'dateTimeFormat' => null,
            'timeAgo' => true,
            'themePath' => null,
            'themeUrl' => null,
            'imagesetFolder' => null
        ];
    }

    /**
     * Discovers whether the current user is a member or just a guest.
     */
    private function userDiscovery() {
        if (isset($_SESSION['discussify_id'])) {
            self::$user->signedIn = true;
            self::$user->id = $_SESSION['discussify_id'];
        } else {
            self::$user->signedIn = false;
            self::$user->id = 0;
        }

        $data = self::cache()->getData('members');
        $found = false;

        foreach ($data as $member) {
            if ($member->id === self::$user->id) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            self::$user->signedIn = false;
            self::$user->id = 0;
        }

        self::initializeData();
    }

    /**
     * Initializes all needed data for the user, either it be a member or guest.
     */
    private function initializeData() {
        if (self::$user->signedIn) {
            $data = self::cache()->getData('members');

            foreach ($data as $member) {
                if ($member->id === self::$user->id) {
                    self::$user->primaryGroup = $member->primary_group;
                    self::$user->secondaryGroups = \strlen($member->secondary_groups) > 0 ? \unserialize($member->secondary_groups) : '';
                    self::$user->timeZone = $member->time_zone;
                    self::$user->dateFormat = $member->date_format;
                    self::$user->dateTimeFormat = $member->date_time_format;
                    self::$user->timeAgo = $member->time_ago;
                    self::$user->email = $member->email;
                    self::$user->username = $member->username;
                    self::$user->languagepackId = $member->languagepack_id;
                    self::$user->themeId = $member->theme_id;
                    break;
                }
            }
        } else {
            if (isset($_COOKIE['discussify_languagepack_id'])) {
                self::$user->languagepackId = $_COOKIE['discussify_languagepack_id'];
            } else {
                self::$user->languagepackId = self::settings()->default_languagepack_id;
            }

            if (isset($_COOKIE['discussify_theme_id'])) {
                self::$user->themeId = $_COOKIE['discussify_theme_id'];
            } else {
                self::$user->themeId = self::settings()->default_theme_id;
            }

            self::$user->primaryGroup = self::settings()->guest_group_id;
            self::$user->secondaryGroups = [];
            self::$user->timeZone = self::settings()->time_zone;
            self::$user->dateFormat = self::settings()->date_format;
            self::$user->timeFormat = self::settings()->time_format;
            self::$user->dateTimeFormat = self::settings()->date_time_format;
            self::$user->timeAgo = self::settings()->time_ago;
            self::$user->username = 'Guest';
        }

        $data = self::cache()->getData('installed_themes');
        
        foreach ($data as $theme) {
            if ($theme->id === self::$user->themeId) {
                $folder = $theme->folder;
                $imagesetFolder = $theme->imageset_folder;
                break;
            }
        }

        self::$user->themePath = ROOT_PATH . 'themes/' . $folder. '/';
        self::$user->themeUrl = self::vars()->baseUrl . '/themes/' . $folder;
        self::$user->imagesetUrl = self::vars()->baseUrl . '/public/imagesets/' . $imagesetFolder;

        \date_default_timezone_set(self::$user->timeZone);
    }

    /**
     * Returns the given database field for the given member identifier.
     * 
     * @param string $field - Field to return.
     * @param int $id - Member identifier (default: signed in member).
     * @return mixed - Field data. Null if field is not found.
     */
    public static function getMemberData($field, $id = null) {
        if ($id === null) $id = self::$user->id;

        $data = self::cache()->getData('members');
        $found = false;

        foreach ($data as $member) {
            if ($member->id === $id) {
                $found = true;
                $value = $member->$field;
                break;
            }
        }

        if (!$found) return null;

        return $value;
    }

    /**
     * Returns a collection of member data according to the given fields and member
     * identifier.
     * 
     * @param array $fields - Collection of fields to return.
     * @param int $id - Member identifier (default: signed in member).
     * @return object - Object containing data. Null if not found.
     */
    public static function getMemberDataCollection($fields = [], $id = null) {
        if ($id === null) $id = self::$user->id;

        $retVal = new \stdClass();
        $data = self::cache()->getData('members');
        $found = false;

        foreach ($data as $member) {
            if ($member->id === $id) {
                $found = true;

                if (\count($fields) > 0) {
                    foreach ($fields as $field) {
                        $retVal->$field = $member->$field;
                    }
                }

                break;
            }
        }

        if (!$found) return null;

        return $retVal;
    }

    /**
     * Returns the given member's username.
     * 
     * @param int $id - The member identifier (default: signed in member).
     * @return string - The username for the given user.
     */
    public static function username($id = null) {
        if ($id === null) return self::$user->username;

        $data = self::getMemberData('username', $id);

        if ($data === null || \strlen($data) < 1) return self::localization()->getWords('global', 'unknown');

        return $data;
    }

    /**
     * Returns the given user's time zone string.
     * 
     * @param int $id - The member identifier (default: signed in member).
     * @return string - Time zone string.
     */
    public static function getTimeZone($id = null) {
        if ($id === null) $id = self::$user->timeZone;

        $data = self::getMemberData('time_zone', $id);

        if ($data === null || \strlen($data) < 1) return '?';

        return $data;
    }

    /**
     * Returns the given user's date format string.
     * 
     * @param int $id - The member identifier (default: signed in member).
     * @return string - Date format string. 
.    */
    public static function getDateFormat($id = null) {
        if ($id === null) return self::$user->dateFormat;

        $data = self::getMemberData('date_format', $id);

        if ($data === null || \strlen($data) < 1) return '?';

        return $data;
    }

    /**
     * Returns the given user's time format string.
     * 
     * @param int $id - The member identifier (default: signed in member).
     * @return string - Time format string.
     */
    public static function getTimeFormat($id = null) {
        if ($id === null) return self::$user->timeFormat;

        $data = self::getMemberData('time_format', $id);

        if ($data === null || \strlen($data) < 1) return '?';

        return $data;
    }

    /**
     * Returns the given user's date and time format string.
     * 
     * @param int $id - The member identifier (default: signed in member).
     * @return string - Date time format string.
     */
    public static function getDateTimeFormat($id = null) {
        if ($id === null) return self::$user->dateTimeFormat;

        $data = self::getMemberData('date_time_format', $id);

        if ($data === null || \strlen($data) < 1) return '?';

        return $data;
    }

    /**
     * Returns the given user's time ago flag value.
     * 
     * @param int $id - The member identifier (defaukt: signed in member).
     * @return bool - True if enabled, false otherwise.
     */
    public static function timeAgo($id = null) {
        if ($id === null) return self::$user->timeAgo;

        $data = self::getMemberData('time_ago', $id);

        if ($data === null || \strlen($data) < 1) return '?';

        return $data;
    }

    /**
     * Returns the theme ID.
     * 
     * @return int - Theme ID.
     */
    public static function themeId() {
        return self::$user->themeId;
    }

    /**
     * Returns the ID of the user.
     * 
     * @return int - User's identifier.
     */
    public static function id() {
        return self::$user->id;
    }

    /**
     * Returns the language pack ID.
     * 
     * @return int - Language pack ID.
     */
    public static function languagepackId() {
        return self::$user->languagepackId;
    }

    /**
     * Returns the path to the themes directory.
     * 
     * @return string - Path to theme folder.
     */
    public static function themePath() {
        return self::$user->themePath;
    }

    /**
     * Returns the URL to the themes directory.
     * 
     * @return string - URL to themes directory.
     */
    public static function themeUrl() {
        return self::$user->themeUrl;
    }

    /**
     * Returns the URL to the imageset.
     * 
     * @return string - URL to imageset.
     */
    public static function imagesetUrl() {
        return self::$user->imagesetUrl;
    }
 }