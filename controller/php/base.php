<?php

/**
 * Config
 */
include_once('../../config/config.php');

/**
 * Debug
 */
if (defined('DEBUG_PHP') && DEBUG_PHP)
{
	error_reporting(E_ALL & ~E_NOTICE);
	ini_set('display_errors', '1');
}

/**
 * Includes
 */

// Database Handler
include_once(CONTROLLER_PATH . 'php/SchedulizerDatabaseHandler.php');

// Models
include_once(MODEL_PATH . 'php/Objects/Employee.php');
include_once(MODEL_PATH . 'php/Objects/Job.php');
include_once(MODEL_PATH . 'php/Objects/Shift.php');

// Schedulizer
include_once(CONTROLLER_PATH . 'php/Schedulizer.php');
