<?php

function calc_intervals($start_date, $interval_days, $end_date) {
    $intervals = array();
    $start_date = new DateTime($start_date);
    $end_date = new DateTime($end_date);
    $date = $start_date;
    

    if ($interval_days > 0) {

        while ($date <= $end_date) {
            $intervals[] = $date->format('Y-m-d');
            $date->add(new DateInterval('P' . $interval_days . 'D'));
        }
    }
    
    else {
        $intervals[] = $date->format('Y-m-d');
    }

    return ($intervals);
}
