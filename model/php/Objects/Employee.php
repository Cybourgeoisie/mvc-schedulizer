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
	protected $job = array();
	protected $availability;

	// Data
	protected $obj_data = array(
		'employee_id',
		'name',
		'availability'
	);

	protected $rel_data = array(
		'Job' => array()
	);

	// Relationships
	protected $relationships = array(
		'Job' => 'employee_to_job'
	);

	// Save data
	public function save()
	{
		// Save this employee
		$data = array(
			'employee_id'  => $this->employee_id,
			'name'         => $this->name,
			'availability' => $this->availability
		);

		// Save the relationships
		foreach ($this->relationships as $obj_name => $rel_table)
		{
			// Get the relationship data, store each row
			if (isset($this->rel_data[$obj_name]) && is_array($this->rel_data[$obj_name]))
			{
				$this->updateRelationship($rel_table, $obj_name, $this->rel_data[$obj_name]);
			}
		}

		return $this->updateOrInsert($data);
	}

	/**
	 * One-use function to copy the current availability for one week to the next eight weeks or so.
	 */
	public static function tweezer()
	{
		return "It's gonna be cold, cold, cold, cold, cold.";

		// Get the date of the Sunday at midnight
		$orig_time = mktime(0, 0, 0, 10, 27, 2013) * 1000;
		$week_time = mktime(0, 0, 0, 11,  3, 2013) * 1000;
		$week_diff = $week_time - $orig_time;

		// Add an hour to accomodate for leap time..
		$leap_hour = 60 * 60 * 1000;

		// Get all employees, determine how many seconds pass between the sunday at midnight and the availability times
		$employees = Employee::getAll();

		foreach ($employees as $employee)
		{
			// Get the object
			$employee_obj = Employee::find($employee['employee_id']);

			// Get availability, add time
			$availability     = json_decode($employee['availability'], true);
			$new_availability = array();
			foreach ($availability as $avail)
			{
				if ($avail['start'] < $orig_time) { continue; }

				$new_availability[] = array(
					'start' => $avail['start'] + $week_diff + $leap_hour,
					'end'   => $avail['end']   + $week_diff + $leap_hour
				);
			}

			// Save the new availability
			$employee_obj->availability = json_encode(array_merge($availability, $new_availability));
			$employee_obj->save();
		}
	}
}
