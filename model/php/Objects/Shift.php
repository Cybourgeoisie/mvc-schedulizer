<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Shift
 */

include_once(MODEL_PATH . 'php/ObjectModel.php');

class Shift extends ObjectModel
{
	// Properties
	protected $shift_id;
	public    $name, $job_id, $start, $end;

	// Data
	protected $obj_data = array(
		'shift_id',
		'name',
		'job_id',
		'start',
		'end'
	);

	protected $rel_data = array(
		'Employee' => array()
	);

	// Relationships
	protected $relationships = array(
		'Employee' => 'employee_to_shift'
	);

	// Save data
	public function save()
	{
		$data = array(
			'shift_id' => $this->shift_id,
			'name'     => $this->name,
			'job_id'   => $this->job_id,
			'start'    => $this->start,
			'end'      => $this->end
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

		$this->updateOrInsert($data);
	}
}

/*

Reference for the full calendar plugin

	$year = date('Y');
	$month = date('m');

	echo json_encode(array(
	
		array(
			'id' => 111,
			'title' => "Event1",
			'start' => "$year-$month-10",
			'url' => "http://yahoo.com/"
		),
		
		array(
			'id' => 222,
			'title' => "Event2",
			'start' => "$year-$month-20",
			'end' => "$year-$month-22",
			'url' => "http://yahoo.com/"
		)
	
	));

*/