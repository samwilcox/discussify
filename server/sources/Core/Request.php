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
 * Class that handles all incoming HTTP/HTTPS request data.
 * 
 * @package Discussify\Core
 */
class Request extends \Discussify\Application {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Holds incoming GET and POST data.
     * @var array
     */
    protected static $incoming = [];

    /**
     * Bot information object.
     * @var object
     */
    protected static $bot;

    /**
     * Secret key for encoding using JWT.
     * @var string
     */
    protected static $secretKey;

    /**
     * Constructor that sets up the Request class.
     */
    public function __construct() {
        self::$secretKey = self::settings()->api_secret_key;
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
     * Handles the incoming API request.
     */
    public static function handleRequest() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::handlePostRequest();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            self::handleGetRequest();
        } else {
            // Invalid request method.
            \http_response_code(405);
            echo \json_encode(['error' => 'Invalid request method']);
            exit();
        }
    }

    /**
     * Handles all incoming HTTP post requests.
     */
    private function handlePostRequest() {
        // Verify JWT token!
        $token = self::getTokenFromHeaders();

        if (!$token || !self::verifyToken($token)) {
            \http_response_code(401);
            echo \json_encode(['error' => 'Invalid or missing token']);
            exit();
        }

        // Handle the request now.
        $requestData = \json_decode(file_get_contents('php://input'), true);

        foreach ($_POST as $k => $v) self::$incoming[$k] = \filter_var($v, FILTER_UNSAFE_RAW);

        self::utils()->determineApiRoute($requestData);

        $responseData = [
            'message' => 'Received POST request from React',
            'data' => $requestData
        ];

        // Send to React front-end.
        self::sendResponse($responseData);
    }

    /**
     * Handles all incoming HTTP get requests.
     */
    private function handleGetRequest() {
        if (!$token || !self::verifyToken($token)) {
            \http_response_code(401);
            echo \json_encode(['error' => 'Invalid or missing token']);
            exit();
        }

        foreach ($_GET as $k => $v) self::$incoming[$k] = \filter_var($v, FILTER_UNSAFE_RAW);

        $returnData = self::utils()->determineApiRoute($requestData);

        $responseData = [
            'message' => 'Received GET request from React',
            'data' => $returnData
        ];

        self::sendResponse($responseData);
    }

    /**
     * Sends out a response to React.
     * 
     * @param mixed $data - Data to send.
     */
    private function sendResponse($data) {
        \header('Content-Type: application/json');

        echo \json_encode($data);
        exit();
    }

    /**
     * Extracts the token from the HTTP headers.
     * 
     * @return string - JWT token. Null if not found.
     */
    private function getTokenFromHeaders() {
        $headers = getallheaders();

        if (isset($headers['Authorization']) && \preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Generates a new token with the given data.
     */
    private function generateToken($data) {
        $issuedAt = \time();
        $expiration = $issuedAt + self::settings()->jwt_token_expiration_seconds;

        $payload = [
            'iat' => $issuedAt,
            'exp' => $expiration,
            'data' => $data
        ];

        return JWT::encode($payload, self::$secretKey);
    }

    /**
     * Verifies the given token.
     * 
     * @return bool - True if valid, false otheriwse.
     */
    private function verifyToken($token) {
        try {
            $decoded = JWT::decode($token, self::$secretKey, ['HS256']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Checks whether the current request is a search bot or not.
     */
    private function detectBots() {
        self::$bot->name;
        self::$bot->present = false;
        $bots = \unserialize(self::settings()->searchBotList);

        for ($i = 0; $i < \count($bots); $i++) {
            if (\strpos(' ' . \strtolower(self::agent()->get('agent')), \strtolower($bots[$i])) != false) self::$bot->name = $bots[$i];
        }

        self::$bot->present = \strlen(self::$bot->name) > 0 ? true : false;
    }

    /**
     * Returns the search bot data object.
     * 
     * @return object - Bot information object.
     */
    public static function botData() {
        return self::$bot;
    }

    /**
     * Magic function that sets a new key/value.
     * 
     * @param string $key - Key to set.
     * @param mixed $value - Value to set.
     */
    public function __set($key, $value) {
        self::$incoming[$key] = $value;
    }

    /**
     * Magic function that returns the given kay value.
     * 
     * @param string $key - Key to get. Null if key does not exist.
     */
    public function __get($key) {
        if (\array_key_exists($key, self::$incoming)) return self::$incoming[$key];
        return null;
    }

    /**
     * Magic function that returns whether the given key exists.
     * 
     * @param string $key - Key to check for existance.
     */
    public function __isset($key) {
        if (\array_key_exists($key, self::$incoming)) {
            return true;
        }

        return false;
    }
}