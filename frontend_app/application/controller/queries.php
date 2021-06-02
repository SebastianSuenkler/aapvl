<?php

/* Controller for Search Queries */

class Queries extends Controller {

    public $fk_cases = null;
    public $pk_queries = null;
    public $queries_values = null;
    public $cases_values = null;
    public $case_type = null;
    public $id_cases = null;
    public $queries = null;
    public $sub_type_cases = null;
    public $type_cases = null;

    public function show($case, $pk_queries) {
        $this->pk_queries = $pk_queries;
        $this->queries_values = $this->queries_model->getQuerybyPK($pk_queries);
        $this->case_type = $case;
        $this->fk_cases = $this->queries_values->fk_cases;
        $this->cases_values = $this->cases_model->getCasebyPK($this->fk_cases);
        $this->id_cases = $this->cases_values->id_cases;
        $this->type_cases = $this->cases_values->type_cases;
        $optional_views = array('application/views/queries/show.php');
        $this->loadViews($optional_views, true);
    }

    public function add($case, $fk_cases, $sub_type_cases) {
        $this->case_type = $case;
        $this->fk_cases = $fk_cases;
        $this->cases_values = $this->cases_model->getCasebyPK($fk_cases);
        $this->id_cases = $this->cases_values->id_cases;
        $this->sub_type_cases = $sub_type_cases;
        $this->type_cases = $this->cases_values->type_cases;
        $optional_views = array('application/views/queries/add.php');
        $this->loadViews($optional_views, true);
    }

    public function save($case, $fk_cases) {

        $form_values = $_POST;

        $form_values = $this->process_form($form_values, $fk_cases, $update = false);

        $this->queries_model->saveQueries($form_values);

        require 'jobs.php';

        $jobs = new Jobs();
        $jobs->generate_jobs_scrapers($fk_cases);

        header('Location: ' . URL . $case . '/show/' . $fk_cases . '?status=q_s');
    }

    public function delete($fk_cases, $pk_queries, $case) {
// todo löschen der dazugehörigen json dateien
        // todo löschen in der datenbank resources
        $this->queries_model->deleteQuery($pk_queries, $fk_cases);
        $this->jobs_model->deleteScrapersJobsbyQuery($pk_queries);
        header('Location: ' . URL . '/' . $case . '/show/' . $fk_cases . '?status=q_d');
    }

    public function update($case, $fk_cases, $pk_queries) {

        // todo aktualisieren in der datenbank resources

        $form_values = $_POST;

        $form_values = $this->process_form($form_values, "", $update = true);

        $this->queries_model->updateQuery($form_values, $pk_queries, $fk_cases);
        
        require 'jobs.php';
        
        $jobs = new Jobs();
        $jobs->generate_jobs_scrapers();        

        header('Location: ' . URL . $case . '/show/' . $fk_cases . '?status=q_u');
    }

    public function process_form($form_values, $fk_cases, $update) {

        $date_queries = $form_values["date_queries"];
        $interval_days_queries = $form_values["interval_days_queries"];
        $interval_completion_queries = NULL;

        if (isset($form_values["interval_completion_queries"])) {

            $interval_completion_queries = $form_values["interval_completion_queries"];

            if ($interval_days_queries != 0 AND $interval_days_queries < 7) {
                $interval_days_queries = 7;


                $check_date = $interval_completion_queries;
                $check_date = date('Y-m-d', strtotime($check_date . ' + 7 day'));

                if (date('Y-m-d', $interval_completion_queries) < $check_date) {
                    $interval_completion_queries = $check_date;
                }
            }
        }

        if (!($update)) {
            $queries_query = explode("\n", $form_values["queries_query"]);
            foreach ($queries_query AS $query) {
                $query = trim($query);
                $query = cleanWhitespace($query);
                if (!empty($query)) {
                    $queries[] = sanitze_string_for_db($query);
                }
            }

            $queries = array_unique($queries);
            $queries = array_values($queries);

            $type_cases = $form_values["type_cases"];


            $form_values = array();

            foreach ($queries AS $query) {
                $form_values[] = array("type_cases" => $type_cases, "query_queries" => $query, "date_queries" => $date_queries, "fk_cases" => $fk_cases, "interval_days_queries" => $interval_days_queries, "interval_completion_queries" => $interval_completion_queries);
            }
        } else {
            $query_queries = $form_values["query_queries"];
            $form_values = array("type_cases" => $type_cases, "query_queries" => $query_queries, "date_queries" => $date_queries, "interval_days_queries" => $interval_days_queries, "interval_completion_queries" => $interval_completion_queries);
        }

        return $form_values;
    }

}
