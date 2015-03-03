# Arc
#### API Framework for PHP 5.5+
Arc is a simple, lightweight PHP API framework.

Arc offers:
- Multiple core support
- Database ([Xap](https://github.com/shayanderson/xap)) support
- Response formats (JSON, string, serialized array, serialized object)
- Simple logging methods
- Response templates
- Global required parameters
- Error handling

## Install and Test
Install the Arc files into the API location on your server.

Next, test the install and server by pinging the server using the a Web browser and URL:
`http://[your server]/testcore/test/ping`

You should see the response:
```html
{"success":1}
```

## Arc Example
The following is an example of how to setup a simple API server and response using Arc.

First, setup a single core API server setup in `/index.php`
```php
<?php
// set paths
define('PATH_ROOT', __DIR__ . '/');
define('PATH_LIB', PATH_ROOT . '_app/lib/');

// include autoloader
require_once PATH_ROOT . '_app/vendor/Arc/com.php';

// setup class autoloading
autoload([PATH_ROOT . '_app/vendor', PATH_LIB]);

// include Xap bootstrap (if using database connection(s))
require_once PATH_ROOT . '_app/com/xap.bootstrap.php';

try
{
	// init core 'mycore'
	$core = new \Arc\Core('mycore', /* Xap database connection ID */ 1);

	// register core
	\Arc\Server::registerCore($core);

	// run dispatcher
	\Arc\Server::dispatch(new \Arc\Route($_SERVER['REQUEST_URI']));
}
catch(\Exception $ex)
{
	// set error data
	$error = ['error' => $ex->getMessage()];

	// write error response
	(new \Arc\Writer\Json())->prepare($error)->write();
}
```
Next, setup a response for the request `/mycore/item/get/title/id:5`

To do this create the response class file `/_app/lib/Test/Item/Get.php` and add the following code:
```php
<?php
namespace Test\Item;

class Get extends \Arc\Response
{
	public function title()
	{
		// query database for record
		$r = $this->db('items(title)/first WHERE id = ?', [$this->id]);

		if($r) // record found
		{
			$this->data('title', $r->title);
		}
		else // no record found
		{
			$this->data('fail', 'Title not found');
		}
	}
}
```
An example response when the item exists would be:
```html
{"title":"My Test Item"}
```