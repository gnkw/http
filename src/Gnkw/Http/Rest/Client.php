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

namespace Gnkw\Http\Rest;

use Gnkw\Http\Uri;
use Gnkw\Http\Request;

/**
 * Client class
 * @author Anthony <anthony.rey@mailoo.org>
 * @since 08/09/2013
 */
class Client
{

	/**
	* Base Url Webservices Repository
	* @var string
	*/
	protected $baseUrl;
	
	/**
	* Resource format
	* @var string
	*/
	protected $resource;
	
	/**
	* Default resource format
	* @var string
	*/
	protected $defaultResource;
	
	
	/**
	 * Client constructor
	 * @param string $baseUrl Url Webservices Repository
	 * @param string $defaultResource Default resource format
	 */
	public function __construct($baseUrl, $defaultResource = 'custom')
	{
		$this->baseUrl = rtrim($baseUrl, '/');
		$this->defaultResource = (is_string($defaultResource)) ? trim(strtolower($defaultResource)) : 'custom';
	}
	
	/**
	* If data are in an array, tranform it in text
	* @param string|array $data The data to use
	* @return string
	*/
	protected function dataFormalize($data)
	{
		if(is_array($data))
		{
			$data = json_encode($data);
		}
		else if($data instanceof \DOMDocument)
		{
			$data = $data->saveXML();
		}
		else if($data instanceof \SimpleXMLElement)
		{
			$data = $data->asXML();
		}
		return $data;
	}
	
	/**
	* Set headers depending on the type
	* @param string $type Headers type
	* @return array Headers matching from the type if exists
	*/
	protected function useHeadersFromType($type=null)
	{
		$defaultHeaders['json'] = array(
			'Accept:application/json',
			'Content-Type:application/json',
		);
		$defaultHeaders['xml'] = array(
			'Accept:application/xml',
			'Content-Type:application/xml',
		);
		$defaultHeaders['custom'] = null;
		if(!isset($type) OR !isset($defaultHeaders[$type]))
		{
			$this->resource = 'custom';
			return $defaultHeaders[$this->defaultResource];
		}
		$this->resource = $type;
		return $defaultHeaders[$type];
	}
	
	/**
	* Init curl headers if not exists
	* @param array|string $headers Headers to use (if string, use the type)
	* @return array
	*/
	protected function recieveHeaders($headers = null)
	{
		$this->resource = $this->defaultResource;
		if(is_string($headers))
		{
			$type = trim(strtolower($headers));
			$headers = $this->useHeadersFromType($type);
		}
		else{
			if(!is_array($headers))
			{
				$headers = $this->useHeadersFromType();
			}
		}
		return $headers;
	}
	
	/**
	* Create request
	* Use HTTP POST to send data
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string|array $headers Request headers
	* @param string $data Data to create
	* @see post
	* @return Gnkw\Http\Rest\Resource
	*/
	public function create($service, $headers = null, $data = null)
	{
		return $this->post($service, $headers, $data);
	}

	/**
	* POST request
	* Use HTTP POST to send data
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string|array $headers Request headers
	* @param string $data Data to create
	* @return Gnkw\Http\Rest\Resource
	*/
	public function post($service, $headers = null, $data = null)
	{
		return $this->customRequest($service, 'POST', $headers, $data);
	}

	/**
	* Update request
	* Use HTTP PUT to update data
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string|array $headers Request headers
	* @param string $data Data to update
	* @see put
	* @return Gnkw\Http\Rest\Resource
	*/
	public function update($service, $headers = null, $data)
	{
		return $this->put($service, $headers, $data);
	}

	/**
	* PUT request
	* Use HTTP PUT to update data
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string|array $headers Request headers
	* @param string $data Data to update
	* @return Gnkw\Http\Rest\Resource
	*/
	public function put($service, $headers = null, $data = null)
	{
		return $this->customRequest($service, 'PUT', $headers, $data);
	}
	
	/**
	* Read request
	* Use HTTP GET to read data
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string|array $headers Request headers
	* @param string Possible $data Data to get
	* @return Gnkw\Http\Rest\Resource
	*/
	public function read($service, $headers = null, $data = null)
	{
		return $this->get($service, $headers, $data);
	}

	/**
	* GET request
	* Use HTTP GET to read data
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string|array $headers Request headers
	* @param string Possible $data Data to get
	* @return Gnkw\Http\Rest\Resource
	*/
	public function get($service, $headers = null, $data = null)
	{
		return $this->customRequest($service, 'GET', $headers, $data);
	}
	
	/**
	* DELETE request
	* Use HTTP DELETE to delete data
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string|array $headers Request headers
	* @return Gnkw\Http\Rest\Resource
	*/
	public function delete($service, $headers = null)
	{
		return $this->customRequest($service, 'DELETE', $headers);;
	}
	
	/**
	* Custom request to call
	* @param string|Gnkw\Http\Uri $service Service to call
	* @param string $method HTTP method
	* @param string|array $headers Request headers
	* @param mixed $data Data used
	* @return Gnkw\Http\Request
	*/
	public function customRequest($service, $method = 'GET', $headers = null, $data = null)
	{
		$serviceUrl = $this->urlService($service);
		$request = new Request($serviceUrl, $method);
		$headers = $this->recieveHeaders($headers);
		if(null !== $headers)
		{
			$request->setHeaders($headers);
		}
		if(null !== $data)
		{
			$data = $this->dataFormalize($data);
			$request->setContent($data);
		}
		$request->setFormat($this->resource);
		return $request;
	}
	
	/**
	* Change the service to a complete URL
	* @param string|Gnkw\Http\Uri $service The service to transform
	* @return string The complete URL
	*/
	protected function urlService($service)
	{
		if($service instanceof Uri)
		{
			$uri = $service->get();
		}
		else
		{
			$uri = $service;
		}
		return $this->baseUrl .'/'. ltrim($uri, '/');
	}
}
