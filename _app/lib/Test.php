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

/**
 * Test response class with ping action
 *
 * @author Shay Anderson 03.15 <http://www.shayanderson.com/contact>
 */
class Test extends \Arc\Response
{
	/**
	 * Ping action
	 */
	public function ping()
	{
		$this->data('success', 1);
	}
}