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
namespace Arc\Writer;

/**
 * Serialized array writer class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
class SerializedArray extends \Arc\Writer
{
	/**
	 * Prepare data for write
	 *
	 * @param array $data
	 * @return \Arc\Writer\SerializedArray
	 */
	public function &prepare(array &$data)
	{
		$this->_data = serialize($data);

		return $this;
	}
}