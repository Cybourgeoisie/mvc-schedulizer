<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/*

Data Access Layer

Available Database:
	SQLite
	
Functions
	Public:
		__construct
		connect
		
	Private:
		createDatabase
		createTable
		isBlobType
		isIntegerType
		isRealType
		isTextType
		isValidDefault
		isValidType
		* addOrModifyColumn
		* checkDatabase
		* count
		* get
		* queryDelete
		* queryInsert
		* querySelect
		* queryUpdate
		* whereStatement
*/

class DataAccessLayer
{
	// Static db connection
	public $db = NULL;
	public $query = NULL;
	
	// To categorize and organize all available datatypes
	private $blobDatatypes = array('BLOB');
	private $intDatatypes = array('INTEGER');
	private $realDatatypes = array('REAL');
	private $textDatatypes = array('TEXT');
	private $allDatatypes = array();
	
	// Construct the Data Access Layer
	public function __construct()
	{
		try
		{
			// Connect to the DB
			$this->connect();

			// Set all datatypes
			$this->allDatatypes = array_merge($this->intDatatypes, 
						$this->realDatatypes, $this->textDatatypes, 
						$this->blobDatatypes, array('NULL'));
		}
		catch(Exception $e)
		{
			die("Error!: " . $e->getMessage() . "<br/>");
		}
	}

