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

namespace Discussify\Entities;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that represents a single topic.
 * 
 * @package \Discussify\Entities
 */
class Topic extends \Discussify\Application {
    /**
     * The topic identifier.
     * @var int
     */
    private $id;

    /**
     * The forum identifier the topic belong to.
     * @var int
     */
    private $forumId;

    /**
     * The title of the topic.
     * @var string
     */
    private $title;

    /**
     * The user identifier of the user that started this topic.
     * @var int
     */
    private $startedById;

    /**
     * The timestamp of when the user created this topic.
     * @var int
     */
    private $startedTimestamp;

    /**
     * The total views the topic currently has.
     * @var int
     */
    private $views;

    /**
     * The total replies the topic currently has.
     * @var int
     */
    private $replies;

    /**
     * The total posts the topic currently has.
     * @var int
     */
    private $posts;
    
    /**
     * Is this topic a question topic?
     * @var bool
     */
    private $question;

    /**
     * If this topic is a question topic, has that question been solved?
     * @var bool
     */
    private $questionSolved;

    /**
     * If the topic question has been solved, the user identifier of who solved it.
     * @var int
     */
    private $questionSolvedUserId;

    /**
     * The timestamp of when the question was solved.
     * @var int
     */
    private $questionSolvedTimestamp;

    /**
     * Does this topic include a poll?
     * @var bool
     */
    private $poll;

    /**
     * Is this topic currently locked?
     * @var bool
     */
    private $locked;

    /**
     * The content preview for the topic from the post.
     * @var string
     */
    private $preview;

    /**
     * Class constructor that populates this class's properties.
     * 
     * @param int $id - The topic identifier from the database.
     * @param int $forumId - The forum identifier of the forum the topic belongs to.
     * @param string $title - The topic title.
     * @param int $startedById - The identifier of the user that started the topic.
     * @param int $startedTimestamp - The timestamp of when the topic was first started.
     * @param int $views - The total views.
     * @param int $replies - The total replies.
     * @param bool $question - True if a querstion topic, false otherwise.
     * @param bool $questionSolved - True if the question has been solved, false otherwise.
     * @param int $questionSolvedUserId - The identifier of the user that solved the question.
     * @param int $questionSolvedTimestamp - The timestamp of when the questin was solved.
     * @param bool $poll - True if the topic contains a poll, false otherwise.
     * @param bool $locked - True if the topic is locked, false otherwise.
     * @param string $preview - The preview of the content from the topic.
     */
    public function __construct($id, $forumId, $title, $startedById, $startedTimestamp, $views, $replies, $question, $questionSolved, $questionSolvedUserId, $questionSolvedTimestamp, $poll, $locked, $preview) {
        $this->id = $id;
        $this->forumId = $forumId;
        $this->title = $title;
        $this->startedById = $startedById;
        $this->startedTimestamp = \strtotime($startedTimestamp);
        $this->views = $views;
        $this->replies = $replies;
        $this->posts = $this->replies + 1;
        $this->question = $question;
        $this->questionSolved = $questionSolved;
        $this->questionSolvedUserId = $questionSolvedUserId;
        $this->questionSolvedTimestamp = \strtotime($questionSolvedTimestamp);
        $this->poll = $poll;
        $this->locked = $locked;
        $this->preview = self::utils()->limitString($preview, self::user()->charLimit('topicsList'));
    }

    /**
     * Returns the topic identifier.
     * 
     * @return int - The topic identifier.
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Sets the identifier for this topic.
     * 
     * @param int $id - The topic identifier.
     */
    public function setId($id) {
        $this->id = $id;
    }

    /**
     * Returns the identifier of the forum this topic belongs to.
     * 
     * @return int - The forum identifier the topic belongs to.
     */
    public function getForumId() {
        return $this->forumId;
    }

    /**
     * Sets the identifier of the forum this topic belongs to.
     * 
     * @param int $forumId - The forum identifier this topic belongs to.
     */
    public function setForumId($forumId) {
        $this->forumId = $forumId;
    }

