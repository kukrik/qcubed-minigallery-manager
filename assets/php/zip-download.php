<?php

require_once('../../../../../qcubed.inc.php');

$zipPath = APP_UPLOADS_TEMP_DIR . '/_files/zip/';

if (isset($_GET['download'])) {
    $download = $_GET['download'];

    header('Content-Description: File Transfer');
    header('Content-Type: application/zip');
    header('Content-Disposition: attachment; filename="' . basename($download) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Connection: Keep-Alive');
    header('Expires: 0');
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Pragma: public');
    header('Content-Length: ' . filesize($zipPath . $download));
    readfile($zipPath . $download);
    ob_clean();
    flush();
    readfile($zipPath . $download);
    unlink($zipPath . $download);
    exit;
}



