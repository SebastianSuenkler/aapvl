<?php

/* Main Class for the app */

class Application {

    private $url_controller = null; // url for controller 
    private $url_action = null; // action for controller 
    private $url_parameter_1 = null; // url parameter 1 
    private $url_parameter_2 = null; // url parameter 2
    private $url_parameter_3 = null;  // url parameter 3 
    private $url_parameter_4 = null;  // url parameter 4    
    // Add more parameter if your action url needs more methods

    // Method to create the base of the app

    public function __construct() {

        $this->splitUrl(); // create array with URL parts in url
        //
        // Check if controller exists
        if (file_exists('./application/controller/' . $this->url_controller . '.php')) {
            require './application/controller/' . $this->url_controller . '.php';
            $this->url_controller = new $this->url_controller(); // assign controller
            
            // Check if action exists in controller and add more parameter if add needs more methods
            if (method_exists($this->url_controller, $this->url_action)) {
                if (isset($this->url_parameter_4)) {

                    $this->url_controller->{$this->url_action}($this->url_parameter_1, $this->url_parameter_2, $this->url_parameter_3, $this->url_parameter_4);
                } else if (isset($this->url_parameter_3)) {

                    $this->url_controller->{$this->url_action}($this->url_parameter_1, $this->url_parameter_2, $this->url_parameter_3);
                } elseif (isset($this->url_parameter_2)) {

                    $this->url_controller->{$this->url_action}($this->url_parameter_1, $this->url_parameter_2);
                } elseif (isset($this->url_parameter_1)) {

                    $this->url_controller->{$this->url_action}($this->url_parameter_1);
                } else {

                    $this->url_controller->{$this->url_action}();
                }
                
                // If no action found redirect to index method of controller
            } else {

                $this->url_controller->index();
            }
            
            // If no controller and action found redirect to the main controller index
        } else {
            require './application/controller/index.php';
            $index = new index();
            $index->home(); // call home method to render the start page
        }
    }

    // Method to splut the URL in actions
    private function splitUrl() {
        if (isset($_GET['url'])) {
            
            $url = rtrim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);

            $this->url_controller = (isset($url[0]) ? $url[0] : null);
            $this->url_action = (isset($url[1]) ? $url[1] : null);
            $this->url_parameter_1 = (isset($url[2]) ? $url[2] : null);
            $this->url_parameter_2 = (isset($url[3]) ? $url[3] : null);
            $this->url_parameter_3 = (isset($url[4]) ? $url[4] : null);
            $this->url_parameter_4 = (isset($url[5]) ? $url[5] : null);
        }
    }

}
