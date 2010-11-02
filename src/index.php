<?php

/**
 * snom_feedreadr
 * 
 * Copyright (C) 2010  by Patrick Engel                                       
 * engel@cpbx.eu                                                                
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * 
 * 
 * @package 	snom_feedreadr
 * @brief 		feedreadr is a simple Feedreader for the Snom Mini XML Browser 
 * @author 		Patrick C. Engel <engel@cpbx.eu>
 * @copyright 	2010 Patrick C. Engel <engel@cpbx.eu>
 * @license		GNU GPL v3.
 * 
 */

// ******************* i18n *******************

$t9n = array(
    'en' => array('new'=>'new', 'newest'=>'newest', 'message'=>'message', 'messages'=>'messages'),
    'de' => array('new'=>'neue','newest'=>'neuste', 'message'=>'Nachricht', 'messages'=>'Nachrichten')
);

function t($msgid) {
	global $t9n, $config;
	return $t9n[$config->get('general', 'lang', 'en')][$msgid];
}


// ******************* main *******************

require_once './lib/Config/Lite.php';
require_once './lib/Config/Lite/Exception.php';
require_once './lib/Request.php';
require_once './lib/vendors/simplepie.php';
require_once './app/FeedReadr.php';

$config = new Config_Lite;
$config->read('./config/feedreadr.cfg');
$request = new Request;
$app = new FeedReadr($config, $request);
$app->run();
