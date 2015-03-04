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
	 * Multicore setup flag
	 *
	 * @var boolean
	 */
	private static $__is_multicore = true;

	/**
	 * Dispatch route action
	 *
	 * @param \Arc\Route $route
	 * @return void
	 * @throws \Exception
	 */
	public static function dispatch(Route $route)
	{
		if(self::$__is_multicore) // multicore
		{
			if(!isset(self::$__cores[$route->core])) // invalid core
			{
				throw new \Exception('Invalid request: server core \'' . $route->core
					. '\' does not exist');
			}

			// init core object from cores
			self::$__core = &self::$__cores[$route->core];
			$route->core = self::$__core->id; // set because of core aliases
		}

		// set core route
		self::$__core->route = &$route;

		// apply callable filters to route parts
		$route->applyFilters();

		$path = PATH_LIB . ( $route->core !== null ? $route->core . DIRECTORY_SEPARATOR : '' );
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
		$response = new $class($route->params, self::$__core);

		if(!$response instanceof Response) // must extend base response class
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
			unset($method);
		}

		if(!empty(self::$__core->hook_path)) // load pre-action hook
		{
			require_once self::$__core->hook_path;
		}

		if(method_exists($response, '__init')) // call init method
		{
			$response->__init();
		}

		if(!method_exists($response, $route->action)) // invalid action
		{
			throw new \Exception('Invalid request: action \'' . $route->action
				. '\' does not exist');
		}

		// required params
		$rp = is_array(self::$__core->required_params) ? self::$__core->required_params : [];

		// get method/action field annotations
		preg_match_all('/@field\s(\w+)/s', new \ReflectionMethod($response, $route->action), $m);

		if(isset($m[1])) // merge field annotation params with required params
		{
			$rp = array_unique(array_merge($rp, $m[1]));
		}

		// verify any field requirements exist
		foreach($rp as $v)
		{
			$v = trim($v);

			if(!property_exists($response, $v))
			{
				throw new \Exception('Invalid request: paramater \'' . $v . '\' is required');
			}
		}

		// call action
		$response->{$route->action}();

		if((int)self::$__core->throttle_microseconds > 0) // throttle response
		{
			usleep((int)self::$__core->throttle_microseconds);
		}

		// respond
		$response->respond();
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
	 * Multicore flag getter
	 *
	 * @return boolean
	 */
	public static function isMulticore()
	{
		return self::$__is_multicore;
	}

	/**
	 * Register core
	 *
	 * @param \Arc\Core $core
	 * @return void
	 */
	public static function registerCore(Core $core)
	{
		if($core->id !== null) // multicore setup
		{
			self::$__cores[$core->id] = &$core;
		}
		else // single core
		{
			self::$__core = &$core;
			self::$__is_multicore = false;
		}
	}

	/**
	 * Register core alias - used for same response classes, different writer/templates/etc
	 *
	 * @param \Arc\Core $core
	 * @param string $alias
	 * @return \Arc\Core
	 */
	public static function registerCoreAlias(Core &$core, $alias)
	{
		self::$__cores[$alias] = clone $core;
		return self::$__cores[$alias];
	}
}