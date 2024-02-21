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
 * Helper class that assists with various utility type routines.
 * 
 * @package Discussify\Helpers
 */
class Utils extends \Discussify\Application {
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
     * Returns a drop down listing of the specified type of resource.
     * 
     * @param string $type - The resource type to get listing for.
     * @return mixed - Select listing.
     */
    public static function getSelectionList($type) {
        $listing = '';

        switch ($type) {
            case 'language':
                $data = self::cache()->getData('installed_languagepacks');

                foreach ($data as $lang) {
                    if ($lang->id === self::user()->languagepackId()) {
                        $listing .= self::output()->getPartial(
                            'UtilsHelper',
                            'List',
                            'OptionSelected', [
                                'url' => self::seo()->url('index', 'language', ['id' => $lang->id]),
                                'name' => $lang->name
                            ]
                        );
                    } else {
                        $listing .= self::output()->getPartial(
                            'UtilsHelper',
                            'List',
                            'Option', [
                                'url' => self::seo()->url('index', 'language', ['id' => $lang->id]),
                                'name' => $lang->name
                            ]
                        );
                    }
                }
                break;

            case 'theme':
                $data = self::cache()->getData('installed_themes');

                foreach ($data as $theme) {
                    if ($theme->id === self::user()->themeId()) {
                        $listing .= self::output()->getPartial(
                            'UtilsHelper',
                            'List',
                            'OptionSelected', [
                                'url' => self::seo()->url('index', 'theme', ['id' => $theme->id]),
                                'name' => $theme->name
                            ]
                        );
                    } else {
                        $listing .= self::output()->getPartial(
                            'UtilsHelper',
                            'List',
                            'Option', [
                                'url' => self::seo()->url('index', 'theme', ['id' => $theme->id]),
                                'name' => $theme->name
                            ]
                        );
                    }
                }
                break;
        }

        return $listing;
    }

    /**
     * Creates a brand new drop down menu for the given list.
     * 
     * @param string $id - The ID for the HTML id tag (dropdown-{id}).
     * @param mixed $list - Listing source.
     * @return mixed - Drop down source.
     */
    public static function createDropDown($id, $list) {
        return self::output()->getPartial(
            'UtilsHelper',
            'DropDown',
            'Generic', [
                'id' => $id,
                'items' => $list
            ]
        );
    }

    /**
     * Initializes the initial breadcrumbs so it's ready for added
     * links at a later point.
     */
    public static function initializeBreadcrumbs() {
        if (self::settings()->home_breadcrumb_enabled && \strlen(self::settings()->home_breadcrumb_title) > 0 && \strlen(self::settings()->home_breadcrumb_url) > 0) {
            self::newBreadcrumb(self::settings()->home_breadcrumb_title, self::settings()->home_breadcrumb_url, false, true);
        }
    }

    /**
     * Adds a new breadcrumb to the breadcrumbs array.
     * 
     * @param string $title - Title of the breadcrumb.
     * @param string $url - URL address of the new breadcrumb.
     * @param bool $selected - Whether the breadcrumb is the current one.
     * @param bool $first - Whether this is the first breadcrumb.
     */
    public static function newBreadcrumb($title, $url, $selected = false, $first = false) {
        $newCrumb = self::vars()->breadcrumbs;
        $newCrumb[] = ['title' => $title, 'url' => $url, 'selected' => $selected, 'first' => $first];
        self::vars()->breadcrumbs = $newCrumb;
    }

    /**
     * Returns the built breadcrumbs.
     * 
     * @return mixed - Breadcrumbs.
     */
    public static function getBreadcrumbs() {
        if (!isset(self::vars()->breadcrumbs)) return;

        $breadcrumbs = '';

        foreach (self::vars()->breadcrumbs as $crumb) {
            if ($crumb['selected']) {
                $breadcrumbs .= self::output()->getPartial(
                    'UtilsHelper',
                    'Breadcrumb',
                    'Selected', [
                        'title' => $crumb['title'],
                        'seperator' => $crumb['selected'] ? self::output()->getPartial('UtilsHelper', 'Breadcrumb', 'Seperator') : ''
                    ]
                );
            } else {
                $breadcrumbs .= self::output()->getPartial(
                    'UtilsHelper',
                    'Breadcrumb',
                    'Normal', [
                        'title' => $crumb['title'],
                        'url' => $crumb['url'],
                        'seperator' => $crumb['selected'] ? self::output()->getPartial('UtilsHelper', 'Breadcrumb', 'Seperator') : ''
                    ]
                );
            }
        }

        return $breadcrumbs;
    }

    /**
     * This method compares the sort_order for sorting.
     * 
     * @param array $a - First array.
     * @param array $b - Second array.
     */
    public static function sortBySortOrder($a, $b) {
        return $a->sort_order - $b->sort_order;
    }

    /**
     * Sets the page title.
     * 
     * @param string $title - The title to set.
     */
    public static function setPageTitle($title) {
        self::vars()->pageTitle = $title;
    }

    /**
     * Limits the given string by the given limit.
     * 
     * @param string $string - The string to limit.
     * @param int $limit - The max characters to return.
     * @return string - Resulting string value.
     */
    public static function limitString($string, $limit) {
        if (\mb_strlen($string) > $limit) {
            $limitedString = \mb_substr($string, 0, $limit);
            $limitedString .= '...';

            return $limitedString;
        } else {
            return $string;
        }
    }

    /**
     * Returns the sort by string that is currently set.
     * 
     * @return string - Sort by string.
     */
    public static function getSortBy() {
        if (isset($_SESSION['discussify_forum_filter'])) {
            return $_SESSION['discussify_forum_filter'];
        } else {
            return self::user()->signedIn() ? self::user()->sortBy : self::settings()->topics_sort_by;
        }
    }
}