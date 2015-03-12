<?php
/**
 * Arc - API Framework for PHP 5.5.0+
 *
 * @package Arc
 * @version 0.0.1
 * @copyright 2015 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <http://www.opensource.org/licenses/mit-license.php>
 * @link <https://github.com/shayanderson/arc>
 */
namespace Arc;

/**
 * Request class
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */
class Request
{
	/**
	 * Response formats
	 */
	const FORMAT_JSON = 1;
	const FORMAT_SERIALIZED_ARRAY = 2;
	const FORMAT_SERIALIZED_OBJECT = 3;
	const FORMAT_STRING = 4;

	/**
	 * HTTP request methods
	 */
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';

	/**
	 * Request separator
	 */
	const REQUEST_SEP = '/';

	/**
	 * Request params
	 *
	 * @var array
	 */
	private $__params = [];

	/**
	 * Host with scheme (ex: 'http://127.0.0.1/api/')
	 *
	 * @var string
	 */
	public $host;

	/**
	 * Host port
	 *
	 * @var int
	 */
	public $host_port = 80;

	/**
	 * Host response format (default: \Arc\Request::FORMAT_JSON)
	 *
	 * @var int
	 */
	public $host_response_format = self::FORMAT_JSON; // host format

	/**
	 * Host connection timeout (seconds)
	 *
	 * @var int
	 */
	public $host_timeout = 5;

	/**
	 * Error has occurred flag
	 *
	 * @var boolean
	 */
	public $is_error = false;
	public $method = self::METHOD_GET;
	public $request;
	public $request_str;
	public $response_code;
	public $response_str;

	/**
	 * Init (overridable)
	 *
	 * @overridable
	 * @param mixed $request (null|string, optional, ex: '/test/get')
	 */
	public function __construct($request = null)
	{
		if($request !== null)
		{
			$this->request = $request;
		}
	}

	/**
	 * Array to object (multidimensional array support)
	 *
	 * @param array $arr
	 * @return object
	 */
	private static function __arrToObj(&$arr)
	{
		if(is_array($arr))
		{
			return (object)array_map(__METHOD__, $arr);
		}
		else
		{
			return $arr;
		}
	}

	/**
	 * Error message to response
	 *
	 * @param string $message
	 * @return \stdClass
	 */
	private function __error($message)
	{
		$this->is_error = true;
		$message = ['error' => $message];
		return self::__formatResponse($message, null);
	}

	/**
	 * Format response to object
	 *
	 * @param string $response_str
	 * @param int $format_type
	 * @return \stdClass
	 */
	private static function __formatResponse(&$response_str, $format_type)
	{
		switch($format_type)
		{
			case self::FORMAT_JSON:
				return json_decode($response_str);
				break;

			case self::FORMAT_SERIALIZED_ARRAY:
				$response_str = unserialize($response_str);
				return self::__arrToObj($response_str);
				break;

			case self::FORMAT_SERIALIZED_OBJECT:
				return unserialize($response_str);
				break;

			case self::FORMAT_STRING:
			default:
				return (object)$response_str;
				break;
		}
	}

	/**
	 * Parse host HTTP response
	 *
	 * @param string $response_str
	 * @return array
	 */
	private static function &__parseResponse(&$response_str)
	{
		$response = [];
		$response['str'] = null; // init
		$response['code'] = null;

		if(strlen($response_str) > 0)
		{
			if(strpos($response_str, "\r\n\r\n") !== false)
			{
				$res = explode("\r\n\r\n", $response_str);

				preg_match('/HTTP\/\d\.\d\s(\d{3})/i', $res[0], $m);

				if(isset($m[1])) // set response code
				{
					$response['code'] = (int)$m[1];
				}

				if($response['code'] === 404) // check for 404 error
				{
					$response['error'] = 'Host path not found';
					return $response;
				}

				$response['str'] = $res[1];
			}
			else
			{
				$response['error'] = 'Host response invalid';
			}
		}
		else
		{
			$response['error'] = 'Host response is empty';
		}

		return $response;
	}

	/**
	 * Execute request and get parsed response
	 *
	 * @param mixed $request (null|string, optional, ex: '/test/get')
	 * @return \stdClass (response data, or error message on error)
	 */
	final public function fetch($request = null)
	{
		if($request !== null)
		{
			$this->request = $request;
		}

		if(filter_var($this->host, FILTER_VALIDATE_URL) === false) // invalid URL
		{
			return $this->__error('Invalid host URL (must contain scheme like http://)');
		}

		$is_post = strcasecmp($this->method, self::METHOD_POST) === 0;
		$params_str = http_build_query($this->__params);

		// set URL (scheme, host, path)
		$url = (object)parse_url(rtrim($this->host, self::REQUEST_SEP) . self::REQUEST_SEP
			. ltrim($this->request, self::REQUEST_SEP));

		if(strcasecmp($url->scheme, 'https') === 0)
		{
			$url->scheme = 'ssl://'; // change to ssl for connection
		}
		else
		{
			$url->scheme = ''; // no scheme for HTTP
		}

		if(!isset($url->path))
		{
			$url->path = '/';
		}

		$this->request_str = strtoupper($this->method) . ' ' . $url->path;

		if(!$is_post) // get params
		{
			$this->request_str .= '?' . $params_str;
		}

		$this->request_str .= " HTTP/1.1\r\n"
			. 'Host: ' . $url->host . "\r\n";

		if(!$is_post) // get
		{
			$this->request_str .= "Content-Type: text/html\r\n";
		}
		else // post
		{
			$this->request_str .= "Content-Type: application/x-www-form-urlencoded\r\n"
				. 'Content-length: ' . strlen($params_str) . "\r\n";
		}

		$this->request_str .= "Connection: close\r\n\r\n";

		if($is_post)
		{
			$this->request_str .= $params_str;
		}

		$sock = @fsockopen($url->scheme . $url->host, (int)$this->host_port, $errno, $err,
			(int)$this->host_timeout);

		if(!$sock) // invalid connection
		{
			return $this->__error('Host connection failed: \'' . $err . '\' (' . $errno . ')');
		}

		fwrite($sock, $this->request_str); // write request

		// set response
		while(!feof($sock))
		{
			$this->response_str .= fgets($sock, 1024);
		}

		fclose($sock);

		$response = &self::__parseResponse($this->response_str);

		if(isset($response['code'])) // set response code
		{
			$this->response_code = $response['code'];
		}

		if(isset($response['error']))
		{
			return $this->__error($response['error']);
		}

		$response = self::__formatResponse($response['str'], $this->host_response_format);

		if(isset($response->error)) // check for error
		{
			$this->is_error = true;
		}

		return $response;
	}

	/**
	 * Request parameter key/value setter
	 *
	 * @param mixed $id (array|string, array ex: ['key' => 'value', ...]
	 * @param mixed $value
	 * @return void
	 */
	final public function param($id, $value)
	{
		if(is_array($id))
		{
			foreach($id as $k => $v)
			{
				$this->param($k, $v);
			}
			return;
		}

		$this->__params[$id] = $value;
	}
}