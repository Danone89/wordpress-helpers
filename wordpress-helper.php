<?php

/*
 * @package           wordpress-helpers
 * @author            Daniel Bosnjak
 * @copyright         2019 Pikselownia
 * @license           GPL-2.0-or-later
 * 
 * @wordpress-plugin
 * Plugin Name: Wordpress Helpers
 * Plugin URI: https://pikselownia.com/
 * Text Domain: wp-helpers
 * Version: 2.0
 * Description: Collection of classes for working with common dev tasks in wordpress. Provides unification for your plugin and themes.
 * Simplifies working with Woocomerce, Wordpress, JetPack, TGMPA, settings 
 * 
*/
/*
  Copyright 2020  Daniel Bośnjak

  This program is free software; you can redistribute it and/or
  modify it under the terms of the GNU General Public License
  as published by the Free Software Foundation; either version 2
  of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, see <http://www.gnu.org/licenses/>.
 */


if (!defined('ABSPATH')) {
  die('Error #1 - file should be loaded by wordpress core');
}

require_once trailingslashit(__DIR__) . 'inc/autoloader.php';

register_activation_hook(__FILE__, function () {
  if (class_exists('WP_Queue'))
    wp_queue_install_tables();
});

//facade mail, queue, pods, 
