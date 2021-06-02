<?php

/* Model with all sql statements for the app for all kind of cases */

class URLsModel extends Model {

    public function getURLsbyCase($pk_cases) {
        $sql = "SELECT `pk_urls`, `id_urls`, `date_urls`, `fk_cases`, `interval_days_urls`, `interval_completion_urls`, `elements_urls_path` FROM `urls` WHERE `fk_cases` = :pk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_cases' => $pk_cases));
        return $query->fetchAll();
    }

    public function getURLbyPK($pk_urls) {
        $sql = "SELECT `id_urls`, `date_urls`, `fk_cases`, `interval_days_urls`, `interval_completion_urls`, `elements_urls_path` FROM `urls` WHERE `pk_urls` = :pk_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_urls' => $pk_urls));
        return $query->fetch();
    }

    public function getURLs($pk_urls) {

        $sql = "SELECT `id_urls`, `date_urls`, `fk_cases`, `interval_days_urls`, `interval_completion_urls`, `elements_urls_path` FROM `urls` WHERE `pk_urls` = :pk_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_urls' => $pk_urls));
        return $query->fetchAll();
    }

    public function saveURLs($db_value) {
        // Speichere die Formulardaten und die IP in der Datenbank

        $sql = "INSERT INTO `urls` (id_urls, date_urls, fk_cases, interval_days_urls, interval_completion_urls) VALUES (:id_urls, :date_urls, :fk_cases, :interval_days_urls, :interval_completion_urls)";
        $query = $this->db->prepare($sql);
        $query->execute(array(':id_urls' => $db_value["id_urls"], ':date_urls' => $db_value["date_urls"], ':fk_cases' => $db_value["fk_cases"], ':interval_days_urls' => $db_value["interval_days_urls"], ':interval_completion_urls' => $db_value["interval_completion_urls"]));
        $last_id = $this->db->lastInsertId();
        $urls_directory = "resources/" . $db_value["fk_cases"] . "/"; // Wurzelverzeichnis für die Suchanfrage
        @mkdir($urls_directory, 0755); // Anlegen des Wurzelverzeichnisses

        $last_id = $this->db->lastInsertId();
        $urls_elements_json_file = $urls_directory . $last_id . "_" . $db_value["date_urls"] . "_url_elements.json";
        write_to_file($db_value["elements_urls"], $urls_elements_json_file);

        $sql = "UPDATE `urls` SET elements_urls_path=:elements_urls_path WHERE pk_urls=:pk_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_urls' => $last_id, ':elements_urls_path' => $urls_elements_json_file));

        return $last_id;
    }

    public function updateURLs($db_values, $pk_urls, $fk_cases) {

        $sql = "UPDATE `urls` SET id_urls=:id_urls, date_urls=:date_urls, interval_days_urls=:interval_days_urls, interval_completion_urls=:interval_completion_urls WHERE pk_urls=:pk_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':id_urls' => $db_values["id_urls"], ':date_urls' => $db_values["date_urls"], ':interval_days_urls' => $db_values["interval_days_urls"], ':interval_completion_urls' => $db_values["interval_completion_urls"], ':pk_urls' => $pk_urls));
        $urls_directory = "resources/" . $fk_cases . "/"; // Wurzelverzeichnis für die Suchanfrage
        $urls_elements_json_file = $urls_directory . $pk_urls . "_" . $db_values["date_urls"] . "_url_elements.json";
        write_to_file($db_values["elements_urls"], $urls_elements_json_file);
    }

    public function deleteURL($pk_urls, $fk_cases, $elments_json_file) {
        unlink($elments_json_file);
        $sql = "DELETE FROM `urls` WHERE pk_urls=:pk_urls";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_urls' => $pk_urls));
    }

    public function deleteURLsbyCase($fk_cases) {
        $sql = "DELETE FROM `urls` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

}
