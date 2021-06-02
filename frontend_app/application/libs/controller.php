<?php

/* Main Class for all controller */

class Controller {

    // Set public properties for the views and database to process them in other controllers
    public $db = null;
    public $views = null;
    // Load all models here
    public $users_model = null;
    public $cases_model = null;
    public $queries_model = null;
    public $urls_model = null;
    public $resources_model = null;
    public $reports_model = null;

    // Constructor which open the DatabaseConnection for the app
    function __construct() {
        // open database connection by the configuration in /config/config.php
        $this->openDatabaseConnection();
        $this->users_model = $this->loadModel("UsersModel");
        $this->cases_model = $this->loadModel("CasesModel");
        $this->queries_model = $this->loadModel("QueriesModel");
        $this->urls_model = $this->loadModel("URLsModel");
        $this->jobs_model = $this->loadModel("JobsModel");
        $this->resources_model = $this->loadModel("ResourcesModel");
        $this->reports_model = $this->loadModel("ReportsModel");
    }

    // Method to open the DatabaseConnection
    protected function openDatabaseConnection() {

        $options = array(PDO::ATTR_PERSISTENT => false, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING, PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'");
        $this->db = new PDO(DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS, $options);
    }

    // Method to load the assigned model for the selected controller
    protected function loadModel($model_name) {
        require_once 'application/models/' . strtolower($model_name) . '.php';
        return new $model_name($this->db);
    }

    // Method to load the assigned views to a controller: loads always a header and footer as mandatory views
    protected function loadViews($optional_views, $check = true) {

      if ($check) {

          $this->views[] = 'application/views/_templates/check.php'; // check user authentication
      }

        $this->views[] = 'application/views/_templates/header.php'; // header information; loading of scripts etc.

        foreach ($optional_views AS $optional_view) {
            $this->views[] = $optional_view;
        }

        $this->views[] = 'application/views/_templates/footer.php'; // footer of rendered html page



        foreach ($this->views AS $view) {
            require_once($view);
        }
    }

    protected function readCasesConfig($cases_type) {
         $config_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] . "cases0".$cases_type."_config.json"));
         $config_array = $config_json["Config"];
         return $config_array;
    }

}
