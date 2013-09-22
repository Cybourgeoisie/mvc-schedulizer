<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Employee
 */

include_once(MODEL_PATH . 'php/ObjectModel.php');

class Employee extends ObjectModel
{
	// Properties
	protected $employee_id;
	public    $name;

	// Jobs, Availabilty, Shifts
	public $availabilty = array();
	public $shifts      = array();
	public $jobs        = array();

	// Add Shift
	public function addShift($shift_obj)
	{
		// Add the shift
		$this->shifts[] = $shift_obj;
	}

	// Save data
	public function save()
	{
		$data = array(
			'employee_id' => $this->employee_id,
			'name'        => $this->name
		);

		$this->updateOrInsert($data);
	}
}
