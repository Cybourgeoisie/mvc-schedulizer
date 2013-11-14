<?php

// Permit access to PHP includes
define('ACCESS', true);

// Debug PHP
define('DEBUG_PHP', true);

// Get the current location
define('ROOT_PATH', realpath(dirname(__FILE__) . '/../') . '/');

// Paths
define('LIB_PATH',        ROOT_PATH . 'lib/');
define('VIEW_PATH',       ROOT_PATH . 'view/');
define('CONTROLLER_PATH', ROOT_PATH . 'controller/');
define('MODEL_PATH',      ROOT_PATH . 'model/');
define('REPORT_PATH',     ROOT_PATH . 'data/reports/');

// Public-accessible paths
define('REPORT_DOWNLOAD_PATH', './data/reports/');

// Database
define('DATABASE_FILE', ROOT_PATH . 'data/schedulizer.sqlite3');
