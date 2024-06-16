<?php
declare(strict_types=1);

require_once __DIR__.'/inc/header.php';
if (!csrf_check('csrfg', $_GET)) {
    echo Message(SECURITY_TIMEOUT_MESSAGE);
}
$_GET['eq'] = array_key_exists('eq', $_GET) && in_array($_GET['eq'], ['weapon', 'armor']) ? $_GET['eq'] : null;
$_GET['unequip'] = array_key_exists('unequip', $_GET) && in_array($_GET['unequip'], ['weapon', 'armor']) ? $_GET['unequip'] : null;
if ($_GET['unequip'] === 'weapon' && $user_class->eqweapon != 0) {
    $db->trans('start');
    Give_Item($user_class->eqweapon, $user_class->id);
    $db->query('UPDATE users SET eqweapon = 0 WHERE id = ?');
    $db->execute([$user_class->id]);
    $db->trans('end');
    mrefresh('inventory.php');
    echo Message('You\'ve unequipped your weapon.', 'Error', true);
} elseif ($_GET['unequip'] === 'armor' && $user_class->eqarmor != 0) {
    $db->trans('start');
    Give_Item($user_class->eqarmor, $user_class->id);
    $db->query('UPDATE users SET eqarmor = 0 WHERE id = ?');
    $db->execute([$user_class->id]);
    $db->trans('end');
    mrefresh('inventory.php');
    echo Message('You\'ve unequipped your armor', 'Error', true);
}
if (empty($_GET['id'])) {
    echo Message('Invalid item', 'Error', true);
}
$db->query('SELECT id, name, level FROM items WHERE id = ?');
$db->execute([$_GET['id']]);
if (!$db->count()) {
    echo Message('The item you selected doesn\'t exist', 'Error', true);
}
$row = $db->fetch(true);
$howmany = Check_Item($_GET['id'], $user_class->id); //check how many they have
if (!$howmany) {
    echo Message('You don\'t have any '.format($row['name']).'s', 'Error', true);
}
if ($row['level'] > $user_class->level) {
    echo Message('You\'re not experienced enough to use the '.format($row['name']), 'Error', true);
}
$db->trans('start');
Take_Item($_GET['id'], $user_class->id);
if ($_GET['eq'] === 'weapon') {
    if ($user_class->eqweapon != 0) {
        Give_Item($user_class->eqweapon, $user_class->id);
    }
    $db->query('UPDATE users SET eqweapon = ? WHERE id = ?');
    $db->execute([$_GET['id'], $user_class->id]);
} elseif ($_GET['eq'] === 'armor') {
    if ($user_class->eqarmor != 0) {
        Give_Item($user_class->eqarmor, $user_class->id);
    }
    $db->query('UPDATE users SET eqarmor = ? WHERE id = ?');
    $db->execute([$_GET['id'], $user_class->id]);
}
$db->trans('end');
mrefresh('inventory.php');
echo Message('You\'ve succesfully equipped '.($_GET['eq'] === 'weapon' ? 'a ' : '').$_GET['eq']);
