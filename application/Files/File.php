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

namespace Discussify\Files;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that manages file creation, reading, deleting, etc.
 * 
 * @package Discussify\Files
 */
class File extends \Discussify\Application {
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
     * Creates a new file with the given filename.
     * 
     * @param string $filename - Name for the new file.
     */
    public static function createFile($filename) {
        if (!\touch($filename)) self::fatalError(\sprintf('Could not create file %s', $filename));
    }

    /**
     * Applies the given permissions to the given file.
     * 
     * @param string $filename - File to set permissions for.
     * @param int $permissions - UNIX-style permissions, such as 0655, etc.
     */
    public static function applyPermissions($filename, $permissions) {
        if (!\chmod($filename, $permissions)) self::fatalError(\sprintf('Could not set permisisons [%s] for file %s', $permissions, $filename));
    }

    /**
     * Deletes the given file from disk.
     * 
     * @param string $filename - File to delete.
     */
    public static function deleteFile($filename) {
        if (!\unlink($filename)) self::fatalError(\sprintf('Could not delete file %s', $filename));
    }

    /**
     * Reads data from the given file and returns the data.
     * Utilizes file locking to avoid collisions.
     * 
     * @param string $filename - File to read data from.
     * @return mixed - File data.
     */
    public static function readFile($filename) {
        $retVal = null;

        if (\file_exists($filename)) {
            if (\filesize($filename) > 0) {
                $handle = @fopen($filename, 'r');

                if (\flock($handle, LOCK_SH)) {
                    $retVal = @fread($handle, \filesize($filename));
                    \flock($handle, LOCK_UN);
                } else {
                    self::fatalError(\sprintf('Could not read file [%s] as a lock could not be obtained on the file', $filename));
                }
            }
        } else {
            self::fatalError(\sprintf('The file [%s] does not exist.', $filename));
        }

        return $retVal;
    }

    /**
     * Writes the given data to the given file.
     * 
     * @param string $filename - File to write data to.
     * @param mixed $data - Data to write to the file.
     */
    public static function writeFile($filename, $data) {
        $handle = @fopen($filename, 'w');

        if (\flock($handle, LOCK_EX)) {
            @ftruncate($handle, 0);
            @fwrite($handle, $data);
            @fflush($handle);
            \flock($handle, LOCK_UN);
        } else {
            self::fatalError(\sprintf('Could not write to the file [%s] as a lock could not be obtained on the file', $filename));
        }
    }

    /**
     * Returns a human-readable file size for the given bytes and decimal points.
     * For example, 1024 bytes = 1kB.
     * 
     * @param int $bytes - Total bytes of the file.
     * @param int $decimals - Total decimal points (default: 2).
     * @return string - Human-readable file size representation of file size.
     */
    public static function getReadableFileSize($bytes, $decimals = 2) {
        $size = ['B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $factor = \floor(\log($bytes, 1024));

        return \sprintf("%.{$decimals}f", $bytes / \power(1024, $factor)) . @$size[$factor];
    }

    /**
     * Handles all errors that occur in this class.
     * 
     * @param string $error - Error message.
     */
    private function fatalError($error) {
        throw new \Discussify\Exceptions\FileException($error);
    }
}