    /**
     * Returns the title of the topic.
     * 
     * @return string - The topic title.
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * Sets the topic title.
     * 
     * @param string $title - The topic title of this topic.
     */
    public function setTitle($title) {
        $this->title = $title;
    }

    /**
     * Returns the identifier of the user that started this topic.
     * 
     * @return int - User identifier of user that started this topic.
     */
    public function getStartedById() {
        return $this->startedById;
    }

    /**
     * Sets the identifier of the user that started this topic.
     * 
     * @param int $startedById - The user identifier of the user that started this topic.
     */
    public function setStartedById($startedById) {
        $this->startedById = $startedById;
    }

    /**
     * Returns the timestamp of when this topic was started.
     * 
     * @return int - Tiemstamp of when this topic was started.
     */
    public function getStartedTimestamp() {
        return $this->startedTimestamp;
    }

    /**
     * Sets the timestamp of when this topic was started.
     * 
     * @param int $startedTimestamp - Timestamp of when this topic was started.
     */
    public function setStartedTimestamp($startedTimestamp) {
        $this->startedTimestamp = \strtotime($startedTimestamp);
    }

    /**
     * Returns the total views this topic has.
     * 
     * @return int - The total views this topic has.
     */
    public function getViews() {
        return $this->views;
    }

    /**
     * Sets the total views this topic has.
     * 
     * @param int $views - The total views the topic has.
     */
    public function setViews($views) {
        $this->views = $views;
    }

    /**
     * Returns the total replies this topic has.
     * 
     * @return int - Total replies the topic has.
     */
    public function getReplies() {
        return $this->replies;
    }

    /**
     * Sets the total replies this topic has.
     * 
     * @param int $replies - Total replies this topic has.
     */
    public function setReplies($replies) {
        $this->replies = $replies;
    }

    /**
     * Returns the total posts this topic currently has.
     * 
     * @return int - Total posts.
     */
    public function getPosts() {
        return $this->posts;
    }

    /**
     * Sets the total posts for this topic.
     * 
     * @param int $posts - Total posts.
     */
    public function setPosts($posts) {
        $this->posts = $posts;
    }

    /**
     * Returns the flag indicating whether this topic is a question topic.
     * 
     * @return bool - True if a question topic, false otherwise.
     */
    public function getQuestion() {
        return $this->question;
    }

    /**
     * Sets the flag indicating whether this topic is a question topic.
     * 
     * @param bool $question - True if a question topic, false otherwise.
     */
    public function setQuestion($question) {
        $this->question = $question;
    }

    /**
     * Returns the flag indicating whether this topic's question has been solved.
     * 
     * @return bool - True if the question is solved, false otherwise.
     */
    public function getQuestionSolved() {
        return $this->questionSolved;
    }

    /**
     * Sets the flag indicating whether the question has been solved.
     * 
     * @param bool $questionSolved - True if question is solved, false otherwise.
     */
    public function setQuestionSolved($questionSolved) {
        $this->questionSolved = $questionSolved;
    }

    /**
     * Returns the identifier of the user who solved the question.
     * 
     * @return int - User identifier of user who solved the question.
     */
    public function getQuestionSolvedUserId() {
        return $this->questionSolvedUserId;
    }

    /**
     * Sets the user identifier of the user that solved the question.
     * 
     * @param int $questionSolvedUserId - User identifier of user who solved the question.
     */
    public function setQuestionSolvedUserId($questionSolvedUserId) {
        $this->getQuestionSolvedUserId = $questionSolvedUserId;
    }

    /**
     * Returns the timestamp of when the question of solved.
     * 
     * @return int - Timestamp of when the question was solved.
     */
    public function getQuestionSolvedTimestamp() {
        return $this->questionSolvedTimestamp;
    }

    /**
     * Sets the timestamp of when the question was solved.
     * 
     * @param int $questionSolvedTimestamp - Timestamp of when the question was solved.
     */
    public function setQuestionSolvedTimestamp($questionSolvedTimestamp) {
        $this->questionSolvedTimestamp = \strtotime($questionSolvedTimestamp);
    }

