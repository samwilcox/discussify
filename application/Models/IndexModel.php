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
 * This class is the model for the index controller.
 * 
 * @package Discussify\Models
 */
class IndexModel extends \Discussify\Models\BaseModel {
    
    /**
     * Key value pair collection for tag replacements.
     * @var array
     */
    private static $vars = [];

    /**
     * Responsible for handling operations for the:
     * Controller: index
     * Action: index
     * 
     * @return $vars
     */
    public function appIndex() {
        self::utils()->newBreadcrumb(self::localization()->getWords('index', 'forumsBreadcrumbTitle'), self::seo()->url('index'), true, false);
        self::utils()->setPageTitle(self::localization()->getWords('index', 'forumsPageTitle'));

        self::$vars['topics'] = self::forumsHelper()->getTopicsList(['index' => 0]);

        self::$vars['newTopicButton'] = self::buttonsHelper()->get(
            1,
            self::localization()->getWords('buttonshelper', 'newTopic'),
            self::seo()->url('post'),
            self::output()->getPartial('Global', 'Icon', 'Plus')
        );

        $filter = self::forumsHelper()->getFilter();

        self::$vars['filterButton'] = $filter->button;        
        self::vars()->dropdowns .= $filter->dropdown;
        self::$vars['loadMoreButton'] = self::ajaxHelper()->topicsLoadMore();

        return self::$vars;
    }

    /**
     * Responsible for handling operations for the:
     * Controller: index
     * Action: css
     * 
     * @return $vars
     */
    public function css() {
        self::theme()->getCss(self::request()->id);
    }
}