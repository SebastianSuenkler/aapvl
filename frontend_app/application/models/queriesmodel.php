<?php

/* Model with all sql statements for the app for all kind of cases */

class QueriesModel extends Model {

    public function getQueries() {
        $sql = "SELECT `query_queries`, `date_queries`, `fk_cases`, `interval_days_queries`, `interval_completion_queries` FROM `queries`";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_queries' => $pk_queries));
        return $query->fetchAll();
    }

    public function getQuerybyPK($pk_queries) {
        $sql = "SELECT `query_queries`, `date_queries`, `fk_cases`, `interval_days_queries`, `interval_completion_queries` FROM `queries` WHERE `pk_queries` = :pk_queries";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_queries' => $pk_queries));
        return $query->fetch();
    }

    public function getQueriesbyCase($pk_cases) {
        $sql = "SELECT `pk_queries`, `query_queries`, `date_queries`, `fk_cases`, `interval_days_queries`, `interval_completion_queries` FROM `queries` WHERE `fk_cases` = :pk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_cases' => $pk_cases));
        return $query->fetchAll();
    }

    public function getQueriesforScraping($fk_cases) {
        $sql = "SELECT `pk_queries`, `query_queries`, `date_queries`, `fk_cases`, `interval_days_queries`, `interval_completion_queries` FROM `queries` WHERE `fk_cases` = :pk_cases";
        $query = $this->db->prepare($sql);
         $query->execute(array(':pk_cases' => $fk_cases));
        return $query->fetchAll();
    }

    public function saveQueries($db_values) {

        // Speichere die Formulardaten und die IP in der Datenbank
        foreach ($db_values AS $db_value) {
            $sql = "INSERT INTO `queries` (query_queries, date_queries, fk_cases, interval_days_queries, interval_completion_queries) VALUES (:query_queries, :date_queries, :fk_cases, :interval_days_queries, :interval_completion_queries)";
            $query = $this->db->prepare($sql);
            $query->execute(array(':query_queries' => $db_value["query_queries"], ':date_queries' => $db_value["date_queries"], ':fk_cases' => $db_value["fk_cases"], ':interval_days_queries' => $db_value["interval_days_queries"], ':interval_completion_queries' => $db_value["interval_completion_queries"]));
            $last_id = $this->db->lastInsertId();
        }
    }

    public function updateQuery($db_values, $pk_queries, $fk_cases) {

        $sql = "UPDATE `queries` SET query_queries=:query_queries, date_queries=:date_queries, interval_days_queries=:interval_days_queries, interval_completion_queries=:interval_completion_queries WHERE pk_queries=:pk_queries";
        $query = $this->db->prepare($sql);
        $query->execute(array(':query_queries' => $db_values["query_queries"], ':date_queries' => $db_values["date_queries"], ':interval_days_queries' => $db_values["interval_days_queries"], ':interval_completion_queries' => $db_values["interval_completion_queries"], ':pk_queries' => $pk_queries));

        // todo: scraper_jobs müssen auch geupdated werden, falls diese angelegt wurden
    }

    public function deleteQuery($pk_queries, $fk_cases) {
        unlink();
        $sql = "DELETE FROM `queries` WHERE pk_queries=:pk_queries";
        $query = $this->db->prepare($sql);
        $query->execute(array(':pk_queries' => $pk_queries));
    }

    public function deleteQueriesbyCase($fk_cases) {
        $sql = "DELETE FROM `queries` WHERE fk_cases=:fk_cases";
        $query = $this->db->prepare($sql);
        $query->execute(array(':fk_cases' => $fk_cases));
    }

}
