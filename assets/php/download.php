<?php

require_once('../../../../../qcubed.inc.php');

$rootPath = APP_UPLOADS_DIR;

if (isset($_GET['download'])) {
    $download = $_GET['download'];

    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($download) . '"');
    header("Accept-Ranges: bytes");
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($rootPath . $download));
    ob_flush();
    flush();
    readfile($rootPath . $download);
    exit;
}
