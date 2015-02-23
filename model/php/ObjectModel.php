<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Data Object Class
 *
 * Base object class with data getters and setters
 */

include_once(CONTROLLER_PATH . 'php/SchedulizerDatabaseHandler.php');

abstract class ObjectModel
{
	// Data
	protected $obj_data = array();
	protected $rel_data = array();

	// Relationships
	protected $relationships = array();

	public function __construct()
	{
		// Data Handler object
		$this->dh = SchedulizerDatabaseHandler::getInstance();

		// Set necessary values
		$this->class = get_class($this);
	}

/* Handle REST actions */
	public static function REST($data)
	{
		switch($_SERVER['REQUEST_METHOD'])
		{
			case 'GET':    // fetch
				return self::find($data['id']);
				break;
			case 'POST':   // save, new record
			case 'PUT':    // save, existing record
				return self::update($data);
				break;
			case 'DELETE': // destroy
				return self::delete($data['id']);
				break;
			default:
				// Do nothing for now
				break;
		}
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

		// Get all of the rows in this table
		$rows = $dh->getAllRows(strtolower($class));

		// Attach all relationships
		foreach ($rows as $key => $row)
		{
			// Find this object - includes relationships
			$_obj = self::find($row[strtolower($class) . '_id']);

			// Add the relationships as an object
			foreach ($_obj->rel_data as $rel_name => $rel_ids)
			{
				$rows[$key][$rel_name] = $rel_ids;
			}
		}

		return $rows;
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

		// Get all relationships
		foreach ($_obj->relationships as $rel_obj_name => $rel_table)
		{
			$where = array(
				strtolower($_obj->class) . '_id' => $id
			);

			// Get the results, verify
			$results  = $dh->get($rel_table, $where);
			if (!$results) { continue; }

			$rel_data = array();
			foreach ($results as $row)
			{
				$rel_data[] = $row[strtolower($rel_obj_name) . '_id'];
			}

			// Store the relationship data
			$_obj->rel_data[$rel_obj_name] = $rel_data;
		}

		// Return the object
		return $_obj;
	}

/* Save Row Data */
	public function updateOrInsert($data)
	{
		// Take the data from this object, store it
		return $this->dh->updateOrInsert(strtolower($this->class), $data);
	}

/* Save Relationship Data */
	public function updateRelationship($table_name, $obj_name, $obj_data)
	{
		// Remove all previous relationships
		$this->dh->queryDelete(strtolower($table_name),
			array(
				strtolower($this->class) . '_id' => $this->{strtolower($this->class) . '_id'}
			)
		);

		// Insert all current relationships
		foreach ($obj_data as $obj_id)
		{
			// Prepare the set data
			$data = array(
				strtolower($this->class) . '_id' => $this->{strtolower($this->class) . '_id'},
				strtolower($obj_name) . '_id'    => $obj_id
			);

			// Update this row in the table
			$this->dh->queryInsert(strtolower($table_name), $data);
		}
	}

/* Create or update a record */
	public static function update($data)
	{
		// Get the class and generate the ID name
		$class = get_called_class();
		$id    = strtolower($class) . '_id';

		// Return an instance of this object with the data set
		$_obj = new $class();

		// Find an existing record
		if ($data[$id])
		{
			$_obj = $class::find($data[$id]);	
		}

		// If no object exists, create a new one
		if (!$_obj)
		{
			$_obj = new $class();
		}

		// Make sure we have data
		if (empty($data)) { return false; }

		// Remove the id
		if ($data[$id])  { unset($data[$id]); }
		if ($data['id']) { unset($data['id']); }

		// Set all values
		foreach ($data as $key => $value)
		{
			// Determine if it's a relationship
			if (array_key_exists($key, $_obj->relationships))
			{
				// Relationship - set the relationships
				$_obj->rel_data[$key] = $value;
			}
			// Or an object property
			else
			{
				// Data - set the object data
				$_obj->set($key, $value);
			}
		}

		// Save the object
		$_obj->save();
		
		// Return success
		return true;
	}

/* Delete Row Data */
	public static function delete($id)
	{
		// Get the Database Handler and class
		$dh    = SchedulizerDatabaseHandler::getInstance();
		$class = get_called_class();

		// TEMP - Call an instance of the class
		$_obj = new $class();

		// Delete all relationships
		foreach ($_obj->relationships as $rel_obj_name => $rel_table)
		{
			$dh->queryDelete($rel_table, array(strtolower($class) . '_id' => $id));
		}

		// Delete the row in the database by ID
		return $dh->deleteById(strtolower($class), $id);
	}
}
