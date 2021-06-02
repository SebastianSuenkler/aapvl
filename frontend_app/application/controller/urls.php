<?php

/* Controller for Search Queries */

class URLs extends Controller {

    public $fk_cases = null;
    public $pk_urls = null;
    public $urls_elements = null;
    public $cases_values = null;
    public $case_type = null;
    public $id_cases = null;
    public $urls = null;
    public $sub_type_cases = null;
    public $elements_urls_path = null;
    public $elements_urls = null;
    public $type_cases = null;

    public function show($case, $pk_urls) {
        $this->pk_urls = $pk_urls;
        $this->urls_values = $this->urls_model->getURLbyPK($pk_urls);
        $this->case_type = $case;
        $this->fk_cases = $this->urls_values->fk_cases;
        $this->cases_values = $this->cases_model->getCasebyPK($this->fk_cases);
        $this->id_cases = $this->cases_values->id_cases;
        $this->elements_urls_path = $this->urls_values->elements_urls_path;
        $this->type_cases = $this->cases_values->type_cases;
        $this->elements_urls = process_external_json_from_file($this->elements_urls_path);

        foreach ($this->elements_urls AS $urls) {
            foreach ($urls AS $url) {
                $url_elements[] = $url["URL"];
            }
        }

        $this->elements_urls = implode("\n", $url_elements);
        $optional_views = array('application/views/urls/show.php');
        $this->loadViews($optional_views, true);
    }

    public function add($case, $fk_cases, $sub_type_cases) {
        $this->case_type = $case;
        $this->fk_cases = $fk_cases;
        $this->cases_values = $this->cases_model->getCasebyPK($fk_cases);
        $this->id_cases = $this->cases_values->id_cases;
        $this->sub_type_cases = $sub_type_cases;
        $this->type_cases = $this->cases_values->type_cases;
        $optional_views = array('application/views/urls/add.php');
        $this->loadViews($optional_views, true);
    }

    public function save($case, $fk_cases) {

        $form_values = $_POST;

        $form_values = $this->process_form($form_values, $fk_cases, $update = false);

        $pk_urls = $this->urls_model->saveURLs($form_values);
        
        require 'jobs.php';
        
       
        $jobs = new Jobs();
        $jobs->generate_jobs_urls($pk_urls);
        
        header('Location: ' . URL . $case . '/show/' . $fk_cases . '?status=u_s');
    }

    public function delete($fk_cases, $pk_urls, $case) {


        $this->urls_values = $this->urls_model->getURLbyPK($pk_urls);
        $elments_json_file = $this->urls_values->elements_urls_path;
        $this->urls_model->deleteURL($pk_urls, $elments_json_file);

        // todo löschen in der datenbank resources

        $this->jobs_model->deleteURLsJobsbyURL($pk_urls);
        header('Location: ' . URL . '/' . $case . '/show/' . $fk_cases . '?status=u_d');
    }

    public function update($case, $fk_cases, $pk_urls) {

        $form_values = $_POST;

        $form_values = $this->process_form($form_values, "", $update = true);

        $this->jobs_model->updateUrlsJobStatusByUrls($pk_urls, NULL);

        // todo aktualisieren in der datenbank resources

        $this->urls_model->updateURLs($form_values, $pk_urls, $fk_cases);
        
        require 'jobs.php';
        
        $jobs = new Jobs();
        $jobs->generate_jobs_urls();

        header('Location: ' . URL . $case . '/show/' . $fk_cases . '?status=u_u');
    }

    public function process_form($form_values, $fk_cases, $update) {

        $id_urls = $form_values["id_urls"];
        $elements_urls = $form_values["elements_urls"];
        $date_urls = $form_values["date_urls"];
        $interval_days_urls = $form_values["interval_days_urls"];
        $interval_completion_urls = NULL;

        $type_cases = $form_values["type_cases"];

        if (isset($form_values["interval_completion_urls"])) {

            $interval_completion_urls = $form_values["interval_completion_queries"];

            if ($interval_days_urls != 0 AND $interval_days_urls < 7) {
                $interval_days_urls = 7;


                $check_date = $interval_completion_urls;
                $check_date = date('Y-m-d', strtotime($check_date . ' + 7 day'));


                if (date('Y-m-d', $interval_completion_urls) < $check_date) {
                    $interval_completion_urls = $check_date;
                }
            }
        }

        if (!($update)) {
            $elements_urls = normalize_urls_json($elements_urls);
            $form_values = array("type_cases" => $type_cases, "id_urls" => $id_urls, "elements_urls" => $elements_urls, "fk_cases" => $fk_cases, "date_urls" => $date_urls, "interval_days_urls" => $interval_days_urls, "interval_completion_urls" => $interval_completion_urls);
        } else {
            $elements_urls = normalize_urls_json($elements_urls);
            $form_values = array("type_cases" => $type_cases, "id_urls" => $id_urls, "elements_urls" => $elements_urls, "fk_cases" => $fk_cases, "date_urls" => $date_urls, "interval_days_urls" => $interval_days_urls, "interval_completion_urls" => $interval_completion_urls);
        }

        return $form_values;
    }

}
