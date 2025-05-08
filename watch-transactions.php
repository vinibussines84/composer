<?php

$logFile = __DIR__ . '/pix-watch.log';

while (true) {
    $timestamp = "[" . date('Y-m-d H:i:s') . "]";
    file_put_contents($logFile, "$timestamp Iniciando verificação...\n", FILE_APPEND);
    $output = shell_exec('php artisan pix:check-status');
    file_put_contents($logFile, "$output\n", FILE_APPEND);
    sleep(5);
}
