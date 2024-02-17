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

namespace Discussify\Type;

// This file may not be accessed directly.
if (!defined('APP_ACTIVE')) {
    \header(isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0 forbidden');
    exit(1);
}

/**
 * Constant class for access to various bulletin board features.
 * 
 * @package \Discussify\Type
 */
class Feature extends \Discussify\Type\Types {
    // All forum related features: link to forums and access to forum listing.
    const FORUMS = 'forums';

    // Access to the members list.
    const MEMBERS_LIST = 'members';

    // Access to active users listing.
    const ACTIVE_USERS = 'activeUsers';

    // Access to search link and page.
    const SEARCH = 'search';

    // Access to help documentation and link.
    const HELP = 'help';

    // Access to listing of groups page and link.
    const GROUPS_LIST = 'groupsList';

    // Access to listing of community leaders and link.
    const COMMUNITY_LEADERS = 'communityLeaders';

    // Access to listing of tags and link.
    const TAGS = 'tags';

    // Whether the user can select a language.
    const LANGUAGE_SELECT = 'languageSelect';

    // Whether the user can select a theme.
    const THEME_SELECT = 'themeSelect';
}