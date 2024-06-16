<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->admin) {
    echo Message('You don\'t have access', 'Error', true);
}
$_GET['radio'] = array_key_exists('radio', $_GET) && in_array($_GET['radio'], ['on', 'off']) ? $_GET['radio'] : 'off';
if (!empty($_GET['radio'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('UPDATE serverconfig SET radio = ? WHERE id = 1');
    $db->execute([$_GET['radio']]);
    echo Message('You\'ve turned the radio '.$_GET['radio']);
}
if (array_key_exists('random', $_GET)) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id FROM users ORDER BY RAND() LIMIT 1');
    $db->execute();
    $id = $db->result();
    $random_person = new User($id); ?>Random player: <?php echo $random_person->formattedname;
}
