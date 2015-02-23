<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/*

Schedulizer Database Handler

*/

include_once(LIB_PATH . 'php/DataAccessLayer.php');

class SchedulizerDatabaseHandler extends DataAccessLayer
{
	// Singleton paradigm
	protected static $_instance;
	public static function getInstance()
	{
		if (!(self::$_instance instanceof self))
		{
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	// Construct
	public function __construct()
	{
		// Initialize
		parent::__construct();

		// Create the tables
		$this->createDatabaseSchedulizer();
	}

/**
 * Data Management
 */

/**
 * Create the Database and Tables
 **/
	// Create the database
	private function createDatabaseSchedulizer()
	{
		// Create the Database
		$this->createDatabase('schedulizer');

		// Create the Tables
		$this->createTableEmployee();
		//$this->createTableEmployeeAvailability();
		$this->createTableJob();
		$this->createTableShift();
		//$this->createTableEvent();

		// Relations
		$this->createTableEmployeeToJob();
		$this->createTableEmployeeToShift();
		//$this->createTableEventToShift();
	}

	// Create the employees table
	private function createTableEmployee()
	{
		$table = "employee";
		$columns = array(
			array('name',         'TEXT'),
			array('availability', 'TEXT')
		);
		$this->createTable($table, $columns);
	}

	// Create the availability table
	private function createTableEmployeeAvailability()
	{
		$table = "employee_availability";
		$columns = array(
			array('employee_id',    'INTEGER'),
			array('start_datetime', 'INTEGER'),
			array('end_datetime',   'INTEGER')
		);
		$this->createTable($table, $columns);
	}
	
	// Create the jobs table
	private function createTableJob()
	{
		$table = "job";
		$columns = array(
			array('name', 'TEXT')
		);
		$this->createTable($table, $columns);
	}

	// Create the employee-to-job table
	private function createTableEmployeeToJob()
	{
		$table = "employee_to_job";
		$columns = array(
			array('employee_id', 'INTEGER'),
			array('job_id',      'INTEGER')
		);
		$this->createTable($table, $columns);
	}

	// Create the shifts table
	private function createTableShift()
	{
		// For this project, we will be storing the datetime and timestamp as integers
		$table = "shift";
		$columns = array(
			array('name',   'TEXT'),
			array('job_id', 'INTEGER'),
			array('start',  'INTEGER'),
			array('end',    'INTEGER')
		);
		$this->createTable($table, $columns);
	}

	// Create the employee-to-shift table
	private function createTableEmployeeToShift()
	{
		$table = "employee_to_shift";
		$columns = array(
			array('employee_id', 'INTEGER'),
			array('shift_id',    'INTEGER')
		);
		$this->createTable($table, $columns);
	}

	/*
	// Collections of shifts, events
	private function createTableEvent()
	{
		// For this project, we will be storing the datetime and timestamp as integers
		$table = "event";
		$columns = array(
			array('name',           'TEXT'),
			array('start_datetime', 'INTEGER'),
			array('end_datetime',   'INTEGER')
		);
		$this->createTable($table, $columns);
	}

	// Collections of shifts, events
	private function createTableEventToShift()
	{
		// For this project, we will be storing the datetime and timestamp as integers
		$table = "event_to_shift";
		$columns = array(
			array('event_id', 'INTEGER'),
			array('shift_id', 'INTEGER')
		);
		$this->createTable($table, $columns);
	}
	*/
}
