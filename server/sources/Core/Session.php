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
 * Class that is responsible for management of all the user sessions.
 * This is to fight against any session hijacking.
 * 
 * @package Discussify\Core
 */
class Session extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Class paramaters object.
     * @var object
     */
    protected static $params;

    /**
     * Class constructor that constructs the class parameters object and
     * handles garbage collection.
     */
    public function __construct() {
        self::$params = (object) [
            'duration' => 15,
            'ipMatch' => false,
            'lifetime' => 0,
            'session' => null
        ];

        self::initializeSessionData();
        self::sessionGc();
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
     * Initializes the data for the session object.
     */
    private function initializeSessionData() {
        self::$params->session = (object) [
            'id' => 0,
            'expires' => 0,
            'lastClick' => 0,
            'location' => '',
            'forumId' => 0,
            'topicId' => 0,
            'memberId' => 0,
            'display' => 0,
            'admin' => false
        ];
    }

    /**
     * Manages the user session data.
     */
    public static function management() {
        self::$params->duration = self::settings()->session_timeout * 60;
        self::$params->ipMatch = self::settings()->session_ip_match;

        // Are we storing session data in database?
        if (self::settings()->sessionStoreMethod === 'dbstore') {
            self::$params->lifetime = \get_cfg_var('session.gc_maxlifetime');

            \session_set_save_handler(
                [&$this, 'session_open'],
                [&$this, 'session_close'],
                [&$this, 'session_read'],
                [&$this, 'session_write'],
                [&$this, 'session_delete'],
                [&$this, 'session_garbage_collection']
            );
        }

        \session_start();
        self::$params->session->id = \session_id();

        // If an user token is set, then we will sign the member in, depending on factors of course.
        if (isset($_COOKIE['discussify_user_token'])) {
            $token = $_COOKIE['discussify_user_token'];
            $found = false;
            $data = self::cache()->getData('members_devices');

            foreach ($data as $device) {
                if ($device->user_key === $token) {
                    $found = true;
                    $memberId = $device->member_id;
                    break;
                }
            }

            switch ($found) {
                case true:
                    $data = self::cache()->massGetData(['members' => 'members', 'sessions' => 'sessions']);

                    foreach ($data->members as $member) {
                        if ( $member->id === $memberId) {
                            $username = $member->username;
                            $displayOnList = $member->display_online_list;
                            break;
                        }
                    }

                    $found = false;

                    foreach ($data->sessions as $session) {
                        if ($session->member_id === $memberId) {
                            $found = true;
                            $ipAddress = $session->ip_address;
                            $userAgent = $session->agent;
                            $adminSess = $session->admin;
                        }
                    }

                    switch ($found) {
                        case true:
                            switch ($params->ipMatch) {
                                case true:
                                    if ($ipAddress !== self::agent()->get('ip') || $userAgent !== self::agent()->get('agent')) {
                                        self::destroySession();
                                    } else {
                                        self::updateSession(['id' => $memberId, 'username' => $username, 'display' => $displayOnList, 'admin' => $adminSess]);
                                    }
                                    break;

                                case false:
                                    self::updateSession(['id' => $memberId, 'username' => $username, 'display' => $displayOnList, 'admin' => $adminSess]);
                                    break;
                            }
                            break;

                        case false:
                            self::createSession(['id' => $memberId, 'username' => $username, 'display' => $displayOnList, 'admin' => 0]);
                            break;
                    }
                    break;

                case false:
                    self::destroySession();
                    break;
            }
        } else {
            $data = self::cache()->getData('sessions');
            $found = false;

            foreach ($data as $session) {
                if ($session->id === self::$params->session->id) {
                    $found = true;
                    $ipAddress = $session->ip_address;
                    $userAgent = $session->agent;
                }
            }

            switch ($found) {
                case true:
                    switch (self::$params->ipMatch) {
                        case true:
                            if ($ipAddress !== self::agent()->get('ip') || $userAgent !== self::agent()->get('agent')) {
                                self::destroySession();
                            } else {
                                self::updateSession();
                            }
                            break;

                        case false:
                            self::updateSession();
                            break;
                    }
                    break;

                case false:
                    self::createSession();
                    break;
            }
        }
    }
}