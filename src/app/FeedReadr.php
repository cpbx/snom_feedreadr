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
 * @todo		App: inject "generic" SnomXmlApp, ie. $this->xml->text())
 * @todo		sort multiple feeds by date  
 * @todo	  	seperate normalize function  
 * 
 */
class FeedReadr
{

	/**
	 * @var Request
	 */
	protected $request;

	/**
	 * @var string
	 */
	protected $encoding;


	public function xml_header()
	{
		header('Content-Type: text/xml');
		printf('<?xml version="1.0" encoding="%s"?>', $this->encoding);
	}

	public function  xml_error($text)
	{
		printf("<SnomIPPhoneText><Title>%s</Title><Text>"
				."ERROR: %s</Text></SnomIPPhoneText>\n", 
				$this->config->get('general', "appname"), $text);
	}

	public function  xml_text($text, $title=NULL)
	{

		if (is_null($title)) {
			$title = $this->config->get('general', "appname");
		}
		$title = html_entity_decode($title, 
					ENT_COMPAT, 
					strtoupper($this->encoding)
				 );
		printf("<SnomIPPhoneText><Title>%s</Title><Text>%s</Text>"
				."</SnomIPPhoneText>\n", $title, 
				html_entity_decode($text, ENT_COMPAT, 
				strtoupper($this->encoding))
		);
	}

	public function  xml_404()
	{
		$this->xml_error("error 404: command not found");
	}

	public function xml_start()
	{
		$this->xml_readfeeds();
	}

	public function xml_readfeeds()
	{
		$feeds = $this->get_feed_titles();
		print '<SnomIPPhoneMenu>';
		printf ('<Title>%s</Title>', 
				$this->config->get("general","appname"));
		foreach ($feeds as $id => $feed) {
			$title = $this->xml_sanitize_content($feed['title']); 
			
			$title = html_entity_decode($title, 
						ENT_COMPAT, strtoupper($this->encoding)
					);
			
			printf ("<MenuItem><Name>%s</Name>"
					."<URL>http://%s?cmd=read&id=%d</URL></MenuItem>", 
					$title, 
					$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'], $id);
		}
		print '</SnomIPPhoneMenu>';
	}

	public function xml_readfeed($id)
	{
		$text = $this->get_feed_by_id($id);
		if (empty($text)) {
			$this->xml_error(sprintf(
						"the feed id `%d' returned no content.", $id)
			);
			exit;
		}
		$content = $this->xml_sanitize_content($text['content']);
		$this->xml_text($content.'<br/>'.$text['date'], 
						$this->xml_sanitize_content($text['title'])
		);
	}
	
	protected function xml_sanitize($str) 
	{
		$search = array('"', '&#8220;', '&#8221;', "\n");
		$replace = array('\'', '\'', '\'', ' ');
		
		return str_replace(
			$search,
			$replace,
			$str
		);
	}
		
	protected function xml_sanitize_content($str) 
	{
		$str = str_replace('<br>', '<br/>', $str);
		$str = strip_tags($str, '<br/>');
		return $this->xml_sanitize($str);
	}
	

	protected function get_feed_by_id($id)
	{
		$text = array();
		$counter = 0;
		foreach($this->config->getSection('feeds') as $feed_url) {
			$feed = new SimplePie();
			$feed->set_feed_url($feed_url);
			$feed->enable_cache(false);
			$feed->enable_order_by_date(true);
			// $feed->set_cache_location($_SERVER['DOCUMENT_ROOT'].'/cache');
			$feed->init();
			$feed->handle_content_type();

			$max = $feed->get_item_quantity(
							$this->config->get('general', 'item_max', 5)
						);
			for ($i = 0; $i < $max; $i++) {
				if ($counter == $id) {
					$item = $feed->get_item($i);
					$text['date'] = $item->get_date(
						$this->config->get('general', 'item_date', 'r')
					);
					$text['title'] = $item->get_title();
					$text['content'] = $item->get_description();
					if (empty($text['content'])) {
						if ($item->get_content()) {
							$text['content'] = $item->get_content();
						} else {
							$text['content'] = 'No further content in the feed available.';
						}
					}
					return $text;
				}
				$counter++;
			}
		}
		return $text;
	}


	protected function build_query($params)
	{
		$separator = '&'; // &amp;
		$is = '=';
		$q = '';
		foreach($params as $key => $value) {
			$q .= $key.$is.$value.$separator;
		}
		return $q;
	}

	protected function get_feed_titles()
	{
		$text = array();
		$id = 0;
		foreach($this->config->getSection('feeds') as $feed_url) {
			$feed = new SimplePie();
			$feed->set_feed_url($feed_url);
			$feed->enable_cache(false);
			$feed->enable_order_by_date(true);
			// $feed->set_cache_location($_SERVER['DOCUMENT_ROOT'].'/cache');
			$feed->init();
			$feed->handle_content_type();

			$max = $feed->get_item_quantity(
					$this->config->get('general', 'item_max', 5)
				);
			for ($i = 0; $i < $max; $i++) {
				$item = $feed->get_item($i);
				// $text[$i]['date']=$item->get_date('j M Y | g:i a T');
				// workarround empty titles
				$title = $item->get_title();
				if (!empty($title)) {
					$text[$id]['title'] = $title;
					$id++;
				}
			}
		}
		return $text;
	}

	public function run()
	{
		$this->xml_header();
		// init cmd
		if ($this->request->has('cmd')) {
			$cmd = $this->request->get('cmd');
		} else {
			$cmd = 'start';
		}
		// init id
		if ($this->request->has('id')) {
			$id = $this->request->get('id');
		} else {
			$id = NULL;
		}
		// dispatch cmd
		if($cmd == "start") {
			$this->xml_start();
		} elseif($cmd == "read") {
			if(!is_null($id)) {
				$this->xml_readfeed($id);
			} else {
				$this->xml_readfeeds();
			}
		} else
		$this->xml_404($config);
	}

	public function init()
	{
		try {
			$this->feeds = $this->config->getSection('feeds');
		} catch (UnexpectedValueException $e) {
			$this->xml_error('section not found: feeds');
		}
		if (!isset($this->encoding)) {
			$this->encoding = 'UTF-8';
		}
	}

	public function __construct($config, $request)
	{
		$this->config = $config;
		$this->request = $request;
		$this->init();
	}
}

