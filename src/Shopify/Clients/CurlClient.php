<?php

/**
 * cURL Handler. User for communicating
 * with cURL to perform requests
 */
namespace Shopify\Clients;

class CurlClient
{
	protected $request_headers;

	protected $response_headers;

	protected $response_status_code;

	protected $curl_error_msg;

	protected $curl_error_code;

	protected $raw_response;

	protected $curl;

	const CONNECTION_ESTABLISHED = "HTTP/1.0 200 OK\n";

	public function __construct(Curl $curl = null)
	{
		$this->curl = $curl ?: new Curl();
	}

	public function add_request_header($key, $value)
	{
		$this->request_headers[$key] = $value;
	}

	public function get_response_headers()
	{
		return $this->response_headers;
	}

	public function get_status_code()
	{
		return $this->response_status_code;
	}

	public function send($url, $method = 'GET', $params = array())
	{
		$this->open_curl($url, $method, $params);
		$this->attempt_request();

		if($this->curl_error_code) {
			throw new \RuntimeException($this->curl_error_msg);
		}

		list($raw_headers, $raw_body) = $this->extract_response();

		$this->response_headers = self::headers_to_array($raw_headers);

		$this->close_curl();

		return $raw_body;
	}

	public function open_curl($url, $method = 'GET', $params = array())
	{
		$options = array(
			CURLOPT_URL				=> $url,
			CURLOPT_CONNECTTIMEOUT 	=> 10,
			CURLOPT_TIMEOUT 		=> 60,
			CURLOPT_RETURNTRANSFER  => true,
			CURLOPT_HEADER			=> true,
		);

		if($method !== 'GET') {
			$options[CURLOPT_POSTFIELDS] = $params;
		}

		if($method === 'DELETE' || $method == 'PUT') {
			$options[CURLOPT_CUSTOMREQUEST] = $method;
		}

		// Access Token Will need to go into headers for every request
		
		if(count($this->request_headers) > 0) {
			$options[CURLOPT_HTTPHEADER] = $this->create_request_headers();
		}

		$this->curl->init();
		$this->curl->setopt_array($options);
	}

	public function close_curl()
	{
		$this->curl->close();
	}

	public function attempt_request()
	{
		$this->send_request();
		$this->curl_error_msg = $this->curl->error();
		$this->curl_error_code = $this->curl->errno();
		$this->response_status_code = $this->curl->getinfo(CURLINFO_HTTP_CODE);
	}

	public function send_request()
	{
		$this->raw_response = $this->curl->exec();
	}

	public function create_request_headers()
	{
		$return = array();

		foreach($this->request_headers as $key => $value) {
			$return[] = $key . ': ' .$value;
		}

		return $return;
	}

	public function extract_response()
	{
		$header_size = self::get_header_size();

		$raw_headers = mb_substr($this->raw_response, 0, $header_size);
		$raw_body = mb_substr($this->raw_response, $header_size);

		return array(trim($raw_headers), trim($raw_body));
	}

	public static function headers_to_array($raw_headers)
	{
		$headers = array();

		$raw_headers = str_replace("\r\n", "\n", $raw_headers);
	}

	public function get_header_size()
	{
		$header_size = $this->curl->getinfo(CURLINFO_HEADER_SIZE);

		if(preg_match('/Content-Length: (\d+)/', $this->raw_response, $m)) {
			$header_size = mb_strlen($this->raw_response) - $m[1];
		} else if (stripos($this->raw_response, self::CONNECTION_ESTABLISHED) !== false)) {
			$header_size += mb_strlen(self::CONNECTION_ESTABLISHED);
		}

		return $header_size;
	}
}