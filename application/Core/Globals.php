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
 * Class that handles all global variables for the application.
 * 
 * @package Discussify\Core
 */
class Globals extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;
    
    /**
     * Global variables collection.
     * @var array
     */
    protected static $vars = [];

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
     * Builds al the various global variables for the application and then
     * returns them all.
     * 
     * @return array - Collection of global variables.
     */
    public static function get() {
        self::$vars['communityTitle'] = self::settings()->community_title;
        self::$vars['imagesetUrl'] = self::user()->imagesetUrl();
        self::$vars['themeUrl'] = self::user()->themeUrl();
        self::$vars['timestamp'] = \time();
        self::$vars['baseUrl'] = self::vars()->baseUrl;
        self::$vars['wrapper'] = self::vars()->wrapper;
        self::$vars['breadcrumbs'] = self::utils()->getBreadcrumbs();
        self::$vars['pageTitle'] = self::vars()->pageTitle;

        if (self::settings()->community_logo_type === 'image') {
            self::$vars['communityLogo'] = self::output()->getPartial(
                'Global',
                'Globals',
                'LogoImage', [
                    'communityLogo' => self::settings()->community_logo,
                    'imagesetUrl' => self::user()->imagesetUrl(),
                    'communityTitle' => self::settings()->community_title,
                    'url' => self::seo()->url('index')
                ]);
        } else {
            self::$vars['communityLogo'] = self::output()->getPartial(
                'Global',
                'Globals',
                'LogoText',
                [
                    'url' => self::seo()->url('index'),
                    'communityTitle' => self::settings()->community_title
                ]
            );
        }

        $extendedMenuEnabled = false;

        if (self::user()->signedIn()) {

        } else {
            $registerLink = self::output()->getPartial(
                'Global',
                'Link',
                'Register', [
                    'url' => self::seo()->url('register')
                ]
            );

            self::$vars['userBar'] = self::output()->getPartial(
                'Global',
                'UserBar',
                'Guest', [
                    'url' => self::seo()->url('authentication'),
                    'registerLink' => $registerLink
                ]
            );
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::FORUMS)) {
            self::$vars['forumsLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Forums', [
                    'url' => self::seo()->url('index')
                ]
            );
        } else {
            self::$vars['forumsLink'] = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::MEMBERS_LIST)) {
            self::$vars['membersLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Members', [
                    'url' => self::seo()->url('members', 'list')
                ]
            );
        } else {
            self::$vars['membersLink'] = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::ACTIVE_USERS)) {
            self::$vars['activeUsersLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'ActiveUsers', [
                    'url' => self::seo()->url('activeusers')
                ]
            );
        } else {
            self::$vars['activeUsersLink'] = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::SEARCH)) {
            self::$vars['searchLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Search', [
                    'url' => self::seo()->url('search')
                ]
            );
        } else {
            self::$vars['searchLink'] = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::HELP)) {
            self::$vars['helpLink'] = self::output()->getPartial(
                'Global',
                'Link',
                'Help', [
                    'url' => self::seo()->url('help')
                ]
            );
        } else {
            self::$vars['helpLink'] = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::GROUPS_LIST)) {
            $groupsLink = self::output()->getPartial(
                'Global',
                'Link',
                'Groups', [
                    'url' => self::seo()->url('groups', 'list')
                ]
            );

            $extendedMenuEnabled = true;
        } else {
            $groupsLink = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::COMMUNITY_LEADERS)) {
            $leadersLink = self::output()->getPartial(
                'Global',
                'Link',
                'CommunityLeaders', [
                    'url' => self::seo()->url('groups', 'leaders')
                ]
            );

            $extendedMenuEnabled = true;
        } else {
            $leadersLink = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\FEATURE::TAGS)) {
            $tagsLink = self::output()->getPartial(
                'Global',
                'Link',
                'Tags', [
                    'url' => self::seo()->url('tags')
                ]
            );

            $extendedMenuEnabled = true;
        } else {
            $tagsLink = '';
        }

        if (self::settings()->privacy_policy_link_enabled && \strlen(self::settings()->privacy_policy_link_url) > 0) {
            $privacyPolicyLink = self::output()->getPartial(
                'Global',
                'Link',
                'PrivacyPolicy', [
                    'url' => self::settings()->privacy_policy_link_url
                ]
            );

            $extendedMenuEnabled = true;
        } else {
            $privacyPolicyLink = '';
        }

        if (self::settings()->contact_us_link_enabled && \strlen(self::settings()->contact_us_link_url) > 0) {
            $contactUsLink = self::output()->getPartial(
                'Global',
                'Link',
                'ContactUs', [
                    'url' => self::settings()->contact_us_link_url
                ]
            );

            $extendedMenuEnabled = true;
        } else {
            $contactUsLink = '';
        }

        if ($extendedMenuEnabled) {
            self::$vars['extendedMenuLink'] = self::output()->getPartial('Global', 'Link', 'ExtendedMenu');
            self::$vars['extendedMenuDropDown'] = self::utils()->createDropDown('extended-menu', $leadersLink . $groupsLink . $tagsLink . $privacyPolicyLink . $contactUsLink);
        } else {
            self::$vars['extendedMenuLink'] = '';
            self::$vars['extendedMenuDropDown'] = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\Feature::LANGUAGE_SELECT)) {
            self::$vars['languageLink'] = self::output()->getPartial('Global', 'Link', 'Language');
            self::$vars['languageDropDown'] = self::utils()->createDropDown('language', self::utils()->getSelectionList('language'));
        } else {
            self::$vars['languageLink'] = '';
            self::$vars['languageDropDown'] = '';
        }

        if (self::user()->hasFeaturePermissions(\Discussify\Type\Feature::THEME_SELECT)) {
            self::$vars['themeLink'] = self::output()->getPartial('Global', 'Link', 'Theme');
            self::$vars['themeDropDown'] = self::utils()->createDropDown('theme', self::utils()->getSelectionList('theme'));
        } else {
            self::$vars['themeLink'] = '';
            self::$vars['themeDropDown'] = '';
        }

        self::$vars['appVersion'] = APP_VERSION;

        $timeZone = new \DateTimeZone(self::user()->getTimeZone());
        $gmt = new \DateTime('now', $timeZone);

        self::$vars['allTimes'] = self::localization()->quickMultiWordReplace('global', 'allTimes', [
            'timeZone' => self::user()->getTimeZone(),
            'gmt'      => \sprintf('GMT %s', $gmt->format('P'))
        ]);

        self::$vars['timeNow'] = self::localization()->quickMultiWordReplace('global', 'timeNow', [
            'icon' => self::output()->getPartial('Global', 'Icon', 'Clock'),
            'time' => self::dateTime()->parse(\time(), ['timeOnly' => true])
        ]);

        self::$vars['ajaxHtml'] = \base64_encode(self::output()->getPartial('Global', 'Ajax', 'ErrorModel'));
        self::$vars['checkmarkIcon'] = \base64_encode(self::output()->getPartial('Global', 'Icon', 'Checkmark'));

        if (isset(self::vars()->dropdowns)) {
            self::$vars['dropdowns'] = self::vars()->dropdowns;
        } else {
            self::$vars['dropdowns'] = '';
        }

        self::$vars['ajaxSessionId'] = \base64_encode(self::session()->id());
        self::$vars['topicsLoadLimit'] = self::user()->entryLimit('topics');

        return self::$vars;
    }
}