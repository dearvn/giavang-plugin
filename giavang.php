<?php
/**
 * Plugin Name: Giavang
 * Plugin URI: https://github.com/dearvn/giavang-plugin
 * Description: The most popular way to display gold, dollar price at Viet Nam on your WordPress website. Easy implementation using a shortcode or widget.
 * Version: 1.0.0
 * Author: dearvn
 * Author URI: https://github.com/dearvn
 * License: GPLv2
 * Text Domain: giavang
 *
 * @package giavang
 */

/*
Copyright 2022  Donald  (email : donald.nguyen.it@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.
*/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/**
 * Pulls in the main plugin class.
 */
require_once 'inc/class-giavang.php';
//Giavang::get_instance();

/**
 * Register activation hook
 */
add_action( 'activated_plugin', array( Giavang::get_instance(), 'activate' ) );
