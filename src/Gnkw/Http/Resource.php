<?php
/*
* Copyright (c) 2013 GNKW
*
* This file is part of GNKW Http.
*
* GNKW Http is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* GNKW Http is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with GNKW Http.  If not, see <http://www.gnu.org/licenses/>.
*/

namespace Gnkw\Http;

/**
 * Resource class
 * @author Anthony <anthony.rey@mailoo.org>
 * @since 08/09/2013
 */
class Resource
{
	/**
	* Resource content
	* @var string
	*/
	private $content;

	/**
	* HTTP response code
	* @var integer
	*/
	private $responseCode;

	/**
	* Content type format used
	* @var string
	*/
	private $format;
	
	/**
	* HTTP Headers
	* @var array
	*/
	private $headers = array();

	/**
	* Resource constructor
	* @param string $content Content recieved
	* @param integer $responseCode Response code recieved
	* @param string $format Content type format
	*/
	public function __construct($content, $responseCode, $format='custom')
	{
		$usableFormat = trim(strtolower($format));
		switch($usableFormat)
		{
			case 'json':
				$this->format = 'json';
				break;
			case 'xml':
				$this->format = 'xml';
				break;
			default:
				$this->format = 'custom';
				break;
		}
		$this->content = $content;
		$this->responseCode = $responseCode;
	}

	/**
	* Return the response code of the resource
	* @return integer The response code
	*/
	public function getResponseCode()
	{
		return $this->responseCode;
	}

	/**
	* Return the response code of the resource
	* @see getResponseCode
	* @return boolean 
	*/
	public function code($code)
	{
		return $this->responseCode == $code;
	}

	/**
	* Return the json object of the resource
	* @param boolean If true, it return an array, else, it return a json object
	* @return mixed
	*/
	public function json($assoc=false)
	{
		return json_decode($this->content, $assoc);
	}

	/**
	* Return the xml object of the ressource
	* @param string $type The type of object (dom or simple)
	* > dom = DOMDocument
	* > simple = SimpleXMLElement
	* @return DOMDocument or SimpleXMLElement
	*/
	public function xml($type = 'dom')
	{
		$xml = $this->content;
		$type = trim(strtolower($type));
		switch($type)
		{
			case 'dom':
				$xml = new \DOMDocument();
				$xml->loadXML($this->content);
				break;
			case 'simple':
				$xml = new \SimpleXMLElement($this->content);
				break;
		}

		return $xml;
	}

	/**
	* Get content from the resource
	* @return string
	*/
	public function getContent()
	{
		return $this->content;
	}

	/**
	* Get the format of the resource
	* @return string
	*/
	public function getFormat()
	{
		return $this->format;
	}

	/**
	* Get the content of the ressource
	* Return a result depending on the type of the resource
	* @param mixed $arg The possible argument(s) of the method to call
	* @return mixed
	*/
	public function content($arg=null)
	{
		switch($this->format)
		{
			case 'json':
				if(!isset($arg))
				{
					$arg = false;
				}
				return $this->json($arg);
				break;
			case 'xml' :
				if(!isset($arg))
				{
					$arg = 'dom';
				}
				return $this->xml($arg);
				break;
			default :
				return $this->getContent();
				break;
		}
	}
	
	/**
	* Set headers from string
	* @param string $string Headers in string format (separated with new line)
	*/
	public function setHeadersFromString($string)
	{
		$this->headers = array_filter(array_map('trim',explode("\n", $string)));
	}
	
	/**
	* Get headers
	* @return array Headers
	*/
	public function getHeaders()
	{
		return $this->headers;
	}
}
