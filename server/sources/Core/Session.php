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

// Need to include Firebase JSON Web Tokens 3rd party library.
require_once (APP_PATH . '3rdParty/firebase-php-jwt/src/BeforeValidException.php');
require_once (APP_PATH . '3rdParty/firebase-php-jwt/src/ExpiredException.php');
require_once (APP_PATH . '3rdParty/firebase-php-jwt/src/SignatureInvalidException.php');
require_once (APP_PATH . '3rdParty/firebase-php-jwt/src/JWT.php');
use \Firebase\JWT\JWT;

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
            'duration' => 3600,
            'ipMatch' => false,
            'lifetime' => 0,
            'session' => null,
            'secretKey' => self::settings()->apiSecretKey
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
     * Starts the user session.
     */
    public static function startSession() {
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
    }

    /**
     * Destroys the user session.
     */
    public static function destroySession() {
        \session_destroy();
    }

    /**
     * Generates a new JWT token.
     * 
     * @param string $userData - User data to send.
     */
    public static function generateToken($userData) {
        $issuedAt = \time();
        $expiration = $issuedAt + self::$params->duration;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiration,
            'data' => $userData
        ];

        return JWT::encode($payload, self::$params->secretKey);
    }

    /**
     * Verfies the given token.
     * 
     * @param string $token - Token to verify.
     * @return <array|bool> - Data collection if verified, false otherwise.
     */
    public static function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, self::$params->secretKey, ['HS256']);
            return (array) $decoded->data;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get's the JWT token from the headers.
     * 
     * @return string - Token from headers.
     */
    public static function getTokenFromHeaders() {
        $headers = \getallheaders();
        
        if (isset($headers['Authorization']) && \preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Stores the session data into the database.
     * 
     * @param int $userId - ID of the user.
     * @param string $sessionId - Session identifier string.
     */
    public static function storeSessionInDatabase($userId, $sessionId) {
        
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