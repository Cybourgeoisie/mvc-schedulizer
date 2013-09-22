<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Data Object Class
 *
 * Base object class with data getters and setters
 */

include_once(CONTROLLER_PATH . 'php/SchedulizerDatabaseHandler.php');

abstract class ObjectModel
{
	public function __construct()
	{
		// Data Handler object
		$this->dh = SchedulizerDatabaseHandler::getInstance();

		// Set necessary values
		$this->class = get_class($this);
	}

/* Get Data */
	public function get($field)
	{
		return $this->{$field};
	}

/* Set Data */
	public function set($field, $data)
	{
		// Set the field to a certain value
		$this->{$field} = $data;
	}

/* Return all of this class */
	public static function getAll()
	{
		// Get the Database Handler and class
		$dh    = SchedulizerDatabaseHandler::getInstance();
		$class = get_called_class();

		// Return all of the rows in this table
		return $dh->getAllRows(strtolower($class));
	}

/* Find Row, Return Object */
	public static function find($id)
	{
		// Get the Database Handler and class
		$dh    = SchedulizerDatabaseHandler::getInstance();
		$class = get_called_class();

		// Find the row in the database by ID
		$data = $dh->getById(strtolower($class), $id);

		// Make sure that we have data
		if (empty($data) || empty($data[0]))
		{
			return null;
		}

		// Return an instance of this object with the data set
		$_obj = new $class();

		// Populate the object with all of the values
		foreach ($data[0] as $key => $value)
		{
			// If the key is not numeric, set the value
			$_obj->set($key, $value);
		}

		// Return the object
		return $_obj;
	}

/* Save Row Data */
	public function updateOrInsert($data)
	{
		// Take the data from this object, store it
		$this->dh->updateOrInsert(strtolower($this->class), $data);
	}
}
