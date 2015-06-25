<?php

namespace Shopify;

use Shopify/Objects/AccessToken;

class Session
{
	private static $app_id;

	private static $app_secret;

	private $access_token;

	public function __construct($access_token)
	{
		$this->access_token = $access_token instanceof AccessToken ? $access_token = new AccessToken($aceess_token);  
	}
}
