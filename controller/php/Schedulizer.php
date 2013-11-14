<?php if (!defined('ACCESS')) die('Unauthorised access.'); // Forbid direct access

/**
 * Schedulizer
 */

class Schedulizer
{
	public static function makeSchedule()
	{
		// Set the timezone to print timestamps
		// For now, Eastern Daylight Time as set in NYC
		date_default_timezone_set('America/New_York');

		// Get all of the shifts
		$shifts = Shift::getAll();

		// Get all of the employees
		$employees = Employee::getAll();

		// Format the employees with their availabilities
		foreach ($employees as $key => $employee)
		{
			// Decode the availability
			$employee['availability'] = json_decode($employee['availability'], true);

			// Save the formatted employee
			$employees[$key] = $employee;
		}

		// Apply an algorithm to make the schedule
		$schedule = self::scheduleBruteForce($shifts, &$employees);

		// Prepare the employees to hours information
		$employees_to_hours = array();
		foreach ($employees as $employee)
		{
			$employees_to_hours[$employee['name']] = $employee['scheduled_time'];
		}

		// Create CSV of schedule
		$filename      = uniqid(date('Y-m-d_His')) . '.csv';
		$download_path = REPORT_DOWNLOAD_PATH . $filename;
		$internal_path = REPORT_PATH . $filename;
		self::generateCsvReportBruteForce($internal_path, $schedule, $employees);

		$download_link = '<a href="' . $download_path . '">Download Schedule Here</a>';
		return $download_link . '<br /><pre>' . print_r($schedule, true)  . print_r($employees_to_hours, true) . '</pre>';
	}

	public function generateCsvReportBruteForce($filename, $full_schedule, $employees)
	{	
		// Days of the week, in order
		$days_of_the_week = array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday');

		// Set the delimiter
		$delimiter = ',';

		// Initialize the output string
		$output = '';

		// For each week..
		foreach ($full_schedule as $week_start_date => $schedule)
		{
			// Print the date
			$output .= date('l jS \of F Y h:i:s A', $week_start_date) . "\r\n";

			// Construct the CSV header
			$output .= "Employee" . $delimiter . implode($delimiter, $days_of_the_week) . "\r\n";

			// Order the employees alphabetically
			usort($employees, array(self, sortEmployeesByEmployeeId));

			// Construct the employee rows
			foreach ($employees as $employee)
			{
				// Print the employee's name
				$output .= '"' . addslashes($employee['name']) . '"' . $delimiter;

				// Go through each day of the week, see if the employee works
				foreach ($days_of_the_week as $key => $dotw)
				{
					$b_found_shift = false;

					foreach ($schedule as $shift)
					{
						// Find a shift on this dotw that the employee works
						if ($shift['day_of_week'] != $dotw) { continue; }
						if ($shift['employee_id'] != $employee['employee_id']) { continue; }
						$b_found_shift = true;

						//$output .= '"' . addslashes($shift['name'] . ': ' . $shift['start'] . ' to ' . $shift['end'])
						//	. '"' . $delimiter;

						// Determine Upstairs or Downstairs
						$b_upstairs   = strpos($shift['name'], 'Upstairs') !== false;
						$first_letter = $shift['name'][0];

						// Remove 'pm'
						$start = explode(' ', $shift['start']);
						$start = $start[0];

						// Custom output
						$output .= '"' . $first_letter . ' ' . $start . ' ' . ($b_upstairs ? 'UP' : 'DOWN') . '"' . $delimiter;
					}

					// Empty row
					if (!$b_found_shift && $key != 6)
					{
						$output .= $delimiter;
					}

					// Last day of the week, newline
					if ($key == 6)
					{
						$output .= "\r\n";
					}
				}
			}

			$output .= "\r\n";
		}

		// Save the data
		file_put_contents($filename, $output);
	}

