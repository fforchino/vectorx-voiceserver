<?php

// https://www.wondergarden.app/voiceserver/index.php/getText?text=hello%20world&lang=en

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require __DIR__ . "/inc/bootstrap.php";
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );
//var_dump($uri);
if (isset($uri[3]) && ($uri[3] != 'getText' && $uri[3] != 'getVoices')) {
    header("HTTP/1.1 404 Not Found");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, GET, DELETE, PUT, PATCH, OPTIONS');
        header('Access-Control-Allow-Headers: token, Content-Type');
        header('Access-Control-Max-Age: 1728000');
        header('Content-Length: 0');
        header('Content-Type: text/plain');
        die();
    }

header('Access-Control-Allow-Origin: *');

require PROJECT_ROOT_PATH . "/Controller/Api/TTSController.php";

$objFeedController = new TTSController();
$strMethodName = $uri[3] . 'Action';
$objFeedController->{$strMethodName}();
?>