<?

require 'URL.php';

// https://jsonplaceholder.typicode.com/posts

$http = new URL('https://jsonplaceholder.typicode.com/');

$result = $http->get('posts/100'/*, [
	'title' => 'foo',
	'body' => 'bar',
	'userId' => 1,
	'id' => 101,
]*/);

echo "<pre>";
print_r($result->objects());
echo "</pre>";
