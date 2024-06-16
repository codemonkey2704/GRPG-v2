<?php
declare(strict_types=1);

require_once __DIR__.'/header.php';
require_once dirname(__DIR__).'/inc/Parsedown.php';
$readme = file_get_contents(dirname(__DIR__).'/README.md');
$parsedown = new Parsedown();
echo $parsedown->text($readme);
