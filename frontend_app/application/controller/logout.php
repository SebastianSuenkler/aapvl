<?php

/* Controller for Logout process */

class Logout extends Controller {

    // Method to render the start page of the app with login form
    public function index() {
        session_start();
        session_destroy();
        header("Location:" . URL);
    }

}
