<?php
          

// Main class for all data models

class Model {

    public function __construct($db) {
        
        session_start();
        session_write_close();
        
        try {
            $this->db = $db;
            
        } catch (PDOException $e) {
            exit('Database connection could not be established.');
        }
    }

}
