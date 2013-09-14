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

use Gnkw\Tools\Escape;

/**
 * Request class
 * @author Anthony <anthony.rey@mailoo.org>
 * @since 08/09/2013
 */
class Request
{
	/**
	* Request url
	* @var string
	*/
	protected $url;
	
	/**
	* HTTP type
	* @var string
	*/
	protected $type;
	
	/**
	* HTTP headers
	* @var array
	*/
	protected $headers = null;
	
	/**
	* HTTP Content
	* @var string
	*/
	protected $content = null;
	
	/**
	* Content type format
	* @var string
	*/
	protected $format = 'custom';
	
	/**
	* HTTP Cookies
	* @var string
	*/
	protected $cookies = null;

	/**
	 * Request constructor
	 * @param string $url Url to call
	 * @param string $type Request type
	 */
	public function __construct($url, $type = 'GET')
	{
		$this->url = $url;
		$this->setType($type);
	}
	
	/**
	* Set headers
	* @param array $headers
	*/
	public function setHeaders($headers)
	{
		if(is_array($headers))
		{
			$this->headers = $headers;
		}
	}
	
	/**
	* Get headers
	* @return array $this->headers 
	*/
	public function getHeaders()
	{
		return $this->headers;
	}
	
	
	/**
	* Set content
	* @param string $content
	*/
	public function setContent($content)
	{
		if(is_string($content))
		{
			$this->content = $content;
		}
	}
	
	/**
	* Get content
	* @return string $this->content
	*/
	public function getContent()
	{
		return $this->content;
	}
	
	/**
	* Set type
	* @param string $type
	*/
	public function setType($type)
	{
		if(is_string($type))
		{
			$this->type = strtoupper($type);
		}
	}
	
	/**
	* Set format
	* @param string $format
	*/
	public function setFormat($format)
	{
		if(is_string($format))
		{
			$this->format = $format;
		}
	}
	
	/**
	* Set cookies
	* @param array|string $cookies
	*/
	public function setCookies($cookies)
	{
		if(is_string($cookies) OR is_array($cookies))
		{
			if(is_string($cookies))
			{
				$this->cookies = $cookies;
			}
			else
			{
				$arrayCookies = array();
				foreach($cookies as $key => $cookie){
					$arrayCookies[] = implode('=', array($key, $cookie));
				}
				$this->cookies = implode('; ', $arrayCookies);
			}
			return true;
		}
		return false;
	}
	
	/**
	* Get the resource using cUrl
	* @param string $format
	* @return Gnkw\Http\Resource 
	*/
	public function getResource($format = null)
	{
		$this->setFormat($format);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->url);
		if(isset($this->headers))
		{
			curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
		}
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $this->type);
		if(isset($this->content))
		{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $this->content);
		}
		if(isset($this->cookies))
		{
			curl_setopt($ch, CURLOPT_COOKIE, $this->cookies);
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		$resourceMessage = curl_exec($ch);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = substr($resourceMessage, 0, $header_size);
		$content = substr($resourceMessage, $header_size);
		$responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		$resource = new Resource($content, $responseCode, $this->format);
		$resource->setHeadersFromString($headers);
		return $resource;
	}
	
	/**
	* Get Curl Call
	* @return string
	*/
	public function curlCall()
	{
		try
		{
			$x = $this->type;
			$url = Escape::quotes($this->url, 'simple');
			$curl = 'curl -X '.$x;
			if(null !== $this->content)
			{
				$d = Escape::quotes($this->content, 'simple');
				$curl .= ' -d \''.$d.'\'';
			}
			$curl .= ' \''.$url.'\'';
			if(null !== $this->cookies)
			{
				$b = Escape::quotes($this->cookies, 'simple');
				$curl .= ' -b ' . '\'' . $this->cookies . '\'';
			}
			if(null !== $this->headers)
			{
				foreach($this->headers AS $h)
				{
					$curl .= ' -H \''.Escape::quotes($h, 'simple').'\'';
				}
			}
			return $curl;
		}
		catch(\Exception $e)
		{
			return null;
		}
	}
}
