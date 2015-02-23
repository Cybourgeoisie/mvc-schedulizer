<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Availability
 */

include_once(MODEL_PATH . 'php/ObjectModel.php');

class Employee_Availability extends ObjectModel
{
	// Properties
	protected $employee_availability_id;
	public    $name;

	// Data
	protected $obj_data = array(
		'employee_availability_id' => null,
		'start_datetime'           => null,
		'end_datetime'             => null
	);

	protected $rel_data = array(
		'Employee' => array()
	);

	// Relationships
	protected $relationships = array(
		'Employee' => 'employee_availability'
	);

	// Save data
	public function save()
	{
		$data = array(
			'employee_availability_id' => $this->employee_availability_id,
			'name'   => $this->name
		);

		/**
		 * This right here is a shitty way to save data.
		 **/

		$this->updateOrInsert($data);
	}
}