	public function scheduleBruteForce($shifts, $employees)
	{
		// Keep track of the schedule
		$schedule = array();

		// We need to box all of the shifts into weeks
		$weeks = self::organizeShiftsToWeeksBruteForce($shifts);

		// For each shift in a week (Sunday through Saturday)..
		$shifts = array();
		foreach ($weeks as $week_start_date => $shifts)
		{
			// Determine who can work each shift.
			foreach ($shifts as $shift)
			{
				// Initialize the spot for the shift in the schedule
				$schedule[$week_start_date][$shift['shift_id']] = null;

				foreach ($employees as $key => $employee)
				{
					// Make sure the employee can perform the job for this shift
					// Then make sure that the employee is available for this shift
					if (self::canEmployeePerformShiftBruteForce($employee, $shift) &&
						self::isEmployeeAvailableForShiftBruteForce($employee, $shift))
					{
						// Add the person to the schedule
						$schedule[$week_start_date][$shift['shift_id']] = array(
							'start'         => date('g:i a', $shift['start']/1000),
							'end'           => date('g:i a', $shift['end']/1000),
							'day_of_week'   => date('l', $shift['start']/1000),
							'name'          => $shift['name'],
							'employee_id'   => $employee['employee_id'],
							'employee_name' => $employee['name']
						);

						// Remove this shift from the person's availability
						$employees[$key] = self::removeTimeFromEmployeeForShiftBruteForce($employee, $shift);

						// Add the time to the employee
						$employees[$key]['scheduled_time'] += ($shift['end'] - $shift['start']) / (60*60*1000);

						// Sort the employees by number of hours given
						usort($employees, array(self, sortEmployeesByHoursGiven));

						// Move on to the next shift.
						break;
					}
				}
			}
		}

		return $schedule;
	}

	public function organizeShiftsToWeeksBruteForce($shifts)
	{
		// Take each shift, determine when that week begins and when it ends
		$weeks = array();

		// Use the start time to determine which week this falls into
		foreach ($shifts as $shift)
		{
			$week_start = mktime(0, 0, 0, date("n", $shift['start'] / 1000), date("j", $shift['start'] / 1000) - date('w', $shift['start'] / 1000));
			$weeks[$week_start][] = $shift;
		}

		return $weeks;
	}

	/**
	 * Returns an updates employee
	 */
	public function removeTimeFromEmployeeForShiftBruteForce($employee, $shift)
	{
		foreach ($employee['availability'] as $key => $avail)
		{
			if ($avail['start'] <= $shift['start'] && $avail['end'] >= $shift['end'])
			{
				// First, unset the availability. This employee is no longer available for the full time.
				unset($employee['availability'][$key]);

				// Now, if there is some time before that can be salvaged, create an entry for it.
				if ($avail['start'] < $shift['start'])
				{
					$employee['availability'][] = array(
						'start' => $avail['start'],
						'end'   => $shift['start'] - 30*60*1000 - 1
					);
				}

				// Now see if there is time after the shift to be salvaged.
				if ($avail['end'] > $shift['end'])
				{
					$employee['availability'][] = array(
						'start' => $shift['end'] + 30*60*1000 + 1,
						'end'   => $avail['end']
					);
				}

				// TODO - Ideally, we would now rearrange the times to be in order.
			}
		}

		return $employee;
	}

	public function canEmployeePerformShiftBruteForce($employee, $shift)
	{
		return in_array($shift['job_id'], $employee['Job']);
	}

	public function isEmployeeAvailableForShiftBruteForce($employee, $shift)
	{
		foreach ($employee['availability'] as $avail)
		{
			if ($avail['start'] <= $shift['start'] && $avail['end'] >= $shift['end'])
			{
				return true;
			}
		}

		return false;
	}

/**
 * Sorting functions
 **/
	public function sortEmployeesByHoursGiven($a, $b)
	{
		// If the former has no time, the former comes first
		if (empty($a['scheduled_time']))
		{
			return false;
		}
		// If the latter has no time, the latter comes first
		else if (empty($b['scheduled_time']))
		{
			return true;
		}

		// Compare their current scheduled time
		return $a['scheduled_time'] > $b['scheduled_time'];
	}

	public function sortEmployeesByFirstName($a, $b)
	{
		// Get the first names
		$a_name  = explode(' ', $a['name']);
		$a_first = array_shift($a_name);

		$b_name  = explode(' ', $b['name']);
		$b_first = array_shift($b_name);

		return strnatcasecmp($a_first, $b_first) >= 0;
	}

	public function sortEmployeesByLastName($a, $b)
	{
		// Get the last names
		$a_name = explode(' ', $a['name']);
		$a_last = array_pop($a_name);

		$b_name = explode(' ', $b['name']);
		$b_last = array_pop($b_name);

		return strnatcasecmp($a_last, $b_last) >= 0;
	}

	public function sortEmployeesByEmployeeId($a, $b)
	{
		return $a['employee_id'] > $b['employee_id'];
	}
}
