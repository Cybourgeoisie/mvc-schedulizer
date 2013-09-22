<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Job
 */

include_once(MODEL_PATH . 'php/ObjectModel.php');

class Job extends ObjectModel
{
	// Properties
	protected $job_id;
	public    $name, $start_datetime, $end_datetime;

	// Save data
	public function save()
	{
		$data = array(
			'job_id'         => $this->job_id,
			'name'           => $this->name,
			'start_datetime' => $this->start_datetime,
			'end_datetime'   => $this->end_datetime
		);

		$this->updateOrInsert($data);
	}
}
