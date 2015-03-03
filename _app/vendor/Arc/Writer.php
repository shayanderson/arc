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
 * Abstract Writer class
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */
abstract class Writer
{
	/**
	 * Data to write
	 *
	 * @var string
	 */
	protected $_data;

	/**
	 * Write data content type
	 *
	 * @var string
	 */
	protected $_type;

	/**
	 * Prepare data for write
	 *
	 * @param array $data
	 * @return \Arc\Writer
	 */
	abstract public function &prepare(array &$data);

	/**
	 * Write data
	 *
	 * @return void
	 */
	final public function write()
	{
		header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // no cache

		if(!empty($this->_type))
		{
			header('Content-Type: ' . $this->_type);
		}

		echo $this->_data;
		exit;
	}
}