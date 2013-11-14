<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Job
 */

include_once(MODEL_PATH . 'php/ObjectModel.php');

class Job extends ObjectModel
{
	// Properties
	protected $job_id;
	public    $name;

	// Data
	protected $obj_data = array(
		'job_id' => null,
		'name'   => null
	);

	protected $rel_data = array(
		'Employee' => array()
	);

	// Relationships
	protected $relationships = array(
		'Employee' => 'employee_to_job'
	);

	// Save data
	public function save()
	{
		$data = array(
			'job_id' => $this->job_id,
			'name'   => $this->name
		);

		/**
		 * This right here is a shitty way to save data.
		 **/

		$this->updateOrInsert($data);
	}
}
