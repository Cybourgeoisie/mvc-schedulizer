<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Shift
 */

include_once(MODEL_PATH . 'php/ObjectModel.php');

class Shift extends ObjectModel
{
	// Properties
	protected $shift_id;
	public    $name, $job_id, $start_datetime, $end_datetime;

	// Save data
	public function save()
	{
		$data = array(
			'shift_id'       => $this->shift_id,
			'name'           => $this->name,
			'job_id'         => $this->job_id,
			'start_datetime' => $this->start_datetime,
			'end_datetime'   => $this->end_datetime
		);

		$this->updateOrInsert($data);
	}
}
