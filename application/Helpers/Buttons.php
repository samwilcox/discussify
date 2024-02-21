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
 * This class handles the building and retrieval of various button elements.
 * 
 * @package \Discussify\Helpers
 */
class Buttons extends \Discussify\Application {
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
     * Builds and then returns the specified extended button.
     * 
     * @param int $type - Type of button (1=Primary, 2=Secondary, 3=Tertiary).
     * @param string $button - The name of the button.
     * @param int $forumId - The forum identifier.
     * @param int $topicId - Optional topic identifier.
     * @return mixed - The button source.
     */
    public static function getExtended($type, $button, $forumId, $topicId = 0) {
        $cssClassName = "";

        switch ($type) {
            case 1:
                $cssClassName = 'primaryButton';
                break;

            case 2:
                $cssClassName = 'secondaryButton';
                break;

            case 3:
                $cssClassName = 'tertiaryButton';
                break;
        }

        switch ($button) {
            case \Discussify\Type\Button::NEW_TOPIC:
                return self::user()->getForumPermission($forumId, 'postTopics') ? self::output()->getPartial('ButtonsHelper', 'Button-' . $buttonName . '-NewTopic', ['class' => self::user()->getClass($cssClassName), 'url' => self::seo()->url('post', 'newtopic', ['id' => $forumId])]) : ''; 
                break;
        }
    }

    /**
     * Builds the button according to the parameters and then returns it.
     * 
     * @param string $type - The type of button (1=Primary, 2=Secondary, 3=Tertiary, 4=Lite).
     * @param string $name - The button text.
     * @param string $url - The URL for the button (leave null if using javascript).
     * @param mixed $icon - Optional icon source for an icon.
     * @return mixed - Button source.
     */
    public static function get($type, $name, $url = null, $icon = '') {
        $cssClassName = "";

        switch ($type) {
            case 1:
                $cssClassName = 'primaryButton';
                break;

            case 2:
                $cssClassName = 'secondaryButton';
                break;

            case 3:
                $cssClassName = 'tertiaryButton';
                break;

            case 4:
                $cssClassName = 'liteButton';
                break;
        }

        return self::output()->getPartial(
            'ButtonsHelper',
            'Button',
            'Base', [
                'url' => $url == null ? 'javascript:void(0);' : $url,
                'name' => $name,
                'icon' => $icon,
                'class' => self::user()->getClass($cssClassName)
            ]
        );
    }
}