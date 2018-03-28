<?php
/**
 *   VGallery: CMS based on FormsFramework
 * Copyright (C) 2004-2015 Alessandro Stucchi <wolfgan@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * @package VGallery
 * @subpackage core
 * @author Alessandro Stucchi <wolfgan@gmail.com>
 * @copyright Copyright (c) 2004, Alessandro Stucchi
 * @license http://opensource.org/licenses/lgpl-3.0.html
 * @link https://bitbucket.org/cmsff/vgallery
 */



switch($_SERVER["PATH_INFO"]) {
	case "/refresh":
		$url = parse_url($_REQUEST["url"]);

		$_SERVER["PATH_INFO"] = $url["path"];
		$_SERVER['QUERY_STRING'] = $url["query"];

		parse_str($_SERVER['QUERY_STRING'], $_GET);
		$_REQUEST = $_GET;
		$_POST = array();


		break;
}

require(__DIR__ . "../../../../index.php");


Cache::log(print_r($_SERVER, true) . print_r($_REQUEST, true), "testncache");