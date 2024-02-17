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
     * Returns the block for the specified parameters.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     * @param string $section - The section to place the block.
     * @return mixed - Block source.
     */
    public static function get($controller = 'index', $action = 'index', $section = 'left') {
        $data = self::cache()->massGetData(['blocks' => 'blocks', 'placement' => 'block_placement']);
        $found = false;
        $placements = [];
        $blockPlacement = '';

        foreach ($data->placement as $place) {
            if ($place->controller === $controller && $place->action === $action && $place->section === $section) {
                $placements[$place->sort_order] = $place->id;
            }
        }

        foreach ($data->placement as $place) {
            if ($place->controller === 'all' && $place->action === 'all' && $place->section === $section) {
                $placements[$place->sort_order] = $place->id;
            }
         }

         \ksort($placements);

         foreach ($placements as $k => $v) {
            foreach ($data->placement as $place) {
                if ($place->id === $v) {
                    foreach ($data->blocks as $block) {
                        if ($block->id === $place->id) {
                            $blockTitle = $block->title;
                        }
                    }

                    $blockOutput = self::getBlock($blockTitle);
                }
            }
         }

         return \strlen($blockOutput) < 1 ? null : $blockOutput;
    }

    /**
     * Returns whether blocks exist for the specified parameters.
     * 
     * @param string $controller - The name of the controller.
     * @param string $action - The name of the action.
     * @param string $section - The section to place the block.
     * @return bool - True if has blocks, false otherwise.
     */
    public static function haveBlocks($controller = 'index', $action = 'index', $section = 'left') {
        
    }
}