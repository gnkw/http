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
 * Uri class
 * @author Anthony <anthony.rey@mailoo.org>
 * @since 08/09/2013
 */
class Uri
{
	/**
	* Base of the URI
	* @var string
	*/
	protected $baseUri;

	/**
	* Params to add to the URI
	* @var array
	*/
	protected $params;

	/**
	* Uri Constructor
	* @param string $baseUri The base URI of the complete URI
	* @param array $params The params to add to the Uri
	*/
	public function __construct($baseUri, $params = null)
	{
		$this->baseUri = $baseUri;
		$this->params = $params;
	}

	/**
	* Add a param to the URI
	* @param string $key
	* @param string|array $value
	*/
	public function addParam($key, $value)
	{
		$this->params[$key] = $value;
	}

	/**
	* Set params of the URI
	* @param array $params
	*/
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	* Get the URI in a string
	* @return string
	*/
	public function get()
	{
		$uri = $this->baseUri;
		$arrayUri = array();
		if(isset($this->params) AND count($this->params) > 0)
		{
			foreach($this->params as $key => $value)
			{
				if(is_array($value))
				{
					# With an array
					foreach($value AS $arrayKey => $arrayValue)
					{
						$composeUri = urlencode($key).'['.urlencode($arrayKey).']';
						if($arrayValue !== null && $arrayValue !== false && $arrayValue !== '')
						{
							$composeUri .= '='.urlencode($arrayValue);
						}
						$arrayUri[] = $composeUri;
					}
				}
				else
				{
					# With a value
					$composeUri = urlencode($key);
					if($value !== null && $value !== false && $value !== '')
					{
						$composeUri .= '='.urlencode($value);
					}
					$arrayUri[] = $composeUri;
				}
			}
		}
		if(count($arrayUri) > 0)
		{
			$uri .= '?' . implode('&', $arrayUri);
		}
		return $uri;
	}

	/**
	 * Get URI as string
	 * @return string
	 */
	public function __toString()
	{
		return $this->get();
	}
}
?>
