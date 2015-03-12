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
 * Server core class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
class Core
{
	/**
	 * Templates
	 *
	 * @var array
	 */
	private $__templates = [];

	/**
	 * Hook file path (pre-action)
	 *
	 * @var string
	 */
	public $hook_path;

	/**
	 * Core ID
	 *
	 * @var string
	 */
	public $id;

	/**
	 * Log handler
	 *
	 * @var \callable
	 */
	public $log_handler;

	/**
	 * Required params template
	 *
	 * @var array
	 */
	public $required_params;

	/**
	 * Request route object
	 *
	 * @var \Arc\Route
	 */
	public $route;

	/**
	 * Throttle response by microseconds (ex: 0 for no throttle, 250000 for quarter of a second)
	 *
	 * @var int
	 */
	public $throttle_microseconds = 0;

	/**
	 * Writer object
	 *
	 * @var \Arc\Writer
	 */
	public $writer;

	/**
	 * Xap connection ID
	 *
	 * @var int
	 */
	public $xap_id;

	/**
	 * Init
	 *
	 * @param mixed $name (null|string)
	 * @param int $xap_id
	 */
	public function __construct($name = null, $xap_id = null)
	{
		$this->id = $name !== null ? strtolower($name) : $name;
		$this->xap_id = $xap_id !== null ? $xap_id : $this->id;
		$this->writer = new Writer\Json; // default writer
	}

	/**
	 * Template data getter
	 *
	 * @param string $name
	 * @return mixed (array|callable)
	 * @throws \Exception
	 */
	public function &getTemplate($name)
	{
		if(!isset($this->__templates[$name]))
		{
			throw new \Exception('Core template \'' . $name . '\' does not exist');
		}

		return $this->__templates[$name];
	}

	/**
	 * Template data setter
	 *
	 * @param string $name
	 * @param mixed $data (array, callable, null)
	 * @return void
	 */
	public function setTemplate($name, $data = null)
	{
		if(is_array($name))
		{
			foreach($name as $k => $v)
			{
				$this->setTemplate($k, $v);
			}
			return;
		}

		$this->__templates[$name] = $data;
	}
}