<?php 

/**
 * Base Files
 **/
require_once('base.php');

/**
 * REST API
 *
 * To come - Use the rest API through backbone.js
 **/ 
switch($_SERVER['REQUEST_METHOD'])
{
	case 'GET':    // fetch
		
		break;
	
	case 'POST':   // save, new record
	case 'PUT':    // save, existing record
		
		// Create the $_PUT global, append array to $_REQUEST
		$_PUT     = json_decode(file_get_contents("php://input"), true);
		$_REQUEST = array_merge($_REQUEST, $_PUT);
		break;

	case 'DELETE': // destroy

		break;
	
	default:

		// Do nothing for now
		break;
}

/**
 * Route the call
 **/
if ($_REQUEST && !empty($_REQUEST))
{
	// Find the action
	if ($_REQUEST['action'])
	{
		// Detach the ID if attached
		if (strpos($_REQUEST['action'], '/') !== false)
		{
			list($_REQUEST['action'], $_REQUEST['id']) = explode('/', $_REQUEST['action']);
		}

		// Save a copy of the GET global
		$local_request = $_REQUEST;

		// Break up the action to class and method
		list($class, $method) = explode('::', $local_request['action']);
		unset($local_request['action']);

		// Call the method, get result
		$result = forward_static_call_array(array($class, $method), array($local_request));

		// Print the results as a JSON string
		print json_encode($result);
	}

	// Our work is done here
	return;
}
