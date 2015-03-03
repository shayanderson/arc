<?php
/**
 * Arc - API Framework for PHP 5.5+
 *
 * @package Arc
 * @version 0.0.1
 * @copyright 2015 Shay Anderson <http://www.shayanderson.com>
 * @license MIT License <http://www.opensource.org/licenses/mit-license.php>
 * @link <https://github.com/shayanderson/arc>
 */
namespace Arc;

/**
 * Arc core engine class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
class Server
{
	/**
	 * Active core
	 *
	 * @var \Arc\Core
	 */
	private static $__core;

	/**
	 * Cores
	 *
	 * @var array
	 */
	private static $__cores = [];

	/**
	 * Dispatch route action
	 *
	 * @param \Arc\Route $route
	 * @return void
	 * @throws \Exception
	 */
	public static function dispatch(Route $route)
	{
		if(!isset(self::$__cores[$route->core])) // invalid core
		{
			throw new \Exception('Invalid request: server core \'' . $route->core
				. '\' does not exist');
		}

		// init core object
		self::$__core = &self::$__cores[$route->core];
		self::$__core->route = &$route;

		// apply callable filters to route parts
		$route->applyFilters();

		$path = PATH_LIB . $route->core . DIRECTORY_SEPARATOR;
		$class = $route->core . '\\';

		if($route->is_namespace)
		{
			$path .= $route->namespace . DIRECTORY_SEPARATOR;
			$class .= $route->namespace . '\\';
		}

		$path .= $route->class . $route::$ext;
		$class .= $route->class;

		if(!file_exists($path))
		{
			throw new \Exception('Invalid request: class file \''
				. str_replace(PATH_ROOT, '', $path) . '\'does not exist');
		}

		if(!class_exists($class))
		{
			throw new \Exception('Invalid request: class \'' . $class . '\' does not exist');
		}

		// GET + POST vars listener
		foreach($_REQUEST as $k => $v)
		{
			// do not override URI params
			if(!isset($route->params[$k]) || !array_key_exists($k, $route->params))
			{
				$route->params[$k] = $v;
			}
		}

		// set class object
		$res = new $class($route->params, self::$__core);

		if(!$res instanceof Response) // must extend base response class
		{
			throw new \Exception('Invalid response: class \'' . $class
				. '\' (must extend Response)');
		}

		// verify request action is not core \Arc\Response method
		foreach((new \ReflectionClass('\Arc\Response'))->getMethods() as $method)
		{
			if($method->name === $route->action)
			{
				throw new \Exception('Invalid request: action \'' . $route->action
					. '\' cannot be core method');
			}
		}

		if(method_exists($res, '__init')) // call init method
		{
			$res->__init();
		}

		if(!method_exists($res, $route->action)) // invalid action
		{
			throw new \Exception('Invalid request: action \'' . $route->action
				. '\' does not exist');
		}

		// required params
		$rp = is_array(self::$__core->required_params) ? self::$__core->required_params : [];

		// get method/action field annotations
		preg_match_all('/@field\s(\w+)/s', new \ReflectionMethod($res, $route->action), $m);

		if(isset($m[1])) // merge field annotation params with required params
		{
			$rp = array_unique(array_merge($rp, $m[1]));
		}

		// verify any field requirements exist
		foreach($rp as $v)
		{
			$v = trim($v);

			if(!property_exists($res, $v))
			{
				throw new \Exception('Invalid request: paramater \'' . $v . '\' is required');
			}
		}

		// call action
		$res->{$route->action}();

		// respond
		$res->respond(self::$__core->writer);
	}

	/**
	 * Active core getter
	 *
	 * @return \Arc\Core (or null when no active core (dispatch pre-core))
	 */
	public static function &getCore()
	{
		return self::$__core;
	}

	/**
	 * Core register
	 *
	 * @param \Arc\Core $core
	 * @return void
	 */
	public static function registerCore(Core $core)
	{
		self::$__cores[$core->id] = &$core;
	}
}