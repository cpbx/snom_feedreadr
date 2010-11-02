<?php

/**
 * 
 * @author    Patrick Engel <info@pc-e.org>
 * @copyright 2010 Patrick Engel
 * @license   http://www.gnu.org/copyleft/lesser.html  LGPL License 2.1
 *
 */

class Request
{
	private $properties;

	public function set($key, $value)
	{
		$this->properties[$key] = $value;
	}

	public function has($key)
	{
		if (isset($this->properties[$key])) {
			return true;
		}
		return false;
	}

	public function get($key, $default=NULL)
	{
		if (!isset($this->properties[$key])) {
			if (!is_null($default)) {
				return $default;
			}
			throw new InvalidArgumentException;
		}
		return $this->properties[$key];
	}

	public function __construct()
	{
		if (isset($_SERVER['REQUEST_METHOD'])) {
			foreach ($_GET as $key => $value) {
				$this->set($key, $value);
			}
			foreach ($_POST as $key => $value) {
				$this->set($key, $value);
			}
		} else {
			foreach ($_SERVER['argv'] as $arg) {
				if (strpos($arg, '=')) {
					list ($key, $value) = explode ('=', $arg);
					$this->set($key, $value);
				}
			}
		}
	}
}
