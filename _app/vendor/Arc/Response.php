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
 * Abstract Response class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
abstract class Response
{
	/**
	 * Request core
	 *
	 * @var \Arc\Core
	 */
	private $__core;

	/**
	 * Response data
	 *
	 * @var array
	 */
	private $__response = [];

	/**
	 * Init
	 *
	 * @param array $params
	 * @param \Arc\Core $core
	 */
	final public function __construct(array &$params, Core &$core)
	{
		foreach($params as $k => $v)
		{
			$this->{$k} = $v;
		}

		$this->__core = &$core;
	}

	/**
	 * Response data setter
	 *
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	final public function data($key, $value = null)
	{
		if(is_array($key))
		{
			foreach($key as $k => $v)
			{
				$this->data($k, $v);
			}
			return;
		}

		$this->__response[$key] = $value;
	}

	/**
	 * Core database access method
	 *
	 * @param string $cmd
	 * @param mixed $_
	 * @return mixed
	 * @throws \Exception
	 */
	final public function db($cmd, $_ = null)
	{
		$args = func_get_args();
		$args[0] = '[' . $this->__core->xap_id . ']' . $args[0]; // add xap connection ID
		return \Xap\Engine::exec($args);
	}

	/**
	 * Request core getter
	 *
	 * @return \Arc\Core
	 */
	final public function &getCore()
	{
		return $this->__core;
	}

	/**
	 * Core logging method (overridable)
	 *
	 * @overridable
	 * @return void
	 */
	public function log()
	{
		if(is_callable($this->__core->log_handler)) // if valid log handler
		{
			call_user_func_array($this->__core->log_handler, array_merge(func_get_args(),
				[$this->__core->route->request_str, $_SERVER['REMOTE_ADDR']]));
		}
	}

	/**
	 * Write response data
	 *
	 * @return void
	 */
	final public function respond()
	{
		$this->__core->writer->prepare($this->__response)->write();
	}

	/**
	 * Set response data using core template
	 *
	 * @param string $name
	 * @param mixed $_
	 * @return void
	 */
	final public function template($name, $_ = null)
	{
		$tpl = &$this->__core->getTemplate($name);

		if(is_callable($tpl)) // callable template
		{
			$this->data(call_user_func_array($tpl, array_slice(func_get_args(), 1)));
		}
		else // array template
		{
			$this->data($tpl);
		}
	}
}