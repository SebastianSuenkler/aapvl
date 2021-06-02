<?php
ignore_user_abort(true);
set_time_limit(0);

error_reporting(E_ALL);
ini_set("display_errors", 0);
ini_set("log_errors", 1);
ini_set("error_log", "error.log");

define('URL', 'http://localhost/aapvl/'); // // Main URL of the app
define('DB_TYPE', 'mysql'); // DBMS
define('DB_HOST', 'localhost'); // DB Host
define('DB_NAME', 'aapvl'); // DB Name
define('DB_USER', 'root'); // DB User
define('DB_PASS', ''); // DB Password
define('WEB_PATH', 'resources/'); // Internal Direcotry to save screenshots and websites for the cases
define('RESOURCE_URL', 'http://localhost/aapvl/resources/'); // External URL to access the resources
define('EXPORT_PATH', 'http://localhost/aapvl/reports/'); // AEternal URL for the reports

/*
define('URL', 'http://aapvl01.gnet.haw-hamburg.de/');

define('DB_TYPE', 'mysql');
define('DB_HOST', 'aapvl01.gnet.haw-hamburg.de');
define('DB_NAME', 'aapvl');
define('DB_USER', 'aapvl');
define('DB_PASS', 'SFZA{x}~');

define('WEB_PATH', 'resources/');
define('RESOURCE_URL', 'http://aapvl01.gnet.haw-hamburg.de/resources/');
define('EXPORT_PATH', 'http://aapvl01.gnet.haw-hamburg.de/export/');
// }}}

*/
