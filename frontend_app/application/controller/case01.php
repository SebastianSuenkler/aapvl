<?php

/**  Extended Class of a Controller for Cases type = 1 */


class Case01 extends Controller {

  /** Default parameters for the class
  *@param array $cases_from_db Array for all cases in db
  *@param int $cases_type Attritube for cases type
  *@param array $users_from_db Array for all users in db
  *@param int $fk_users ID of logged users
  *@param array $db_values Array for data of a selected case
  *@param array $config_json_cases JSON Object for the config data of the case
  *@param int $pk_cases ID of the case
  *@param array $queries_cases Array of all given search queries to the case
  *@param array $urls_cases Array of all given URLs to the case
  *@param string $id_cases Name of the case
  *@param int $sub_type_cases Value of sub_type if a sub type exists
  *@param array $results Array of saved results of the case
  */

    public $cases_from_db = null;
    public $cases_type = 1;
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

    /** Default constructor. */

    function __construct() {

        parent::__construct();
        /**Set parameters for the overview of all cases type = 1
        *@see views/case01/index.php
        *@param array $this->cases_from_db Get all cases by Type 1 to save them as $arrayName
        *@param array $this->users_from_db Get all Users
        *@param int $this->fk_users Open the session to get the user who is currently logged in
        */

        $this->cases_from_db = $this->cases_model->getCasesByType(1);
        $this->users_from_db = $this->users_model->getAllUsers();
        session_start();
        $this->fk_users = $_SESSION["pk_users"];
        session_write_close();
    }

    /** Function to load the templates to generate the view for cases type = 1 */

    public function index() {

      /**
      *@param array $optional_views Load the view file for the case
      *@see views/case01/index.php
      * Load view via the method loadViews
      *@see libs/controller.php
      */

        $optional_views = array('application/views/case01/index.php');
        $this->loadViews($optional_views, true);
    }

    public function show($pk_cases) {

        $this->db_values = $this->cases_model->getCasebyPK($pk_cases);

        $this->config_json_cases = process_json($this->db_values->config_json_cases);

        $this->pk_cases = $pk_cases;

        $this->id_cases = $this->db_values->id_cases;

        $this->queries_cases = $this->queries_model->getQueriesbyCase($pk_cases);

        $this->urls_cases = $this->urls_model->getURLsbyCase($pk_cases);

        $this->case_type = "case01";

        $this->sub_type_cases = $this->db_values->sub_type_cases;

        $this->judgement_table = $this->reports_model->getResultTableByCase($pk_cases);

        $optional_views = array('application/views/case01/show.php', 'application/views/queries/index.php', 'application/views/urls/index.php');
        $this->loadViews($optional_views, true);
    }

    public function add() {

        $optional_views = array('application/views/case01/add.php');
        $this->loadViews($optional_views, true);
    }

    public function save() {

        $form_values = $_POST;

        $form_values = $this->process_form($form_values);

        $last_id = $this->cases_model->saveCase($form_values);

        $resources_directory = "resources/" . $last_id . "/"; // Wurzelverzeichnis für die Suchanfrage
        @mkdir($queries_directory, 0755); // Anlegen des Wurzelverzeichnisses

        $html_directory = $resources_directory . "/html";
        @mkdir($html_directory, 0755, true);

        $screenshot_directory = $resources_directory . "/screenshots";
        @mkdir($screenshot_directory, 0755, true);

        $config_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] . "cases0" . $form_values["type_cases"] . "_config.json"));
        $config_array = $config_json["Config"];

        header('Location: ' . URL . 'case01/show/' . $last_id . '?status=c_s');
    }

    public function update($pk_cases) {

        $form_values = $_POST;

        $form_values = $this->process_form($form_values);

        $this->cases_model->updateCase($form_values, $pk_cases);

        header('Location: ' . URL . 'case01/show/' . $pk_cases . '?status=c_u');
    }

    public function delete($pk_cases) {
        $this->queries_model->deleteQueriesbyCase($pk_cases);
        $this->urls_model->deleteURLsbyCase($pk_cases);
        $this->cases_model->deleteCase($pk_cases);
        $this->jobs_model->deleteScrapersJobsbyCases($pk_cases);
        $this->jobs_model->deleteURLsJobsbyCases($pk_cases);
        $this->resources_model->deleteResourcesbyCase($pk_cases);
        $this->resources_model->deleteAssignedJobsbyCase($pk_cases);
        $this->jobs_model->deleteResults($pk_cases);
        $this->jobs_model->deleteJobs($pk_cases);
        header('Location: ' . URL . 'case01' . '?status=c_d');
    }

    public function process_form($form_values) {
        $id_case01 = sanitze_string_for_db($form_values["id_case01"]);
        $type_cases = 1;
        $date_case01 = date("Y-m-d");
        $config_case01 = $form_values["select_sub_type"];
        $comment_case01 = sanitze_string_for_db($form_values["comment_case01"]);
        $config_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] . "cases01_config.json"));
        $config_array = $config_json["Config"];
        $config_modules = "";
        $config_crawlers = "";

        foreach ($config_array AS $config) {
            $id = $config["ID"];
            $name = $config["Name"];
            if ($id == $config_case01) {
                $modules = $config["Modules"];
                $crawlers = $config["Crawlers"];
                $config_screenshots = $config["Screenshots"];
            }
        }

        foreach ($modules AS $module) {

            $config_modules .= $module . ",";
        }

        foreach ($crawlers AS $crawler) {
            $config_crawlers .= $crawler . ",";
        }

        $config_modules = rtrim($config_modules, ",");

        $config_crawlers = rtrim($config_crawlers, ",");

        $config = '{"Modules":[' . $config_modules . '], "Crawlers":[' . $config_crawlers . '], "Screenshots":' . $config_screenshots . '}';

        $fk_users = $form_values["fk_users"];

        $ip_case01 = $_SERVER['REMOTE_ADDR'];

        $form_values = array("id_cases" => $id_case01, "type_cases" => $type_cases, "sub_type_cases" => $config_case01, "fk_users" => $fk_users, "comment_cases" => $comment_case01, "date_cases" => $date_case01, "ip_cases" => $ip_case01, "config_json_cases" => $config);

        return $form_values;
    }

    public function judgement($fk_cases) {

        // define results



      $table_name = "results_".$fk_cases;


    $table_columns = $this->reports_model->GetTableColumns($table_name);



    $last_fix_column_index = 18; // change that value if standard column scheme has to change


    for($i = $last_fix_column_index; $i < count($table_columns); $i++) {

      $array_custom_columns[] = $table_columns[$i];
    }




      $this->pk_cases = $fk_cases;

      $this->results_table = $table_name;

      $this->custom_columns = $array_custom_columns;

      $this->last_column_index = $last_fix_column_index;




  $optional_views = array('application/views/case01/judgement.php');
  $this->loadViews($optional_views, true);

    }

  public function add_custom_column_for_judgement($table_id) {

$column = $_POST["add_custom_column"];

$table_name = "results_".$table_id;

$this->reports_model->AddNewColumn($table_name, $column);

header('Location: ../judgement/'.$table_id);


  }

  public function remove_custom_column_for_judgement($table_id) {

    $column = $_POST["del_custom_column"];

    $table_name = "results_".$table_id;


    $this->reports_model->RemoveColumn($table_name, $column);

    header('Location: ../judgement/'.$table_id);

  }

  public function export_results($table_id) {

    $download_link = $this->reports_model->ExportResults($table_id);

    header('Location: ../../report/export/'.$table_id);

  }



}
