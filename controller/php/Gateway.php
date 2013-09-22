<?php 

/**
 * Base Files
 **/
require_once('base.php');

/**
 * Route the call
 **/
if ($_GET && !empty($_GET))
{
	// Find the action
	if ($_GET['action'])
	{
		// Save a copy of the GET global
		$local_get = $_GET;

		// Break up the action to class and method
		list($class, $method) = explode('::', $local_get['action']);
		unset($local_get['action']);

		// Call the method, get result
		$result = forward_static_call_array(array($class, $method), $local_get);

		// Print the results as a JSON string
		print json_encode($result);
	}

	// Our work is done here
	return;
}
