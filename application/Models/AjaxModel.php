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

namespace Discussify\Models;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * AJAX model class for handling all AJAX requests.
 * 
 * @package \Discussify\Models
 */
class AjaxModel extends \Discussify\Models\BaseModel {
    /**
     * Key value pair collection for tag replacements.
     * @var array
     */
    private static $vars = [];

    /**
     * Sets the forum filter to the specified filter.
     */
    public function setForumFilter() {
        $data = self::ajaxHelper()->getInput();
        $_SESSION['discussify_forum_filter'] = $data->filter;
        $options = [];

        if ($data->forum != 0) {
            $options['forumId'] = $data->forum;
        }
        
        self::ajaxHelper()->send(['result' => true, 'topics' => self::forumsHelper()->getTopicsList($options)]);
    }

    /**
     * Loads more topics.
     */
    public function loadMoreTopics() {
        $data = self::cache()->getData('topics');
        $index = self::request()->index;
        $forumId = self::request()->forumid;
        $total = 0;
        $ajax = [];

        if ($forumId != 'null') {
            foreach ($data as $topic) {
                if ($topic->forum_id == $forumId) $total++;
            }
        } else {
            foreach ($data as $topic) $total++;
        }

        $limit = self::user()->entryLimit('topics');
        $endIndex = \min($index + $limit - 1, $total - 1);
        $moreAvailable = $endIndex < $total - 1;

        if (!$moreAvailable) {
            $ajax['hideButton'] = true;
        } else {
            $ajax['hideButton'] = false;
        }

        $ajax['index'] = $endIndex + 1;
        $ajax['topics'] = self::forumsHelper()->getTopicsList(['forumId' => $forumId, 'index' => $index]);

        self::ajaxHelper()->send($ajax);
    }

    /**
     * Loads the specified forum.
     */
    public function forumItemSelect() {
        $forumId = self::request()->forumid;
        $landingForum = self::request()->landingforum;
        $jsonArr['index'] = 0;

        if ($landingForum != 'allforums') {
            $jsonArr['forumId'] = $forumId;
        }

        self::ajaxHelper()->send(['status' => true, 'index' => 0, 'menu' => self::blocksHelper()->forumsList($landingForum), 'topics' => self::forumsHelper()->getTopicsList($jsonArr)]);
    }
}