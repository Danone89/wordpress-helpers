<?php

/*
  Plugin Name: Runtime Libraries
  Plugin URI: https://pikselownia.com/
  Description: Execution envairoment for pikselownia.com plugins and themes.
  Version: 2.0
  Author: Daniel Bośnjak
  Author URI: https://pikselownia.com/

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


require_once trailingslashit(__DIR__).'inc/autoloader.php';
require_once trailingslashit(__DIR__).'inc/general-functions.php';
require_once trailingslashit(__DIR__).'inc/wordpress-functions.php';
require_once trailingslashit(__DIR__).'inc/woocommerce-functions.php';
