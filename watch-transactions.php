<?php

while (true) {
    echo "[" . date('Y-m-d H:i:s') . "] Verificando status das transações...\n";
    exec('php artisan pix:check-status');
    sleep(5);
}
