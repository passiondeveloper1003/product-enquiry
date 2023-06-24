<?php

// Data of the enquiry or quotation selected by user which are included in the CSV.
$data = json_decode($_POST['data']);

$head = array();

$hd = array();

if (sizeof($data) > 0) {
    //setting timezone to UTC for universal time , so that correct time is appended to the csv file name while csv generation for proper identification.
    date_default_timezone_set('UTC');
    $date = new DateTime();

    $ts = $date->format('Y-m-d-G-i-s');

    $filename = "report-$ts.csv";
    //CSV Generation using PHP, write content to output and specify headers to indicate its csv
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename='.$filename);

    $fp = fopen('php://output', 'w');
    $keys = array();

    foreach ($data[0] as $k => $v) {
        array_push($keys, $k);
    }

    fputcsv($fp, $keys);
    foreach ($data as $k => $v) {
        $values = array();
        foreach ($v as $m => $n) {
            array_push($values, $n);
        }
        fputcsv($fp, $values);
    }

    fclose($fp);
}

return true;
