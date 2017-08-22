<?php
/**
* Plugin Name: GW | Redirect Posts
* Plugin URI: http://www.guahanweb.com
* Description: Create redirect posts without overriding permalink globally
* Version: 0.1
* Tested With: 4.3.1
* Author: Garth Henson
* Author URI: http://www.guahanweb.com
* License: GPLv2 or later
* Text Domain: gw
* Domain Path: /languages
*/

use GW\RedirectPosts;

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/functions.php';
$plugin = RedirectPosts\Plugin::instance(__FILE__);
