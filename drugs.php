<?php
declare(strict_types=1);

require_once __DIR__.'/inc/header.php';
if (!csrf_check('csrfg', $_GET)) {
    echo Message(SECURITY_TIMEOUT_MESSAGE);
}
$drug = array_key_exists('use', $_GET) && in_array($_GET['use'], ['cocaine', 'genericsteroids', 'nodoze']) ? $_GET['use'] : null;
$which = ['cocaine' => 'Cocaine', 'genericsteroids' => 'Generic Steroids', 'nodoze' => 'No-Doze'];
if (empty($drug)) {
    echo Message('You didn\'t select a valid drug', 'Error', true);
}
$db->query('SELECT COUNT(id) FROM effects WHERE userid = ?');
$db->execute([$user_class->id]);
if ($db->result()) {
    echo Message('You\'re already under the influence', 'Error', true);
}
if (!$user_class->$drug) {
    echo Message('You don\'t have any '.$which[$drug], 'Error', true);
}
$db->trans('start');
$db->query('UPDATE users SET '.$drug.' = GREATEST('.$drug.' - 1, 0) WHERE id = ?');
$db->execute([$user_class->id]);
if (in_array($drug, ['cocaine', 'genericsteroids'])) {
    $db->query('INSERT INTO effects (userid, effect, timeleft) VALUES (?, ?, 15)');
    $db->execute([$user_class->id, $which[$drug]]);
} elseif ($drug === 'nodoze') {
    $db->query('UPDATE users SET awake = LEAST(awake + 50, ?) WHERE id = ?');
    $db->execute([$user_class->maxawake, $user_class->id]);
}
$db->trans('end');
echo Message('You\'ve taken some '.$which[$drug]);
