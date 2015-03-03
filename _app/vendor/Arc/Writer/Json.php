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
namespace Arc\Writer;

/**
 * Json writer class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
class Json extends \Arc\Writer
{
	/**
	 * Prepare data for write
	 *
	 * @param array $data
	 * @return \Arc\Writer\Json
	 * @throws \Exception
	 */
	public function &prepare(array &$data)
	{
		$this->_type = 'application/json';

		if(!function_exists('json_encode'))
		{
			throw new \Exception('Failed to find function \'json_encode\'');
		}

		$this->_data = json_encode($data);

		return $this;
	}
}