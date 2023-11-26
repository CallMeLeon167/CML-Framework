<?php 
/** Enter your functions here */

function formatDate($dateString, $format = "d.m.Y") {
    $timestamp = strtotime($dateString);
    return date($format, $timestamp);
}

?>