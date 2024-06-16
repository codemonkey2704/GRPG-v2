<?php
declare(strict_types=1);
/* Character class mod v1.00
 * this file mug.php is
 * already part of grpg script
 * and has been modified and
 * secured for the above
 * mentioned mod.
*/
require_once __DIR__.'/inc/header.php';
if (!csrf_check('csrfg', $_GET)) {
    echo Message(SECURITY_TIMEOUT_MESSAGE);
}
$errors = [];
$_GET['mug'] = array_key_exists('mug', $_GET) && ctype_digit($_GET['mug']) ? $_GET['mug'] : null;
if (empty($_GET['mug'])) {
    $errors[] = 'You didn\'t specify a valid player';
}
if ($_GET['mug'] == $user_class->id) {
    $errors[] = 'You can\'t mug yourself.';
}
if (!userExists($_GET['mug'])) {
    $errors[] = 'The player you selected doesn\'t exist';
}
$attack_person = new User($_GET['mug']);
checkUserStatus();
checkUserStatus($attack_person->id);
if ($attack_person->city != $user_class->city) {
    $errors[] = 'You must be in the same city as the person you are attacking. Duh.';
}
if ($user_class->nerve < 10) {
    $errors[] = 'You need to have at least 10 nerve if you want to mug someone.';
}
if ($user_class->level > 5 && $attack_person->level < 6) {
    $errors[] = 'You can\'t attack someone that is level 5 or below because you are higher than level 5.';
}
if (count($errors)) {
    display_errors($errors);
} else {
    if ($user_class->speed > $attack_person->speed) {
        $mugamount = floor($attack_person->money / 4);
        if ($user_class->class === 'Thief') {
            $mugamount += ($attack_person->money / 4) * .05;
        }
        $db->trans('start');
        $db->query('UPDATE users SET money = money + ? WHERE id = ?');
        $db->execute([$mugamount, $user_class->id]);
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
        $db->execute([$mugamount, $attack_person->id]);
        Send_Event($attack_person->id, 'You were mugged for '.prettynum($mugamount, true).' by {extra}', $user_class->id);
        $db->trans('end');
        echo Message('You mugged '.$attack_person->formattedname.' for '.prettynum($mugamount, true));
    } else {
        Send_Event($attack_person->id, 'You were going to be mugged by {extra}, but your speed was higher and you saw them coming.', $user_class->id);
        echo Message('Their speed is higher than yours, so you failed.');
    }
    $db->query('UPDATE users SET nerve = GREATEST(nerve - 10, 0) WHERE id = ?');
    $db->execute([$user_class->id]);
}
