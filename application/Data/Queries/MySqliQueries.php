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

namespace Discussify\Data\Queries;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that manages all the various SQL queries for MySQLi servers.
 * 
 * @package Discussify\Data\Queries
 */
class MySqliQueries implements \Discussify\Data\QueriesStructure {
    /**
     * Singleton instance of this class.
     * @var object
     */
    protected static $instance;

    /**
     * Database connection information.
     * @var array
     */
    protected static $connInfo;

    /**
     * Database prefix.
     * @var string
     */
    protected $prefix = '';

    /**
     * Constructor that initializes class properties.
     */
    public function __construct() {
        require (APP_PATH . 'Config.inc.php');
        self::$connInfo = isset($cfg) ? $cfg : [];
        $this->prefix = self::$connInfo['db_prefix'];
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

// Make sure that the functions below do NOT have any white space before each line.

/**
 * SQL query for selecting data for caching purposes.
 * 
 * @param array $data - Key value pairs array collection.
 * @return string - SQL query statement.
 */
public function selectForCache($data = []) {
return <<<QUERY
SELECT * FROM `{$this->prefix}{$data['table']}`{$data['sorting']}
QUERY;
}

/**
 * SQL query for selecting data for caching puproses, but using
 * MySQL cache.
 * 
 * @param array $data - Key value pairs array collection.
 * @return string - SQL query statement.
 */
public function selectForCacheCached($data = []) {
return <<<QUERY
/*qc=on*/SELECT * FROM `{$this->prefix}{$data['table']}`{$data['sorting']}
QUERY;
}

/**
 * SQL query that sets the stores session data into the database.
 * 
 * @return string - SQL query statement.
 */
public function updateCacheData() {
return <<<QUERY
UPDATE {$this->prefix}stored_cache SET data = '{{toCache}}' WHERE title = '{{table}}'
QUERY;
}

/**
 * SQL query that inserts a brand new user session.
 * 
 * @return string - SQL query statement.
 */
public function insertUserSession() {
return <<<QUERY
INSERT INTO {$this->prefix}sessions
VALUES (
'{{id}}',
'{{userId}}',
'{{expires}}',
'{{lastClick}}',
'{{location}}',
'{{forumId}}',
'{{topicId}}',
'{{ipAddress}}',
'{{userAgent}}',
'{{hostname}}',
'{{display}}',
'{{searchBot}}',
'{{searchBotName}}',
'{{admin}}'
)
QUERY;
}

/**
 * SQL query that updates a given user session.
 * 
 * @return string - SQL query statement.
 */
public function updateUserSession() {
return <<<QUERY
UPDATE {$this->prefix}sessions
SET
expires = '{{expires}}',
last_click = '{{lastClick}}',
location = '{{location}}',
display = '{{display}}',
forum_id = '{{forumId}}',
topic_id = '{{topicId}}'
WHERE id = '{{id}}'
QUERY;
}

/**
 * SQL uery that delete the given user session.
 * 
 * @return string - SQL query statement.
 */
public function deleteUserSession() {
return <<<QUERY
DELETE FROM {$this->prefix}sessions WHERE id = '{{id}}'
QUERY;
}

/**
 * SQL query that deletes expired sessions from the database.
 * 
 * @return string - SQL query statement.
 */
public function deleteUserSessionGc() {
return <<<QUERY
DELETE FROM {$this->prefix}sessions WHERE expires < UNIX_TIMESTAMP();
QUERY;
}

/**
 * SQL query that grabs the data from the session store table
 * where the given parameters match.
 * 
 * @return string - SQL query statement.
 */
public function selectSessionDataFromStore() {
return <<<QUERY
SELECT * FROM {$this->prefix}session_store WHERE id = '{{id}}' AND expires > '{{time}}'
QUERY;
}

/**
 * SQL query that grabs data from the session store by a given
 * identifier.
 * 
 * @return string - SQL query statement.
 */
public function selectSessionFromStore() {
return <<<QUERY
SELECT * id FROM {$this->prefix}session_store WHERE id = '{{id}}'
QUERY;
}

/**
 * SQL query that inserts a new record in the session store table.
 * 
 * @return string - SQL query statement.
 */
public function insertSessionStoreNew() {
return <<<QUERY
INSERT INTO {$this->prefix}session_store
VALUES (
'{{id}}',
'{{data}}',
'{{lifetime}}'
)
QUERY;
}

/**
 * SQL query that updates the data in the given session store.
 * 
 * @return string - SQL query statement.
 */
public function updateSessionStoreData() {
return <<<QUERY
UPDATE {$this->prefix}session_store
SET
id = '{{id}}',
data = '{{data}}',
lifetime = '{{lifetime}}'
WHERE id = '{{id}}'
QUERY;
}

/**
 * SQL query that deletes a given session from the session store.
 * 
 * @return string - SQL query statement.
 */
public function deleteFromSessionStore() {
return <<<QUERY
DELETE FROM {$this->prefix}session_store WHERE id = '{{id}}'
QUERY;
}

/**
 * SQL query that deletes expires session stores.
 * 
 * @return string - SQL query statement.
 */
public function deleteFromSessionStoreGc() {
return <<<QUERY
DELETE FROM {$this->prefix}session_store WHERE lifetime < UNIX_TIMESTAMP();
QUERY;
}

/**
 * SQL query that inserts a new record into the application
 * registry.
 * 
 * @return string - SQL query statement.
 */
public function insertIntoRegistry() {
return <<<QUERY
INSERT INTO {$this->prefix}registry
VALUES (
null,
'{{name}}',
'{{value}}',
'{{type}}'
)
QUERY;
}

/**
 * SQL query that updates a given application registry record.
 * 
 * @return string - SQL query statement.
 */
public function updateRegistry() {
return <<<QUERY
UPDATE {$this->prefix}registry
SET
value = '{{value}}',
type = '{{type}}'
WHERE id = '{{id}}'
QUERY;
}

/**
 * SQL query that updates a given member field in the database.
 */
public function updateUserField() {
return <<<QUERY
UPDATE {$this->prefix}users
SET
{{field}} = '{{value}}'
WHERE id = '{{id}}'
QUERY;
}

}