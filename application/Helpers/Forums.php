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
 * Helper class for forums specified routines.
 * 
 * @package \Discussify\Helpers
 */
class Forums extends \Discussify\Application {
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
     * Returns whether the specified forum is "visible", meaning that its not hidden
     * and the user has view access or not.
     * 
     * @param int $id - The forum ID to check.
     * @return bool - True if visible, false otherwise.
     */
    public static function forumVisible($id) {
        $data = self::cache()->getData('forums');
        $visible = false;

        foreach ($data as $forum) {
            if ($forum->id == $id) {
                if ($forum->visible == 0) {
                    return false;
                    break;
                }
            }
        }

        if (self::user()->getForumPermissions($id)->viewForum) {
            $visible = true;
        }

        return $visible;
    }

    /**
     * Returns whether or not if the specified forum has child forums.
     * 
     * @param int $id - The forum identifier.
     * @return bool - True if it has children, false otherwise.
     */
    public static function haveChildForums($id) {
        $data = self::cache()->getData('forums');

        foreach ($data as $forum) {
            if ($forum->id == $id) {
                if ($forum->children == 1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Determines what the last activity was for the given topic.
     * 
     * @param int $id - The topic identifier.
     * @return int - UNIX timestamp.
     */
    public static function lastTopicActivity($id) {
        $data = self::cache()->getData('posts');
        $list = [];

        foreach ($data as $post) {
            if ($post->topic_id == $id) {
                $list[] = \strtotime($post->created_timestamp);
            }
        }

        \rsort($list);
        \reset($list);

        return \current($list);
    }

    /**
     * Compares the given values and sorts the object correctly.
     * 
     * @param int $a - The first ID.
     * @param int $b - the second ID.
     * @return object - Sorted object.
     */
    private static function compareById($a, $b) {
        return $a - $b;
    }

    /**
     * Compares the given values and sorts the objects in reverse order.
     * 
     * @param int $a - The first ID.
     * @param int $b - The second ID.
     * @return array - Sorted collection.
     */
    private static function compareByIdReversed($a, $b) {
        return $b - $a;
    }

    /**
     * Returns the total likes the specified topic currently has.
     * 
     * @param int $id - The topic identifier.
     * @return int - Total likes.
     */
    public static function totalTopicLikes($id) {
        $data = self::cache()->getData('likes');
        $total = 0;

        foreach ($data as $like) {
            if ($like->content_id == $id && $like->type == 'topic') $total++;
        }

        return $total;
    }

    /**
     * Returns the data for the last post to the given topic.
     * 
     * @param int $id - The topic identifier.
     * @return object - Last post data object.
     */
    public static function lastPost($id) {
        $lastPost = new \stdClass();
        $data = self::cache()->massGetData(['topics' => 'topics', 'posts' => 'posts']);
        $list = [];

        foreach ($data->posts as $post) {
            if ($post->topic_id == $id) {
                $list[\strtotime($post->created_timestamp)] = $post->id;
            }
        }

        \krsort($list);
        \reset($list);
        $postId = \current($list);

        foreach ($data->posts as $post) {
            if ($post->id == $postId) {
                $lastPost->postId = $post->id;
                $lastPost->authorId = $post->author_user_id;
                $lastPost->timestamp = $post->created_timestamp;
                $lastPost->content = \base64_decode($post->post_content);
                $lastPost->original = \count($list) == 1 ? true : false;
                break;
            }
        }

        foreach ($data->topics as $topic) {
            if ($topic->id == $id) {
                $forumId = $topic->forum_id;
                break;
            }
        }

        $lastPost->content = self::textParsingHelper()->censor(
            $lastPost->content,
            'forums',
            $forumId
        );

        $lastPost->content = \str_replace(["\r", "\n", "<br>"], ' ', $lastPost->content);

        return $lastPost;
    }

    /**
     * Returns the forum bubble for the given forum ID.
     * 
     * @param int $id - The forum identifier.
     * @return mixed - Forum bubble source.
     */
    public static function forumBubble($id) {
        $data = self::cache()->getData('forums');

        foreach ($data as $forum) {
            if ($forum->id == $id) {
                $bubbleColor = $forum->tag_color;
                $bubbleTextColor = $forum->tag_text_color;
                $bubbleIconType = $forum->tag_icon_type;
                $bubbleSource = $forum->tag_icon_source;
                $forumTitle = $forum->title;
                break;
            }
        }

        $icon = null;

        switch ($bubbleIconType) {
            case 'fa': // <- Font Awesome icons.
                $icon = self::output()->getPartial(
                    'ForumsHelper',
                    'BubbleIcon',
                    'FA', [
                        'source' => $bubbleSource
                    ]
                );
                break;

            case 'img': // <- Images
                $icon = self::output()->getPartial(
                    'ForumsHelper',
                    'BubbleIcon',
                    'Img', [
                        'source' => $bubbleSource
                    ]
                );
                break;
        }

        return self::output()->getPartial(
            'ForumsHelper',
            'BubbleIcon',
            'Bubble', [
                'icon' => $icon,
                'title' => $forumTitle,
                'textColor' => $bubbleTextColor,
                'bgColor' => $bubbleColor,
                'tooltip' => self::localization()->quickReplace('forumshelper', 'forumBubbleTooltip', 'title', $forumTitle)
            ]
        );
    }

    /**
     * Returns the data for either all topics in all forums or topics
     * in a specified forum identifier.
     * 
     * @param int $forumId - Optional forum identifier.
     * @return array - Array containing the topic data.
     */
    private static function getTopicData($forumId = null) {
        $data = self::cache()->massGetData(['topics' => 'topics', 'posts' => 'posts']);
        $topicsData = [];

        if ($forumId != null) {
            foreach ($data->topics as $topic) {
                if ($topic->forum_id == $forumId) {
                    $topicsData[] = $topic;
                }
            }
        } else {
            foreach ($data->topics as $topic) {
                $topicsData[] = $topic;
            }
        }

        return $topicsData;
    }

    /**
     * Retrieves topics data based on the provided data.
     * 
     * @param array $options - Options for filter topics (e.g. forumId).
     * @return array - Array of topic objects.
     */
    private static function getTopics($options) {
        $topicsData = [];
        $data = self::cache()->massGetData(['topics' => 'topics', 'posts' => 'posts']);
        
        if (isset($options['forumId'])) {
            $topicsData = self::getTopicData($options['forumId']);
        } else {
            $topicsData = self::getTopicData();
        }

        $topics = [];

        foreach ($topicsData as $topic) {
            $lastPost = self::lastPost($topic->id);

            $topics[] = new \Discussify\Entities\Topic(
                $topic->id,
                $topic->forum_id,
                $topic->title,
                $topic->started_user_id,
                $topic->started_timestamp,
                $topic->views,
                $topic->replies,
                $topic->question == 1 ? true : false,
                $topic->question_solved == 1 ? true : false,
                $topic->question_solved_user_id,
                $topic->question_solved_timestamp,
                $topic->poll == 1 ? true : false,
                $topic->locked == 1 ? true : false,
                $topic->preview = $lastPost->content
            );
        }

        return $topics;
    }

    /**
     * Sorts the given array of topics data based on a specified criteria.
     * 
     * @param array $data - Array of topic objects to be sorted.
     * @return array - Array of sorted topic objects.
     */
    private static function sortData($data) {
        $sortBy = self::utils()->getSortBy();
        $ordered = null;
        
        switch ($sortBy) {
            case 'latest':
                \usort($data, function($a, $b) {
                    return $b->getStartedTimestamp() - $a->getStartedTimestamp();
                });
                break;

            case 'oldest':
                \usort($data, function($a, $b) {
                    return $a->getStartedTimestamp() - $b->getStartedTimestamp();
                });
                break;

            case 'popular':
                \usort($data, function($a, $b) {
                    return $b->getViews() - $a->getViews();
                });
                break;

            case 'answered':
                \usort($data, function($a, $b) {
                    if ($a->getQuestion() && !$b->getQuestion()) {
                        return -1;
                    }

                    if (!$a->getQuestion() && $b->getQuestion()) {
                        return 1;
                    }

                    if ($a->getQuestion() && $b->getQuestion()) {
                        if ($a->getQuestionSolved() && !$b->getQuestionSolved()) {
                            return -1;
                        }

                        if (!$a->getQuestionSolved() && $b->getQuestionSolved()) {
                            return 1;
                        }
                    }

                    return $b->getStartedTimestamp() - $a->getStartedTimestamp();
                });
                break;
        }

        return $data;
    }

    /**
     * Retrieves a list of topics based on provided options.
     * 
     * @param array $options - Options for filtering, sorting, and loading topics.
     * @return mixed - The topic list source.
     */
    public static function getTopicsList($options = []) {
        $topics = '';
        
        $topicsData = self::getTopics($options);
        $orderedTopics = self::sortData($topicsData);

        if (isset($options['index'])) {
            $lastIndex = $options['index'];
        } else {
            $lastIndex = 0;
        }

        $limit = self::user()->entryLimit('topics');
        $totalToLoad = self::math()->calculateEntriesToLoad($orderedTopics, $limit, $lastIndex);
        $endIndex = $lastIndex + $totalToLoad - 1;

        if ($totalToLoad < $limit) {
            $total = $totalToLoad;
        } else {
            $total = $endIndex;
        }
 
        for ($i = $lastIndex; $i <= $endIndex; $i++) {
            $topics .= $orderedTopics[$i]->buildTopic();
        }

        return $topics;
    }

    /**
     * Builds the filter drop down and menu and returns it.
     * 
     * @return object - Filter data object.
     */
    public static function getFilter() {
        if (isset($_SESSION['discussify_forum_filter'])) {
            $sortBy = $_SESSION['discussify_forum_filter'];
        } else {
            $sortBy = self::user()->sortBy();
        }

        $options = ['latest', 'oldest', 'popular', 'answered'];
        $filter = new \stdClass();
        $items = "";

        foreach ($options as $option) {
            if ($option == $sortBy) {
                $items .= self::output()->getPartial(
                    'ForumsHelper',
                    'Filter',
                    'ItemSelected', [
                        'name' => \ucfirst($option),
                        'spanId' => $option
                    ]
                );
            } else {
                $items .= self::output()->getPartial(
                    'ForumsHelper',
                    'Filter',
                    'Item', [
                        'name' => \ucfirst($option),
                        'spanId' => $option
                    ]
                );
            }
        }

        $filter->dropdown = self::output()->getPartial(
            'ForumsHelper',
            'DropDown',
            'ForumsFilter', [
                'items' => $items
            ]
        );

        $filter->button = self::output()->getPartial('ForumsHelper', 'ForumsFilter', 'Button', ['sortBy' => self::localization()->getWords('index', $sortBy)]);

        return $filter;
    }

    /**
     * Returns whether or not the specified forum exists.
     * 
     * @param int $id - The forum identifier.
     * @return bool - True if exists, false otherwise.
     */
    public static function forumExist($id) {
        $data = self::cache()->getData('forums');

        foreach ($data as $forum) {
            if ($forum->id == $id) {
                return true;
            }
        }

        return false;
    }
}