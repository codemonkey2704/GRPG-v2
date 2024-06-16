<?php
declare(strict_types=1);

require_once __DIR__.'/inc/header.php';
$_GET['view'] = array_key_exists('view', $_GET) && in_array(strtolower($_GET['view']), ['cocaine', 'generic steroids']) ? strtolower($_GET['view']) : null;
$effect = ['cocaine' => '+30% speed', 'generic steroids' => '+15% strength'];
echo Message($effect[$_GET['view']], 'Effect - '.ucwords($_GET['view']));
