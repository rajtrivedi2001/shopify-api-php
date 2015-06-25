<?php

namespace Shopify;

use Shopify\Clients\Curl;
use Shopify\Clients\CurlClient;

class FacebookRequest
{
	private $session;

	private $method;

	private $url;

	private $params;

	private $version;

	private $etag;

	private static $client_handler;

	public function get_session()
	{
		return $this->session;
	}

	public function get_path()
	{
		return $this->path;
	}

	public function get_params()
	{
		return $this->params;
	}

	public function get_version()
	{
		return $this->version;
	}

	public function get_etag()
	{
		return $this->etag;
	}

	public static function set_client_handler(CurlClient $handler)
	{
		static::$client_handler = $handler;
	}

	public static function get_client_handler()
	{
		if(static::$client_handler) {
			return static::$client_handler;
		}
		return new CurlClient();
	}

	public function __construct(
		Session $session,
		$path,
		$method = 'GET',
		$params = array()) {
		$this->session = $session;
		$this->path = $path;
		$this->method = $method;
		$this->params = $params;
	}

	public function execute()
	{
		$url = $this->get_path;
		$params = $this->get_params;
		if($this->method === 'GET') {
			$url = self::$add_query_string($url, $params);
			$params = array();
		}
		$curl = self::get_client_handler();
		$curl->add_request_header('X-Shopify-Access-Token', $this->get_session()->get_access_token());

		$result = $curl->send($url, $this->method, $params);

		$headers = $curl->get_response_headers();

		$results = json_decode($result);

		if($results === null) {
			$out = array();
			parse_str($result, $out);
			return new Response($this, $out, $result);
		}
		if(isset($results->error)) {
			throw new RequestException::create(
				$result,
				$results->error,
				$curl->get_status_code());
		}

		return new Response($this, $results, $result);
	}
}