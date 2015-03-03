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
 * Arc common functions
 *
 * @author Shay Anderson 02.15 <http://www.shayanderson.com/contact>
 */

/**
 * Class autoloading
 *
 * @param array $autoload_paths
 * @return void
 */
function autoload(array $autoload_paths)
{
	set_include_path(get_include_path() . PATH_SEPARATOR . implode(PATH_SEPARATOR, $autoload_paths));

	function __autoload($class)
	{
		require_once str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
	}
}