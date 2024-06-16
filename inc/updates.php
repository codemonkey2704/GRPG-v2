<?php
declare(strict_types=1);

if (!defined('GRPG_INC')) {
    exit;
}
define('CRON_FILE_INC', true);
require_once dirname(__DIR__) . '/crons/Cron.php';
$cron = new Cron($db);
$crons = ['1min', '5min', '1hour', '1day'];
foreach ($crons as $which) {
    if (($data = $cron->isDue($which)) !== null) {
        $cron->runCron($which, $data);
    }
}
