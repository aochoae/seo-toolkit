<?php
/**
 * @link https://developer.wordpress.org/plugins/the-basics/uninstall-methods/
 *
 * @package Toolkit
 */

/* if uninstall.php is not called by WordPress, die */
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

flush_rewrite_rules();

