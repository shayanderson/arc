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
 * Route class - request URI to route
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
class Route
{
	/**
	 * Request separator
	 */
	const REQUEST_SEP = '/';

	/**
	 * Action name
	 *
	 * @var string
	 */
	public $action;

	/**
	 * Class name
	 *
	 * @var string
	 */
	public $class;

	/**
	 * Core name
	 *
	 * @var string
	 */
	public $core;

	/**
	 * Response class file extension
	 *
	 * @var string
	 */
	public static $ext = '.php';

	/**
	 * Array of callable filters applied to route parts (core, namespace, class) at dispatch
	 *
	 * @var array
	 */
	public static $filters = ['ucfirst'];

	/**
	 * Namespace exists flag
	 *
	 * @var boolean
	 */
	public $is_namespace = false;

	/**
	 * Namespace name (optional)
	 *
	 * @var string
	 */
	public $namespace;

	/**
	 * Route params
	 *
	 * @var array
	 */
	public $params = [];

	/**
	 * Request parameter separator (must be 1 character length)
	 *
	 * @var string
	 */
	public static $request_param_sep = ':';

	/**
	 * Request string
	 *
	 * @var string
	 */
	public $request_str;

	/**
	 * Init
	 *
	 * @param string $request_uri
	 */
	public function __construct($request_uri)
	{
		if(($p = strpos($request_uri, '?')) !== false)
		{
			$request_uri = substr($request_uri, 0, $p); // trim query string
		}

		$request_uri = array_values(array_filter(explode(self::REQUEST_SEP, $request_uri)));

		$is_multicore = Server::isMulticore();
		$k = $is_multicore ? 3 : 2;

		if(count($request_uri) < $k)
		{
			throw new \Exception('Invalid request (missing request parts)');
		}

		// check for namespace
		if(isset($request_uri[$k]) && strpos($request_uri[$k], self::$request_param_sep) === false)
		{
			if($is_multicore)
			{
				$this->core = self::__validatePathString(array_shift($request_uri));
			}
			$this->namespace = self::__validatePathString(array_shift($request_uri));
			$this->class = self::__validatePathString(array_shift($request_uri));
			$this->action = self::__validatePathString(array_shift($request_uri));
			$this->is_namespace = true;
		}
		else // no namespace
		{
			if($is_multicore)
			{
				$this->core = self::__validatePathString(array_shift($request_uri));
			}
			$this->class = self::__validatePathString(array_shift($request_uri));
			$this->action = self::__validatePathString(array_shift($request_uri));
		}

		$this->request_str = ( $this->core !== null ? self::REQUEST_SEP . $this->core : '' )
			. self::REQUEST_SEP
			. ( $this->is_namespace ? $this->namespace . self::REQUEST_SEP : '' )
			. $this->class . self::REQUEST_SEP . $this->action;

		foreach($request_uri as $v)
		{
			if(($p = strpos($v, self::$request_param_sep)) !== false)
			{
				$k = substr($v, 0, $p);

				if(!empty($k))
				{
					$this->params[$k] = urldecode(substr($v, $p + 1));
				}
			}
		}
	}

	/**
	 * Validate path string (and format)
	 *
	 * @param string $str
	 * @return string
	 * @throws \Exception
	 */
	private static function &__validatePathString($str)
	{
		if(strpos($str, self::$request_param_sep) !== false)
		{
			throw new \Exception('Invalid request core, class or action (contains \''
				. self::$request_param_sep . '\' request param separator)');
		}

		$str = strtolower($str);

		return $str;
	}

	/**
	 * Apply callable filters to route parts (core, namespace, class)
	 *
	 * @return void
	 */
	public function applyFilters()
	{
		foreach(self::$filters as $f)
		{
			if($this->core !== null)
			{
				$this->core = $f($this->core);
			}

			if($this->is_namespace)
			{
				$this->namespace = $f($this->namespace);
			}

			$this->class = $f($this->class);
		}
	}
}