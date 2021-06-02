<?php

/* Controller for User Dashboard */

class DashBoard extends Controller {

  public $cases_from_db = null;
  public $cases_type = 2;
  public $users_from_db = null;
  public $fk_users = null;
  public $db_values = null;
  public $config_json_cases = null;
  public $pk_cases = null;
  public $queries_cases = null;
  public $urls_cases = null;
  public $case_type = null;
  public $id_cases = null;
  public $sub_type_cases = null;
  public $results = null;

  function __construct() {
      // open database connection by the configuration in /config/config.php
      parent::__construct();

      $this->cases_from_db = $this->cases_model->getCasesByType(2);

      // function to gather the date of the last latest_research

      foreach($this->cases_from_db AS $values) {

        $calculated_score = $values->calculated_score;
        $latest_research = $values->latest_research;
        $fk_cases = $values->pk_cases;
        $time_score = $values->time_score;
        $latest_resource_date = $this->resources_model->GetLatestResourcebyCase($fk_cases, $calculated_score, $latest_research, $time_score);

      }

      $this->cases_from_db = $this->cases_model->getCasesByType(2);

      $this->users_from_db = $this->users_model->getAllUsers();
      session_start();
      $this->fk_users = $_SESSION["pk_users"];
      session_write_close();
  }

    // Method to render the Dashboard
    public function index() {
        $optional_views = array('application/views/dashboard/index.php');
        $this->loadViews($optional_views, true);
    }

}
