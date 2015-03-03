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
 * String writer class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
class String extends \Arc\Writer
{
	/**
	 * Write string pattern
	 *
	 * @var string
	 */
	private $__pattern;

	/**
	 * Init
	 *
	 * @param string $pattern (ex: "{\$k}:{\$v}\n")
	 */
	public function __construct($pattern = "{\$k}:{\$v}\n")
	{
		$this->__pattern = $pattern;
	}

	/**
	 * Flatten array (multidimensional support)
	 *
	 * @param array $data
	 * @param string $str
	 * @return void
	 */
	private function __flattenArr(array &$data, &$str)
	{
		foreach($data as $k => $v)
		{
			if(is_array($v))
			{
				$str .= self::__flatArr($v, $str);
				return;
			}

			$str .= str_replace('{$v}', $v, str_replace('{$k}', $k, $this->__pattern));
		}
	}

	/**
	 * Prepare data for write
	 *
	 * @param array $data
	 * @return \Arc\Writer\String
	 */
	public function &prepare(array &$data)
	{
		$this->__flattenArr($data, $this->_data);

		return $this;
	}
}