<?php
require_once __DIR__ . '/.env.php';
require_once __DIR__ . '/calendar_controller.php';
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/sandbox.php';

$client = getClient();
$service = new Google_Service_Calendar($client);
$calendarController = new CalendarController(
    $ENV['my_gmail'], 
    $ENV['my_work'], 
    $ENV['girlfriend_gmail'], 
    $ENV['girlfriend_work']);
$calendarController->getTodaysPossiblity();