    /**
     * Returns the flag indicating whether the topic contains a poll.
     * 
     * @return bool - True if topic contains a poll, false otherwise.
     */
    public function getPoll() {
        return $this->poll;
    }

    /**
     * Sets the flag indicating whether the topic contains a poll.
     * 
     * @return bool - True if topic contains a poll, false otherwise.
     */
    public function setPoll($poll) {
        $this->poll = $poll;
    }

    /**
     * Returns the flag indicating whether the topic is locked.
     * 
     * @return bool - True if locked, false otherwise.
     */
    public function getLocked() {
        return $this->locked;
    }

    /**
     * Sets the flag indicating whether the topic is locked.
     * 
     * @param bool $locked - True if locked, false otherwise.
     */
    public function setLocked($locked) {
        $this->locked = $locked;
    }

    /**
     * Returns the preview content for this topic.
     * 
     * @return string - Preview content.
     */
    public function getPreview() {
        return $this->preview;
    }

    /**
     * Sets the preview content for this topic.
     * 
     * @param string $preview - Preview content.
     */
    public function setPreview($preview) {
        $this->preview = self::utils()->limitString($preview, self::user()->charLimit('topicsList'));
    }

    /**
     * Returns the total topic views as a formatted number.
     * 
     * @return string - Formatted number.
     */
    public function getViewsFormatted() {
        return self::math()->formatNumber($this->views);
    }

    /**
     * Returns the total replies as a formatted number.
     * 
     * @return string - Formatted number.
     */
    public function getRepliesFormatted() {
        return self::math()->formatNumber($this->replies);
    }

    /**
     * Returns the total posts as a formatted number.
     * 
     * @return string - Formatted number.
     */
    public function getPostsFormatted() {
        return self::math()->formatNumber($this->posts);
    }

    /**
     * Builds the topic so it's ready for the UI.
     * 
     * @return mixed - Topic source.
     */
    public function buildTopic() {
        $lastPost = self::forumsHelper()->lastPost($this->id);

        if (!$lastPost->original) {
            $replyAuthor = self::localization()->quickMultiWordReplace('forumshelper', 'replyAuthor', [
                'username' => self::user()->username($lastPost->authorId),
                'timestamp' => self::dateTime()->parse($lastPost->timestamp, ['timeAgo' => true]),
                'icon' => self::output()->getPartial('ForumsHelper', 'Icon', 'Reply')
            ]);
        } else {
            $replyAuthor = '';
        }

        return self::output()->getPartial(
            'ForumsHelper',
            'Topic',
            'Item', [
                'photo' => self::user()->profilePhoto($this->startedById, ['thumbnail' => true, 'link' => true]),
                'topicTitle' => $this->title,
                'preview' => $this->preview,
                'totalPosts' => $this->getPostsFormatted(),
                'totalViews' => $this->getViewsFormatted(),
                'postsTooltip' => self::localization()->quickReplace('forumshelper', 'topicPostsStateTooltip', 'total', $this->getPostsFormatted()),
                'viewsTooltip' => self::localization()->quickReplace('forumshelper', 'topicViewsStatsTooltip', 'total', $this->getViewsFormatted()),
                'forumBubble' => self::forumsHelper()->forumBubble($this->forumId),
                'byAuthor' => self::localization()->quickMultiWordReplace('forumshelper', 'byAuthor', [
                    'username' => self::user()->username($this->startedById),
                    'timestamp' => self::dateTime()->parse($this->startedTimestamp, ['timeAgo' => true]),
                    'icon' => self::output()->getPartial('ForumsHelper', 'Icon', 'PenToSquare')
                ]),
                'replyAuthor' => $replyAuthor,
                'url' => self::seo()->url('topic', 'view', ['id' => self::urls()->getDualUrl($this->id, $this->title)])
            ]
        );
    }
}