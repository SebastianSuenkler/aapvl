<?php

/* Controller for Case type 2 (Produktrecherche) */

class Case04 extends Controller {

    public $cases_from_db = null;
    public $cases_type = 4;
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
        $this->cases_from_db = $this->cases_model->getCasesByType(4);
        $this->users_from_db = $this->users_model->getAllUsers();
        session_start();
        $this->fk_users = $_SESSION["pk_users"];
        session_write_close();
    }

    public function index() {

        $optional_views = array('application/views/case04/index.php');
        $this->loadViews($optional_views, true);
    }

    public function show($pk_cases) {

        $this->db_values = $this->cases_model->getCasebyPK($pk_cases);

        $this->config_json_cases = process_json($this->db_values->config_json_cases);

        $this->pk_cases = $pk_cases;

        $this->id_cases = $this->db_values->id_cases;

        $this->queries_cases = $this->queries_model->getQueriesbyCase($pk_cases);

        $this->urls_cases = $this->urls_model->getURLsbyCase($pk_cases);

        $this->judgement_table = $this->reports_model->getResultTableByCase($pk_cases);        

        $this->case_type = "case04";

        $optional_views = array('application/views/case04/show.php', 'application/views/queries/index.php', 'application/views/urls/index.php');
        $this->loadViews($optional_views, true);
    }

    public function add() {

        $optional_views = array('application/views/case04/add.php');
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

        header('Location: ' . URL . 'case04/show/' . $last_id . '?status=c_s');
    }

    public function update($pk_cases) {

        $form_values = $_POST;

        $form_values = $this->process_form($form_values);

        $this->cases_model->updateCase($form_values, $pk_cases);

        header('Location: ' . URL . 'case04/show/' . $pk_cases . '?status=c_u');
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
        header('Location: ' . URL . 'case04' . '?status=c_d');
    }

    public function process_form($form_values) {
        $id_case04 = sanitze_string_for_db($form_values["id_case04"]);
        $type_cases = 4;
        $date_case04 = date("Y-m-d");
        $comment_case04 = sanitze_string_for_db($form_values["comment_case04"]);
        $config_json = process_json(open_external_file_to_read("/user/cases/" . $_SESSION["language_json"]["Language"] . "cases04_config.json"));
        $config_array = $config_json["Config"];
        $config_modules = "";
        $config_crawlers = "";

        foreach ($config_array AS $config) {

            $modules = $config["Modules"];
            $crawlers = $config["Crawlers"];
            $config_screenshots = $config["Screenshots"];
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

        $ip_case04 = $_SERVER['REMOTE_ADDR'];

        $form_values = array("id_cases" => $id_case04, "type_cases" => $type_cases, "sub_type_cases" => NULL, "fk_users" => $fk_users, "comment_cases" => $comment_case04, "date_cases" => $date_case04, "ip_cases" => $ip_case04, "config_json_cases" => $config);

        return $form_values;
    }

    public function judgement($fk_cases) {

        // define results

      #  $this->results = $this->resources_model->getResultsforJudgement($fk_cases);


      $table_name = "results_".$fk_cases;


    $table_columns = $this->reports_model->GetTableColumns($table_name);



    $last_fix_column_index = 20; // change that value if standard column scheme has to change


    for($i = $last_fix_column_index; $i < count($table_columns); $i++) {

      $array_custom_columns[] = $table_columns[$i];
    }


      $this->pk_cases = $fk_cases;

      $this->results_table = $table_name;

      $this->custom_columns = $array_custom_columns;

      $this->last_column_index = $last_fix_column_index;




  $optional_views = array('application/views/case04/judgement.php');
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
