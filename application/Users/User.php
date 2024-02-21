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
            'imagesetFolder' => null,
            'classes' => (object) [
                'noPhotoThumbnail' => null,
                'photo' => null,
                'photoThumbnil' => null,
                'noPhoto' => null,
                'primaryButton' => null,
                'secondaryButton' => null,
                'tertiaryButton' => null,
                'liteButton' => null,
            ],
            'photoId' => null,
            'photoType' => null,
            'photoGalleryId' => null,
            'topicsSortBy' => null,
            'limits' => (object) [
                'topics' => null,
                'posts' => null
            ],
            'charLimits' => (object) [
                'topicsList' => null
            ],
            'censoring' => (object) [
                'forums' => false,
                'comments' => false,
                'replacementChar' => '*'
            ]
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
                    self::$user->photoId = $member->photo_id;
                    self::$user->photoType = $member->photo_type;
                    self::$user->photoGalleryId = $member->gallery_id;
                    self::$user->topicsSortBy = $member->topics_sort_by;
                    self::$user->limits->topics = \unserialize($member->entry_limits)['topics'];
                    self::$user->limits->posts = \unserialize($member->entry_limits)['posts'];
                    self::$user->charLimits->topicsList = (int)$member->topics_list_char_limit;
                    self::$user->censoring->forums = \unserialize($member->censoring)['forumsEnabled'];
                    self::$user->censoring->comments = \unserialize($member->censoring)['commentsEnabled'];
                    self::$user->censoring->replacementChar = \unserialize($member->censoring)['replacementChar'];
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
            self::$user->limits->topics = self::settings()->entry_limits['topics'];
            self::$user->limits->posts = self::settings()->entry_limits['posts'];
            self::$user->charLimits->topicsList = self::settings()->topics_list_char_limit;
            self::$user->censoring->forums = self::settings()->word_censoring_enabled;
            self::$user->censoring->comments = self::settings()->word_censoring_enabled;
            self::$user->censoring->replacementChar = self::settings()->word_censoring_replacement_char;
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
     * Populates the class vars with the proper classes.
     * 
     * @param array $arr - Collection of vars.
     */
    public static function populateVarsUsingOutput($arr) {
        self::$user->classes->noPhoto = $arr['noPhotoClass'];
        self::$user->classes->noPhotoThumbnail = $arr['noPhotoThumbnailClass'];
        self::$user->classes->photo = $arr['photoClass'];
        self::$user->classes->photoThumbnail = $arr['photoThumbnailClass'];
        self::$user->classes->primaryButton = $arr['primaryButtonClass'];
        self::$user->classes->secondaryButton = $arr['secondaryButtonClass'];
        self::$user->classes->tertiaryButton = $arr['tertiaryButtonClass'];
        self::$user->classes->liteButton = $arr['liteButtonClass'];
    }

    /**
     * Returns the given database field for the given member identifier.
     * 
     * @param string $field - Field to return.
     * @param int $id - Member identifier (default: signed in member).
     * @return mixed - Field data. Null if field is not found.
     */
    public static function getMemberData($field, $id = null) {
        if ($id == null) $id = self::$user->id;

        $data = self::cache()->getData('users');
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
        if ($id == null) $id = self::$user->id;

        $retVal = new \stdClass();
        $data = self::cache()->getData('users');
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
     * Returns whether the current user has permission for the given feature.
     * 
     * @param string $feature - The name of the feature to check.
     * @return bool - True if valid permissions, false otherwise.
     */
    public static function hasFeaturePermissions($feature) {
        // TO-DO: FINISH AT A LATER POINT - TRUE FOR DEVELOPMENT.
        return true;
    }

    /**
     * Gathers the permissions for the specified forum for the specified user
     * and returns them.
     * 
     * @param int $forumId - The forum identifier.
     * @param int $id - The user identifier (default: signed in user).
     */
    public static function getForumPermissions($forumId, $id = null) {
        if ($id === null) $id = self::$user->id;

        $permissions = new \stdClass();

        // TODO: True for development purposes - add more code for the rest of the function later. :-)
        $permissions->viewForum = true;
        $permissions->viewTopics = true;
        $permissions->postTopics = true;
        $permissions->postReply = true;
        $permissions->uploadAttachments = true;
        $permissions->downloadAttachments = true;
        $permissions->createPoll = true;
        $permissions->castPoll = true;

        return $permissions;
    }

    /**
     * Returns whether or not the user has access to a permission in a given forum.
     * 
     * @param int $forumId - The forum identifier.
     * @param string $permission - The permission to check.
     * @param int $id - The user identifier (default: signed in user).
     * @return bool - True if permission is granted, false otherwise.
     */
    public static function getForumPermission($forumId, $permission, $id = null) {
        return self::getForumPermissions($forumId, $id)->$permission;
    }

    /**
     * Determines whether the user has access to any forum for the specified
     * permission.
     * 
     * @param int $id - The user identifier (default: signed in user).
     * @param string $permission - The permission to check.
     * @return bool - true if access to at least one forum, false otherwise.
     */
    public static function getForumPermissionsCheck($id = null, $permission = 'view') {
        $data = self::cache()->getData('forums');
        $access = false;    

        if (\count($data) == 0) {
            return false;
        }

        foreach ($data as $forum) {
            if (self::getForumPermissions($forum->id, $id)) {
                $access = true;
                break;
            }
        }

        return $access;
    }

    /**
     * Returns the collection of block related settings.
     * 
     * @return array - Collection of settings; if not signed in, returns defaults.
     */
    public static function blocks() {
        $blocks = [];

        if (self::$user->signedIn) {
            if (self::hasFeaturePermissions(\Discussify\Type\FEATURE::BLOCKS)) {
                $data = self::getMemberData('blocks');

                if ($data == null) {
                    $blocks = self::settings()->blocks;
                }
            } else {
                $blocks = \unserialize($data);
            }
        } else {
            $blocks = self::settings()->blocks;
        }

        return $blocks;
    }

    /**
     * Builds and then returns the profile link for the user.
     * 
     * @param int $id - The user identifier (default: signed in user).
     * @param string $tooltip - Optional tooltip for the link.
     * @param string $seperator - Optional link seperator.
     * @return mixed - Profile link.
     */
    public static function profileLink($id = null, $tooltip = null, $seperator = null) {
        if ($id == null) $id = self::$user->id;

        return self::output()->getPartial(
            'Users',
            'Profile',
            'Link', [
                'url' => self::seo()->url('members', 'view', ['id' => self::urls()->getDualUrl($id, self::username($id))]),
                'username' => self::username($id),
                'seperator' => $seperator == null ? '' : $seperator,
                'tooltip' => $tooltip == null ? self::localization()->quickReplace('user', 'userLinkTooltip', 'username', self::username($id)) : $tooltip
            ]
        );
    }

    /**
     * Returns the correct CSS class for the given type of photo.
     * 
     * @param array $options - Collection of options.
     * @return string - CSS class.
     */
    private static function photoClass($options) {
        $class = '';

        switch ($options['type']) {
            case 'nophoto':
                $class = $options['thumbnail'] ? self::$user->classes->noPhotoThumbnail : self::$user->classes->noPhoto;
                if ($options['custom']) $class = $options['customClass'];
                break;

            case 'photo':
                $class = $options['thumbnail'] ? self::$user->classes->photoThumbnail : self::$user->classes->photo;
                if ($options['custom']) $class = $options['customClass'];
                break;
        }

        return $class;
    }

    /**
     * Returns the photo for the given options.
     * 
     * @param array $options - Collection of options.
     * @return mixed - Photo source.
     */
    private static function getPhoto($options) {
        switch ($options['type']) {
            case 'nophoto':
                return self::output()->getPartial(
                    'Users',
                    'Photo',
                    'NoPhoto', [
                        'letter' => 'G',
                        'backgroundColor' => self::settings()->no_photo_colors['G']['background'],
                        'textColor' => self::settings()->no_photo_colors['G']['text'],
                        'linkBegin' => '',
                        'linkEnd' => '',
                        'class' => $options['class']
                    ]
                );
                break;

            case 'nophotomember':
                return self::output()->getPartial(
                    'Users',
                    'Photo',
                    'NoPhoto', [
                        'letter' => $options['letter'],
                        'backgroundColor' => self::settings()->no_photo_colors[$options['letter']]['backgroundColor'],
                        'textColor' => self::settings()->no_photo_colors[$options['letter']]['text'],
                        'linkBegin' => $options['linkBegin'],
                        'linkEnd' => $options['linkEnd'],
                        'class' => $options['class']
                    ]
                );
                break;

            case 'photo':
                return self::output()->getPartial(
                    'Users',
                    'Photo',
                    'Photo', [
                        'url' => $options['url'],
                        'linkBegin' => $options['linkBegin'],
                        'linkEnd' => $options['linkEnd'],
                        'class' => $options['class']
                    ]
                );
                break;
        }
    }

    /**
     * Returns the profile photo for the specified member.
     * 
     * @param int $id - The member identifier.
     * @param array $options - Collection of options.
     * @return mixed - Profile phot source.
     */
    public static function profilePhoto($id = null, $options = []) {
        $user = self::getMemberDataCollection(['photo_type', 'photo_id'], $id);
        $linkBegin = '';
        $linkEnd = '';
        $custom = false;
        $customClass = '';

        if (!\array_key_exists('thumbnail', $options)) {
            $options['thumbnail'] = false;
        }

        if (\array_key_exists('link', $options)) {
            if ($options['link']) {
                $linkBegin = self::output()->getPartial(
                    'Users',
                    'Photo',
                    'LinkBegin', [
                        'url' => self::seo()->url('members', 'view', ['id' => self::urls()->getDualUrl($id, self::username($id))]),
                        'title' => \array_key_exists('title', $options) ? $options['title'] : self::localization()->quickReplace('users', 'memberTooltip', 'username', self::username($id))
                    ]
                );
    
                $linkEnd = self::output()->getPartial('Users', 'Photo', 'LinkEnd');
            }
        }

        if (\array_key_exists('customClass', $options) && \strlen($options['customClass'])) {
            $custom = true;
            $customClass = $options['customClass'];
        }


        if ($id == 0 || $user == null) {
            return self::getPhoto([
                'type' => 'nophoto',
                'linkBegin' => $linkBegin,
                'linkEnd' => $linkEnd,
                'class' => self::photoClass(['type' => 'nophoto', 'thumbnail' => $options['thumbnail'], 'custom' => $custom, 'customClass' => $customClass])
            ]);
        }

        $firstChar = \strtoupper(\substr(self::username($id), 0, 1));

        if ($user->photo_id == 0) {
            return self::getPhoto([
                'type' => 'nophotomember',
                'letter' => $firstChar,
                'linkBegin' => $linkBegin,
                'linkEnd' => $linkEnd,
                'class' => self::photoClass(['type' => 'nophoto', 'thumbnail' => $options['thumbnail'], 'custom' => $custom, 'customClass' => $customClass])
            ]);
        } else {
            switch ($user->photo_type) {
                case 'uploaded':
                    $photo = '';
                    $photos = self::cache()->getData('user_photos');

                    foreach ($photos as $obj) {
                        if ($obj->id == $user->photo_id) $photo = $obj->file_name;
                    }
                    
                    $photoUrl = self::vars()->baseUrl . '/' . self::settings()->upload_dir . '/' . self::settings()->profile_photos_dir . '/' . $id.  '/' . $photo;

                    if (!@file_get_contents($photoUrl)) {
                        return self::getPhoto([
                            'type' => 'nophotomember',
                            'letter' => $firstChar,
                            'linkBegin' => $linkBegin,
                            'linkEnd' => $linkEnd,
                            'class' => self::photoClass(['type' => 'nophoto', 'thumbnail' => $options['thumbnail'], 'custom' => $custom, 'customClass' => $customClass])
                        ]);
                    }

                    return self::getPhoto([
                        'type' => 'photo',
                        'url' => $photoUrl,
                        'linkBegin' => $linkBegin,
                        'linkEnd' => $linkEnd,
                        'class' => self::photoClass(['type' => 'photo', 'thumbnail' => $options['thumbnail'], 'custom' => $custom, 'customClass' => $customClass])
                    ]);
                    break;

                case 'gallery':
                    $gallery = self::cache()->getData('avatar_gallery');
                    $found = false;

                    foreach ($gallery as $obj) {
                        if ($obj->id == $user->photoGalleryId) {
                            $found = true;
                            $photo = $obj->file_name;
                            break;
                        }
                    }

                    $photoUrl = self::vars()->baseUrl . '/public/gallery/' . $photo;

                    if (!$found || !\file_exists($photoUrl)) {
                        return self::getPhoto([
                            'type' => 'nophotomember',
                            'letter' => $firstChar,
                            'linkBegin' => $linkBegin,
                            'linkEnd' => $linkEnd,
                            'class' => self::photoClass(['type' => 'nophoto', 'thumbnail' => $options['thumbnail'], 'custom' => $custom, 'customClass' => $customClass])
                        ]);
                    }

                    return self::getPhoto([
                        'type' => 'photo',
                        'url' => $photoUrl,
                        'linkBegin' => $linkBegin,
                        'linkEnd' => $linkEnd,
                        'class' => self::photoClass(['type' => 'photo', 'thumbnail' => $options['thumbnail'], 'custom' => $custom, 'customClass' => $customClass])
                    ]);
                    break;
            }
        }
    }

    /**
     * Returns the given member's username.
     * 
     * @param int $id - The member identifier (default: signed in member).
     * @return string - The username for the given user.
     */
    public static function username($id = null) {
        if ($id == null) return self::$user->username;

        $data = self::getMemberData('username', $id);

        if ($data === null || \strlen($data) < 1) return self::localization()->getWords('global', 'unknown');

        return $data;
    }

    /**
     * Returns the member's sort by settings.
     * 
     * @return string - Sort by string.
     */
    public static function sortBy() {
        return self::$user->topicsSortBy;
    }

    /**
     * Returns the given user's time zone string.
     * 
     * @param int $id - The member identifier (default: signed in member).
     * @return string - Time zone string.
     */
    public static function getTimeZone($id = null) {
        if ($id === null) return self::$user->timeZone;

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
     * Returns the entry limit for the specified limit.
     * 
     * @param string $limit - The limit to return.
     * @return int - The entry limit.
     */
    public static function entryLimit($limit) {
        switch ($limit) {
            case 'topics':
                return self::$user->limits->topics;
                break;

            case 'posts':
                return self::$user->limits->posts;
                break;
        }
    }

    /**
     * Returns the characters limit for the given limit.
     * 
     * @param string $limit - The limit type.
     * @return int - The character limit.
     */
    public static function charLimit($limit) {
        switch ($limit) {
            case 'topicsList':
                return self::$user->charLimits->topicsList;
                break;
        }
    }

    /**
     * Returns the flag whether to censor depending on settings.
     * 
     * @param string $within - The source within such as forums or comments.
     * @return bool/string - Either a boolean result or string dependent upon the parameter.
     */
    public static function censoring($within) {
        switch ($within) {
            case 'forums':
                return self::$user->censoring->forums;
                break;

            case 'comments':
                return self::$user->censoring->comments;
                break;

            case 'replacementChar':
                return self::$user->censoring->replacementChar;
                break;
        }
    }

    /**
     * Sets an user field in the database.
     * 
     * @param string $name - The name of the field to set.
     * @param mixed $value - The value to set.
     * @param int $id - Optional custom user identifier.
     */
    public static function setField($name, $value, $id = null) {
        if ($id == null) $id = self::$user->id;

        self::db()->query(self::queries()->updateUserField(), [
            'name' => $name,
            'value' => $value,
            'id' => $id
        ]);

        self::cache()->update('users');
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

    /**
     * Returns whether the user is signed into their account.
     * 
     * @return bool - True if signed in, false otherwise.
     */
    public static function signedIn() {
        return self::$user->signedIn;
    }

    /**
     * Returns the specified class name source.
     * 
     * @param string $className - Name of the CSS class to return.
     * @return string - CSS class.
     */
    public static function getClass($className) {
        return self::$user->classes->$className;
    }
 }