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

namespace Discussify\Blocks;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Class that manages the various blocks on set pages.
 * 
 * @package \Discussify\Blocks
 */
class Block extends \Discussify\Application {
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
     * Returns the blocks for the specified parameters.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     * @param string $section - The section to place the block.
     * @return mixed - Block source.
     */
    public static function get($controller = 'index', $action = 'index', $section = 'left') {
        $blocks = self::user()->blocks();
        $placements = [];
        $blockOutput = '';

        for ($i = 0; $i < \count($blocks); $i++) {
            if ($blocks[$i]['controller'] === $controller && $blocks[$i]['action'] === $action && $blocks[$i]['section'] === $section) {
                $placements[$blocks[$i]['order']] = $i;
            }
        }

        for ($i = 0; $i < \count($blocks); $i++) {
            if ($blocks[$i]['controller'] === 'all' && $blocks[$i]['action'] === 'all' && $blocks[$i]['section'] === $section) {
                $placements[$blocks[$i]['order']] = $i;
            }
        }

        \ksort($placements);

        foreach ($placements as $k => $v) {
            for ($i = 0; $i < \count($blocks); $i++) {
                if ($i === $v) {
                    $blockOutput .= self::getBlock($blocks[$i]['title']);
                }
            }
        }

        return \strlen($blockOutput) < 1 ? null : $blockOutput;
    }

    /**
     * Returns whether or not if there are blocks for the specified parameters.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     * @param string $section - The section name.
     * @return bool - True if blocks are present, false otherwise.
     */
    public static function haveBlocks($controller = 'index', $action = 'index', $section = 'left') {
        $blocks = self::user()->blocks();

        foreach ($blocks as $block) {
            if (($block['controller'] === $controller || $block['controller'] == 'all') && ($block['action'] === $action || $block['action'] === 'all') && $block['section'] === $section) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the requested block source.
     * 
     * @param string $block - The name of the block.
     * @return mixed - Block source.
     */
    public static function getBlock($block) {
        return self::blocksHelper()->getRequestedBlock($block);
    }
}