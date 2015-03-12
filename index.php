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

/**
 * Arc API server dispatcher
 */

// set paths
$path_root = __DIR__ . '/';
$path_lib = $path_root . '_app/lib/';

// include class autoloader (optional)
require_once $path_root . '_app/vendor/Arc/autoload.php';

// setup class autoloading
autoload([$path_root . '_app/vendor', $path_lib]);

// include Xap bootstrap (if using database connection(s))
require_once $path_root . '_app/com/xap.bootstrap.php';

// include Xap function (if using xap() function)
// require_once $path_root . '_app/vendor/Xap/xap.php';

try
{
	// init core
	$core = new \Arc\Core(null, 1);

	// register core
	\Arc\Server::registerCore($core);

	// run dispatcher
	\Arc\Server::dispatch(new \Arc\Route($_SERVER['REQUEST_URI']), $path_lib);
}
catch(\Exception $ex)
{
	// set error data
	$error = ['error' => $ex->getMessage()];

	if(\Arc\Server::getCore()) // active core ready
	{
		\Arc\Server::getCore()->writer->prepare($error)->write();
	}
	else // no active core (dispatch pre-core), use json writer
	{
		(new \Arc\Writer\Json())->prepare($error)->write();
	}
}