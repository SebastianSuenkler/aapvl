<?php

// load application config (error reporting etc.)
require 'application/config/config.php';

// load application class
require 'application/libs/application.php';
require 'application/libs/controller.php';
require 'application/libs/model.php';
require 'helper/strings.php';
require 'helper/curl.php';
require 'helper/scraper.php';
require 'helper/date.php';
require 'helper/files.php';
require 'helper/json.php';
require 'helper/urls.php';
require 'helper/rasterize.php';
// load application extensions


// start the application
$app = new Application();
