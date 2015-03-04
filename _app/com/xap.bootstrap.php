<?php
/**
 * Xap bootstrap
 */

// register database connection(s)
\Xap\Engine::exec([[
	// database connection params
	'id' => 1, // set connection ID
	'host' => 'localhost',
	'database' => 'test',
	'user' => 'myuser',
	'password' => 'mypass',
	// 'errors' => false, // display errors (default true)
	// 'debug' => false, // debug messages and errors to log (default true)
	// 'objects' => false, // return objects instead of arrays (default true)
	// 'error_handler' => null, // optional error handler (callable)
	// 'log_handler' => null // optional log handler (callable)
]]);