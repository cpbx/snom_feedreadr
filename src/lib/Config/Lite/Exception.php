<?php

// require_once 'PEAR/Exception.php'

/**
 * Config_Lite (Config/Lite.php)
 *
 * read & save "INI-Style" Configuration Files
 * fast and with the native php function under the hood.
 *
 * Inspired by Python's ConfigParser.
 *
 * A "Config_Lite" file consists of sections,
 * "[section]"
 * followed by "name = value" entries
 *
 * note: Config_Lite assumes that all name/value entries are in sections.
 *
 * @copyright  	2010 info@pc-e.org
 * @license    	LGPL
 */

// class Config_Lite_Exception extends PEAR_Exception {}
class Config_Lite_Exception extends Exception {}
