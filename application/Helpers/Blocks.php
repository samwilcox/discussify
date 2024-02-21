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
 * Helper class for block related routines.
 * 
 * @package \Discussify\Helpers
 */
class Blocks extends \Discussify\Application {
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
     * Returns the HTML source for various block components.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     * @return array - Array collection of block related components.
     */
    public static function getBlocksOutputVars($controller, $action) {
        $retVal = [];
        $haveLeftBlocks = self::block()->haveBlocks($controller, $action, 'left');
        $haveRightBlocks = self::block()->haveBlocks($controller, $action, 'right');

        if ($haveLeftBlocks && $haveRightBlocks) {
            $gridClass = self::output()->getPartial('BlocksHelper', 'Class', 'BlockGridFull');
        } elseif ($haveLeftBlocks && !$haveRightBlocks) {
            $gridClass = self::output()->getPartial('BlocksHelper', 'Class', 'BlockGridLeft');
        } else {
            $gridClass = self::output()->getPartial('BlocksHelper', 'Class', 'BlockGridRight');
        }

        $retVal['blockBegin'] = ($haveLeftBlocks || $haveRightBlocks) ? self::output()->getPartial('BlocksHelper', 'Blocks', 'Begin', ['class' => $gridClass]) : '';
        $retVal['blockEnd'] = ($haveLeftBlocks || $haveRightBlocks) ? self::output()->getPartial('BlocksHelper', 'Blocks', 'End') : '';
        $retVal['blockLeftBegin'] = $haveLeftBlocks ? self::output()->getPartial('BlocksHelper', 'Blocks', 'LeftBegin') : '';
        $retVal['blockLeftEnd'] = $haveLeftBlocks ? self::output()->getPartial('BlocksHelper', 'Blocks', 'LeftEnd') : '';
        $retVal['blockCenterBegin'] = ($haveLeftBlocks || $haveRightBlocks) ? self::output()->getPartial('BlocksHelper', 'Blocks', 'CenterBegin') : '';
        $retVal['blockCenterEnd'] = ($haveLeftBlocks || $haveRightBlocks) ? self::output()->getPartial('BlocksHelper', 'Blocks', 'CenterEnd') : '';
        $retVal['blockRightBegin'] = $haveRightBlocks ? self::output()->getPartial('BlocksHelper', 'Blocks', 'RightBegin') : '';
        $retVal['blockRightEnd'] = $haveRightBlocks ? self::output()->getPartial('BlocksHelper', 'Blocks', 'RightEnd') : '';
        $retVal['leftBlocks'] = $haveLeftBlocks ? self::block()->get($controller, $action, 'left') : '';
        $retVal['rightBlocks'] = $haveRightBlocks ? self::block()->get($controller, $action, 'right') : '';

        return $retVal;
    }

    /**
     * Returns the requested block source.
     * 
     * @param string $block - The name of the block.
     * @return mixed - Block source.
     */
    public static function getRequestedBlock($block) {
        switch ($block) {
            case 'forumsList':
                return self::forumsList();
                break;
        }
    }

    /**
     * Builds the forums list block and returns it.
     * 
     * @return mixed - Forums list block source.
     */
    private static function forumsList() {
        $data = self::cache()->getData('forums');
        $forums = '';
        $exist = true;
    
        if (!self::user()->getForumPermissionsCheck(null, 'view')) {
            $message = self::output()->getPartial(
                'Global',
                'Centered',
                'Text', [
                    'text' => self::localization()->getWords('user', 'noForumsToList')
                ]
            );

            $exist = false;
        }

        if (\count($data) == 0) {
            $message = self::output()->getPartial(
                'Global',
                'Centered',
                'Text', [
                    'text' => self::localization()->getWords('user', 'noForumsToList')
                ]
            );

            $exist = false;
        }

        \usort($data, [self::utils(), 'sortBySortOrder']);
        
        foreach ($data as $forum) {
            if (self::forumsHelper()->forumVisible($forum->id) && $forum->parent_id == 0) {

                if ($forum->tag_icon_type == 'image') {
                    $icon = self::output()->getPartial(
                        'BlocksHelper',
                        'ForumsList',
                        'ImgIcon', [
                            'source' => $forum->tag_icon_source
                        ]
                    );
                } else {
                    $icon = self::output()->getPartial(
                        'BlocksHelper',
                        'ForumsList',
                        'FAIcon', [
                            'source' => $forum->tag_icon_source,
                            'color' => $forum->tag_color
                        ]
                    );
                }

                if (self::forumsHelper()->haveChildForums($forum->id)) {
                    $dta = self::cache()->getData('forums');
                    $i = 0;

                    \usort($dta, [self::utils(), 'sortBySortOrder']);

                    foreach ($dta as $frm) {
                        if ($frm->parent_id == $forum->id) {
                            if ($frm->tag_icon_type == 'image') {
                                $icn = self::output()->getPartial(
                                    'BlocksHelper',
                                    'ForumsList',
                                    'ImgIcon', [
                                        'source' => $frm->tag_icon_source
                                    ]
                                );
                            } else {
                                $icn = self::output()->getPartial(
                                    'BlocksHelper',
                                    'ForumsList',
                                    'FAIcon', [
                                        'source' => $frm->tag_icon_source,
                                        'color' => $frm->tag_color
                                    ]
                                );
                            }

                            $children .= self::output()->getPartial(
                                'BlocksHelper',
                                'ForumsList',
                                'ChildItem', [
                                    'url' => self::seo()->url('forum', 'view', ['id' => self::urls()->getDualUrl($frm->id, $frm->title)]),
                                    'title' => $frm->title,
                                    'icon' => $icn,
                                    'break' => $i > 0 ? self::output()->getPartial('Global', 'Tag', 'Break') : ''
                                ]
                            );

                            $i++;
                        }
                    }
                } else {
                    $children = '';
                }

                $forums .= self::output()->getPartial(
                    'BlocksHelper',
                    'ForumsList',
                    'Item',
                    [
                        'icon' => $icon,
                        'title' => $forum->title,
                        'childForums' => $children
                    ]
                );
            }
        }

        return self::output()->getPartial(
            'BlocksHelper',
            'ForumsList',
            'Block', [
                'forums' => $exist ? $forums : $message
            ]
        );
    }
}