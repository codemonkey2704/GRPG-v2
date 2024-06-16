<?php
declare(strict_types=1);

require_once __DIR__.'/inc/header.php';
if (!csrf_check('csrfg', $_GET)) {
    echo Message(SECURITY_TIMEOUT_MESSAGE);
}
if ($_GET['id'] === null) {
    echo Message('You didn\'t select a valid item', 'Error', true);
}
$db->query('SELECT * FROM items WHERE id = ?');
$db->execute([$_GET['id']]);
if (!$db->count()) {
    echo Message('The item you selected doesn\'t exist', 'Error', true);
}
$row = $db->fetch(true);
$db->query('SELECT id, quantity FROM inventory WHERE itemid = ? AND userid = ?');
$db->execute([$row['id'], $user_class->id]);
$qty = $db->result(1);
if (!$qty) {
    echo Message('You don\'t own any '.format($row['name']), 'Error', true);
}
if (!$row['reduce'] && !$row['heal'] && !$row['drugstr'] && !$row['drugspe'] && !$row['drugdef']) {
    echo Message('The '.format($row['name']).' can\'t be used like this', 'Error', true);
}
Take_Item($row['id'], $user_class->id);
if ($row['reduce']) {
    if ($user_class->jail < 1) {
        echo Message('You\'re not in jail', 'Error', true);
    }
    $reduce = $row['reduce'] * 60;
    $freedom = $reduce >= $user_class->jail ? 'quickly escorted yourself out of jail' : 'reduced your time in jail by '.time_format($reduce);
    $db->query('UPDATE users SET jail = GREATEST(jail - ?, 0) WHERE id = ?');
    $db->execute([$reduce, $user_class->id]);
    echo Message('You\'ve used '.aAn($row['name']).' and '.$freedom);
} elseif ($row['heal']) {
    if ($user_class->hospital < 1) {
        echo Message('You\'re not in hospital', 'Error', true);
    }
    $perc = $row['heal'] / $user_class->maxhp * 100;
    $full = $user_class->hp + $perc >= $user_class->maxhp ? 'fully healed' : 'healed by '.format($perc).'%';
    $db->query('UPDATE users SET hp = LEAST(hp + ?, ?) WHERE id = ?');
    $db->execute([$perc, $user_class->maxhp, $user_class->id]);
    echo Message('You\'ve been '.$full, 'Success');
}
