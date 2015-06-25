<?php

namespace Shopify\Objects;

use Shopify\Request;
use Shopify\RequestException;
use Shopify\Session;

class AccessToken
{
	protected $access_token;

	public function __construct($access_token)
	{
		$this->access_token = $access_token;
	}

	protected function is_valid()
	{

	}
}
