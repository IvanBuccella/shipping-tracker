<?php

include 'classes/Tracker.class.php';

header('Content-Type: text/json');

$trackingCode = $_GET["tracking-code"] ?? "";

try {
    http_response_code(200);
    $tracker = new Tracker();
    echo $tracker->getTrackingHistory($trackingCode);
    $tracker->close();
} catch (Exception $e) {
    http_response_code($e->getCode() ?? 400);
    echo $e->getMessage();
}
