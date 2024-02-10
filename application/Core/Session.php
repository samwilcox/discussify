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
     * Object for handling the class properties.
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

        self::constructData();
        self::gc();
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
     * Contructs the session data parameters object.
     */
    private static function constructData() {
        self::$params->session = (object) [
            'id' => 0,
            'expires' => 0,
            'lastClick' => 0,
            'location' => '',
            'forumId' => 0,
            'topicId' => 0,
            'userId' => 0,
            'display' => 0,
            'admin' => false
        ];
    }

    /**
     * Loads up the session and verifies that no session-hijacking is happening
     * by storing all sessions into the database for better accounting abilities.
     */
    public static function load() {
        self::$params->duration = self::settings()->session_duration_seconds * 60;
        self::$params->ipMatch = self::settings()->session_ip_matching;

        if (self::settings()->session_store_method === 'dbstore') {
            self::$params->lifetime = \get_cfg_var('session.gc_maxlifetime');

            \session_set_save_handler(
                [&$this, 'sessionOpen'],
                [&$this, 'sessionClose'],
                [&$this, 'sessionRead'],
                [&$this, 'sessionWrite'],
                [&$this, 'sessionDelete'],
                [&$this, 'sessionGc']
            );
        }

        \session_start();

        // Do we need to get a session ID from query string (if its an AJAX request).
        if (isset(self::request()->controller) && self::request()->controller === 'ajax' && isset(self::request()->sid)) {
            self::$params->session->id = \session_id(self::request()->sid);
        } else {
            self::$params->session->id = \session_id();
        }

        // Check to see if a user token exists in a cookie or not.
        // If exists, verify user; sign them in if not signed in.
        if (self::cookies()->exists('discussify_user_token')) {
            $token = self::cookies()->discussify_user_token;
            $found = false;
            $data = self::cache()->getData('users_devices');

            foreach ($data as $device) {
                if ($device->sign_in_key === $token) {
                    $found = true;
                    $userId = $device->user_id;
                    break;
                }
            }

            switch ($found) {
                case true:
                    $data = self::cache()->massGetData(['users' => 'users', 'sessions' => 'sessions']);

                    foreach ($data->users as $user) {
                        if ($user->id === $userId) {
                            $display = $user->active_users_display;
                            break;
                        }
                    }

                    $found = false;

                    foreach ($data->sessions as $session) {
                        if ($session->user_id === $userId) {
                            $found = true;
                            $ipAddress = $session->ip_address;
                            $userAgent = $session->user_agent;
                            $admin = $session->admin;
                            break;
                        }
                    }

                    switch ($found) {
                        case true:
                            switch (self::$params->ipMatch) {
                                case true:
                                    if ($ipAddress !== self::agent()->get('ip') || $userAgent !== self::agent()->get('agent')) {
                                        self::destroySession();
                                    } else {
                                        self::updateSession(['id' => $userId, 'display' => $display, 'admin' => $admin]);
                                    }
                                    break;

                                case false:
                                    self::updateSession(['id' => $userId, 'display' => $display, 'admin' => $admin]);
                                    break;
                            }
                            break;

                        case false:
                            self::createSession(['id' => $userId, 'display' => $display, 'admin' => $admin]);
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
                    $userAgent = $session->user_agent;
                    break;
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

    /**
     * Destroys the current sessions and the generates a brand new
     * session identifier.
     */
    private static function destroySession() {
        self::cookies()->deleteCookie('discussify_user_token');

        \session_unset();
        \session_destroy();

        if (self::cookies()->exists(\session_name())) self::cookies()->deleteCookie(\session_name(), true);

        self::deleteUserSession();
        unset($_SESSION['discussify_user_id']);
        \session_regenerate_id(true);
        self::redirect()->go(self::seo()->url('index'));
    }

    /**
     * Updates the current user's session.
     * 
     * @param array $data - Optional user data collection.
     */
    private static function updateSession($data = null) {
        self::$params->session->expires = \time() + self::$params->duration;
        self::$params->session->lastClick = \time();
        self::$params->session->location = $_SERVER['REQUEST_URI'];
        self::$params->session->display = 0;

        if ($data !== null) {
            self::$params->session->userId = $data['id'];
            self::$params->session->display = $data['display'];
            self::$params->session->admin = $data['admin'] === 1 ? true : false;
            $_SESSION['discussify_user_id'] = $data['id'];
        } else {
            unset($_SESSION['discussify_user_id']);
        }

        self::updateUserSession();
    }

    /**
     * Creates a brand new session in the database for the current user.
     * 
     * @param array $data - Optional user data collection.
     */
    private static function createSession($data = null) {
        self::$params->session->expires = \time() + self::$params->duration;
        self::$params->session->lastClick = \time();
        self::$params->session->location = $_SERVER['REQUEST_URI'];

        if ($data !== null) {
            self::$params->session->userId = $data['id'];
            self::$params->session->display = $data['id'];
            self::$params->session->admin = $data['admin'] === 1 ? true : false;
        } else {
            self::$params->session->userId = 0;
            self::$params->session->display = 0;
            self::$params->session->admin = false;

            unset($_SESSION['discussify_user_id']);
        }

        self::createUserSession();
    }

    /**
     * Creates a brand new user session in the database using the class
     * properties.
     */
    private static function createUserSession() {
        if (self::request()->controller !== 'source') {
            self::db()->query(self::queries()->insertUserSession(),
            [
                'id' => self::$params->session->id,
                'userId' => self::$params->session->userId,
                'expires' => self::$params->session->expires,
                'lastClick' => self::$params->session->lastClick,
                'location' => self::$params->session->location,
                'forumId' => self::$params->session->forumId,
                'topicId' => self::$params->session->topicId,
                'ipAddress' => self::agent()->get('ip'),
                'userAgent' => self::agent()->get('agent'),
                'hostname' => self::agent()->get('hostname'),
                'display' => self::$params->session->display,
                'searchBot' => self::request()->botData()->present ? 1 : 0,
                'searchBotName' => self::request()->botData()->name,
                'admin' => self::$params->session->admin ? 1 : 0
            ]);

            self::cache()->update('sessions');
        }
    }

    /**
     * Updates the current user's session in the database.
     */
    private static function updateUserSession() {
        if (self::request()->controller !== 'source') {
            self::db()->query(self::queries()->updateUserSession(), [
                'expires' => self::$params->session->expires,
                'lastClick' => self::$params->session->lastClick,
                'location' => self::$params->session->location,
                'display' => self::$params->session->display,
                'forumId' => self::$params->session->forumId,
                'topicId' => self::$params->session->topicId,
                'id' => self::$params->session->id
            ]);

            self::cache()->update('sessions');
        }
    }

    /**
     * Deletes the current user's session and creates a brand new one
     * in the database.
     */
    private static function deleteUserSession() {
        if (self::request()->controller !== 'source') {
            self::db()->query(self::queries()->deleteUserSession(), ['id' => self::$params->session->id]);
            self::cache()->update('sessions');
        }
    }

    /**
     * Performs garbage collection on user sessions.
     * If an user's session expires, remove it.
     */
    private static function gc() {
        self::db()->query(self::queries()->deleteUserSessionGc());
        if (self::db()->affectedRows() > 0) self::cache()->update('sessions');
    }

    /**
     * Magic function for opening sessions in files.
     */
    public function sessionOpen() {
        // Left blank on purpose.
    }

    /**
     * Magic function for closing sessions in files.
     */
    public function sessionClose() {
        // Left blank on purpose.
    }

    /**
     * Magic function for reading data from sessions in files.
     * 
     * @param string $id - Session identifier.
     * @return mixed - Session data.
     */
    public function sessionRead($id) {
        $data = '';
        $time = \time();

        $sql = self::db()->query(self::queries()->selectSessionDataFromStore(), ['id' => $id, 'time' => $time]);

        if (self::db()->numRows($sql) > 0) {
            $row = self::db()->fetchArray($sql);
            $data = $row['data'];
        }

        self::db()->freeResult($sql);

        return $data;
    }

    /**
     * Magic function that writes session data to files.
     * 
     * @param string $id - Session identifier.
     * @param mixed $data - Data to store in the session.
     * @return bool - Just returns true (PHP your weird!).
     */
    public function sessionWrite($id, $data) {
        $time = \time();
        $sql = self::db()->query(self::queries()->selectSessinFromStore(), ['id' => $id]);
        $total = self::db()->numRows();
        self::db()->freeResult($sql);

        if ($total === 0) {
            self::db()->query(self::queries()->insertSessionStoreNew(), ['id' => $id, 'data' => $data, 'lifetime' => self::$params->lifetime]);
        } else {
            self::db()->query(self::queries()->updateSessionStoreData(), ['id' => $id, 'data' => $data, 'lifetime' => self::$params->lifetime]);
        }

        return true;
    }

    /**
     * Magic function that deletes the given session from the database.
     */
    public function sessionDelete($id) {
        self::db()->query(self::queries()->deleteSessionFromSessionStore(), ['id' => $id]);
    }

    /**
     * Magic function that performs garbage collection on session
     * data stores.
     */
    public function sessionGc() {
        self::db()->query(self::queries()->deleteFromSessionStoreGc());
    }

    /**
     * Returns the current session identifying string.
     * 
     * @return string - Current session identifier.
     */
    public static function id() {
        return self::$params->session->id;
    }

    /**
     * Sets the forum identifier for the session.
     * 
     * @param int $id - Forum identifier.
     */
    public static function setForumId($id) {
        self::$params->session->forumId = $id;
    }

    /**
     * Sets the topic identifier for the session.
     * 
     * @param int $id - Topic identifier.
     */
    public static function setTopicId($id) {
        self::$params->session->topicId = $id;
    }
}