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
 * Serialized object writer class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
class SerializedObject extends \Arc\Writer
{
	/**
	 * Array to object (multidimensional array support)
	 *
	 * @param array $arr
	 * @return \stdClass
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
	 * Prepare data for write
	 *
	 * @param array $data
	 * @return \Arc\Writer\SerializedObject
	 */
	public function &prepare(array &$data)
	{
		$this->_data = serialize(self::__arrToObj($data));

		return $this;
	}
}