	// Connect
	// Default DSN is sqlite
	public function connect($dsn = NULL, $user = NULL, $pass = NULL)
	{
		// If there is no DSN provided, use default from config.php
		$dsn = $dsn ?: 'sqlite:' . DATABASE_FILE;

		try
		{
			$this->db = new PDO($dsn, $user, $pass);
		}
		catch(PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
		
		return true;
	}

	public function updateOrInsert($table, $data)
	{
		// Craft the ID column name
		$id_col = $table . '_id';
		$id_val = $data[$id_col];

		// Remove the ID column from the data
		if (array_key_exists($id_col, $data))
		{
			unset($data[$id_col]);
		}

		// For each column and value, clean and quote
		foreach ($data as $column => $value)
		{
			$columns[] = stripslashes($column);
			$values[]  = $this->db->quote(stripslashes($value));
			$set[]     = stripslashes($column) . " = " . $this->db->quote(stripslashes($value));
		}

		// Implode the keys and values
		$columns = implode(', ', $columns);
		$values  = implode(', ', $values); 
		$set     = implode(', ', $set);

		// If the ID is found, update the row
		if (!empty($id_val))
		{
			try
			{
				// Update the row
				$sql = 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $id_col . ' = ' . $id_val . ';';
				print $sql . "<br />";
				return $this->db->query($sql);
			}
			catch (PDOException $e)
			{
				throw new Exception($e->getMessage());
			}
		}
		// Otherwise, insert data
		else
		{
			try
			{
				$sql = 'INSERT INTO ' . $table . ' (' . $columns . ') VALUES (' . $values . ');';
				print $sql . "<br />";
				return $this->db->query($sql);
			}
			catch (PDOException $e)
			{
				throw new Exception($e->getMessage());
			}
		}
	}

	public function getById($table, $id)
	{
		return $this->get($table, array($table . '_id' => $id));
	}

	public function getAllRows($table)
	{
		return $this->get($table);
	}
	
// Private functions
	// Create the database
	protected function createDatabase($database)
	{
		try
		{
			if (!is_string($database))
			{
				throw new Exception("DataAccessLayer::createDatabase - Bad database name");
			}

			// Create the database
			$sql = "CREATE DATABASE " . $database;
			$this->query = $this->db->query($sql);

			return true;
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	// Add a new table
	// $table = Table name, string.
	// $columns = array(array('name', 'value'[, 'default']), ...)
	// Id column is automatically created
	protected function createTable($table, $columns)
	{
		try
		{
			// Check for valid passed values
			if (!is_string($table))
			{
				throw new Exception("DataAccessLayer::createTable - Table name is not valid");
			}
			elseif (!is_array($columns) || empty($columns))
			{
				throw new Exception("DataAccessLayer::createTable - Passed columns data must be an array of column arrays");
			}
			
			// Check and prepare all of the columns
			$data = array();
			foreach ($columns as $key => $column)
			{
				if (!is_array($column))
				{
					throw new Exception("DataAccessLayer::createTable - Columns must be an array of form: ('name','value'[,'default'])");
				}
				elseif (!is_string($column[0]))
				{
					throw new Exception("DataAccessLayer::createTable - Column name is not valid for item #" . $key);
			 	}
			 	elseif ($column[0] == ($table . '_id'))
			 	{
					throw new Exception("DataAccessLayer::createTable - Column name " . $table . '_id' . " already provided as primary key");
			 	}
			 	elseif (!$this->isValidType($column[1]))
			 	{
					throw new Exception("DataAccessLayer::createTable - Column type is not valid for item #" . $key);
				}
				
				// Prepare the data
				$name = $this->db->quote($column[0]);
				$type = $column[1];
				
				// Add a default value if provided
				if ($this->isValidDefault($column[1], $column[2]))
				{
					$default = ' DEFAULT ' . $this->db->quote($column[2]);
				}
				elseif (!is_null($default))
				{
					throw new Exception("DataAccessLayer::create - Default value does not match the datatype");
				}
				
				// Column data: assemble!
				$data[] = $name . " " . $type . $default;
			}
			
			// Alter table statement
			$columns = implode(', ', $data);
			$sql = "CREATE TABLE " . $table . " ('" . $table . "_id' INTEGER PRIMARY KEY, " . $columns . ");";
			$this->db->query($sql);
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	// Check if the column type is among any of the blob types
	private function isBlobType($type)
	{
		return in_array($type, $this->blobDatatypes, TRUE);
	}
	
	// Check if the column type is among any of the integer types
	private function isIntegerType($type)
	{
		return in_array($type, $this->intDatatypes, TRUE);
	}
	
	// Check if the column type is among any of the real types
	private function isRealType($type)
	{
		return in_array($type, $this->realDatatypes, TRUE);
	}
	
	// Check if the column type is among any of the text types
	private function isTextType($type)
	{
		return in_array($type, $this->textDatatypes, TRUE);
	}
	
	// Check if the default value is valid for a given type
	private function isValidDefault($type, $default)
	{
		return (is_int($default) && $this->isIntegerType($type)) ||
				(is_float($default) && $this->isRealType($type)) ||
				(is_string($default) && $this->isTextType($type)) ||
				(is_string($default) && $this->isBlobType($type));
	}
	
	// Check if the column type is valid
	private function isValidType($type)
	{
		return in_array($type, $this->allDatatypes, TRUE);
	}
	
	// Count rows of a certain delimiter
	private function count($table, $where)
	{
		try
		{
			if (!is_string($table))
			{
				throw new Exception("DataAccessLayer::count - Incorrect table name, must be string");
			}
			
			$this->query = $this->querySelect("COUNT(ID)", $table, $where);
			if (!empty($this->query))
			{
				$count = $this->query->fetchAll();
				return $count[0][0];
			}
			return NULL;
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	// get
	// A general get function
	private function get($table, $where = NULL, $order_by = NULL, $limit = NULL)
	{
		try
		{
			if (!is_string($table))
			{
				throw new Exception("DataAccessLayer::get - Incorrect table name, must be string");
			}
			
			$this->query = $this->querySelect("*", $table, $where, $order_by, $limit);
			if (!empty($this->query))
			{
				$this->cats = $this->query->fetchAll(PDO::FETCH_ASSOC);
				return $this->cats;
			}
			return NULL;
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	// Generate the limit statement
	private function limitStatement($limit)
	{
		$limit_stmt = NULL;
		if (is_array($limit))
		{
			$limit_stmt = "LIMIT " . $limit[0];
			if (!empty($limit[1]))
			{
				$limit_stmt .= ", " . $limit[1];
			}
		}
		return $limit_stmt;
	}

	// queryDelete
	// Delete records from the database
	private function queryDelete($table, $where)
	{
		// The delete statement must have some discrimatory values
		if (!is_string($table) || !is_array($where))
		{
			throw new Exception("DataAccessLayer::queryDelete - Table or Where statement not provided");
		}
		
		// Organize the where statement
		$where = $this->whereStatement($where);
		
		// Delete
		try
		{
			$sql = "DELETE FROM " . $table . " " . $where . ";";
			$this->db->query($sql);
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
		
		return true;
	}

	// queryInsert
	// Make an insert query, return query success information
	private function queryInsert($table = "Posts", $insert = NULL)
	{
		// Sanitize the table name
		$table = $this->db->quote($table);
		
		// Get the keys and values
		$keys = array();
		$values = array();
		foreach ($insert as $key => $value)
		{
			$keys[] = $this->db->quote(stripslashes($key));
			$values[] = $this->db->quote(stripslashes($value));
		}
		$keys = implode(", ", $keys);
		$values = implode(", ", $values);
		
		// Run the query
		try
		{
			$sql = "INSERT INTO " . $table . " (" . $keys . ") VALUES (" . $values . ");";
			return $this->db->query($sql);
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	// querySelect
	// Make a select query, return query information
	private function querySelect($select = "*", $table = "Posts", $where = NULL, $order_by = NULL, $limit = NULL)
	{
		// Condense the table keys
		if (is_array($select))
		{
			$select = implode(", ", $select);
		}
		
		// Organize the where statement
		$where = $this->whereStatement($where);
				
		// Construct the order by statement
		if (is_string($order_by))
		{
			$order_by = "ORDER BY " . $order_by;
		}
		elseif (is_array($order_by))
		{
			$order_by = "ORDER BY " . $order_by[0] . " " . $order_by[1];
		}
		
		// Organize the limit statement
		$limit = $this->limitStatement($limit);
		
		// Run the query
		try
		{
			$sql = "SELECT " . $select . " FROM " . $table . " " . $where . " " . $order_by . " " . $limit . ";";
			return $this->db->query($sql);
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}
	
	// queryUpdate
	// Make an update query, return query success information
	private function queryUpdate($table = "Posts", $update = NULL, $where = NULL)
	{
		// Sanitize the table name
		$table = $this->db->quote($table);
		
		// Get the keys and values
		$set = array();
		foreach ($update as $key => $value)
		{
			$set[] = $this->db->quote(stripslashes($key)) . " = " . $this->db->quote(stripslashes($value));
		}
		$set = implode(", ", $set);
		
		// Organize the where statement
		$where = $this->whereStatement($where);
		
		// Run the query
		try
		{
			$sql = "UPDATE " . $table . " SET " . $set . " " . $where . ";";
			return $this->db->query($sql);
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	// Generate the where statement
	private function whereStatement($where)
	{
		$where_stmt = NULL;
		if (is_array($where))
		{
			$where_stmt = "WHERE ";
			foreach ($where as $key => $value)
			{
				if (is_array($value))
				{
					// Quote for security
					foreach ($value as $k => $v)
					{
						$value[$k] = $this->db->quote($v);
					}
					
					// Construct statement
					$where_stmt .= $key . " IN (";
					$where_stmt .= implode(", ", $value);
					$where_stmt .= ")";
				}
				elseif(!empty($value))
				{
					$equals = " = ";
					// Test if a string value
					if (!is_numeric($value))
					{
						$equals = " == ";
					}
					$where_stmt .= $key . $equals . $this->db->quote($value);
				}
			}
		}
		return $where_stmt;
	}

		// Add or modify a column. May want to extend to cover delete, or just recreate as alterTable()
	private function addOrModifyColumn($operation, $table, $column, $type, $default = NULL)
	{
		try
		{
			if (!in_array($operation, array('ADD', 'MODIFY'), TRUE))
			{
				throw new Exception("DataAccessLayer::addOrModifyColumn - Operation is not valid, only ADD or MODIFY allowed");
			}
			else
			{
				// Spacing for the SQL statement
				$operation = ' ' . $operation . ' ';
			}
			
			// Check for valid passed values
			if (!is_string($table))
			{
				throw new Exception("DataAccessLayer::addOrModifyColumn - Table name is not valid");
			}
			elseif (!is_string($column))
			{
				throw new Exception("DataAccessLayer::addOrModifyColumn - Column name is not valid");
			}
			elseif (!$this->isValidType($type))
			{
				throw new Exception("DataAccessLayer::addOrModifyColumn - Column type is not valid");
			}
			
			// Add a default value if provided
			if ($this->isValidDefault($type, $default))
			{
				$default = ' DEFAULT ' . $this->db->quote($default);
			}
			elseif (!is_null($default))
			{
				throw new Exception("DataAccessLayer::addOrModifyColumn - Default value does not match the datatype");
			}
			
			// Alter table statement
			$sql = "ALTER TABLE " . $table . $operation . $column . " " . $type . $default . ";";
			$this->db->query($sql);
		}
		catch (PDOException $e)
		{
			throw new Exception($e->getMessage());
		}
	}

	// Check if database exists
	private function checkDatabase()
	{
		// Make sure that the databases are installed
		if ($this->query = $this->db->query("SELECT * FROM Posts ORDER BY Datetime DESC"))
		{
			try
			{
				// Get the Posts
				$this->posts = $this->query->fetchAll();
			}
			catch(PDOException $e)
			{
				throw new Exception($e->getMessage());
			}
		// Create the database if non-existent
		}
		else
		{
			try
			{
				$this->createDatabase();
			}
			catch (PDOException $e)
			{
				throw new Exception($e->getMessage());
			}
		}
	}
}
