<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$errors = [];
$_GET['spend'] = array_key_exists('spend', $_GET) && in_array($_GET['spend'], ['energy', 'nerve', 'awake', 'HP']) ? $_GET['spend'] : null;
if ($_GET['spend'] === 'energy') {
    if (10 > $user_class->points) {
        $errors[] = 'You don\'t have enough points';
    }
    if ($user_class->energy == $user_class->maxenergy) {
        $errors[] = 'You\'re already full of energy';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET energy = ?, points = GREATEST(points - 10, 0) WHERE id = ?');
        $db->execute([$user_class->maxenergy, $user_class->id]);
        echo Message('You\'ve refilled your energy');
    }
}
if ($_GET['spend'] === 'nerve') {
    if (10 > $user_class->points) {
        $errors[] = 'You don\'t have enough points';
    }
    if ($user_class->nerve == $user_class->maxnerve) {
        $errors[] = 'You\'re already as brave as you\'re gonna get';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET nerve = ?, points = GREATEST(points - 10, 0) WHERE id = ?');
        $db->execute([$user_class->maxnerve, $user_class->id]);
        echo Message('You\'ve refilled your nerve');
    }
}
if ($_GET['spend'] === 'awake') {
    if (10 > $user_class->points) {
        $errors[] = 'You don\'t have enough points';
    }
    if ($user_class->awake == $user_class->maxawake) {
        $errors[] = 'You\'re already as brave as you\'re gonna get';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET awake = ?, points = GREATEST(points - 10, 0) WHERE id = ?');
        $db->execute([$user_class->maxawake, $user_class->id]);
        echo Message('You\'ve refilled your awake');
    }
}
if ($_GET['spend'] === 'HP') {
    if (10 > $user_class->points) {
        $errors[] = 'You don\'t have enough points';
    }
    if ($user_class->hp == $user_class->maxhp) {
        $errors[] = 'You\'re already as healthy as you\'re gonna get';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET hp = ?, points = GREATEST(points - 10, 0) WHERE id = ?');
        $db->execute([$user_class->maxhp, $user_class->id]);
        echo Message('You\'ve refilled your HP');
    }
}
?><tr>
    <th class="content-head">Point Shop</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">Welcome to the Point Shop, here you can spend your points on various things.</td>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <tr>
                <td><a href="spendpoints.php?spend=energy">Refil Energy</a></td>
                <td> - 10 Points</td>
            </tr>
            <tr>
                <td><a href="spendpoints.php?spend=nerve">Refil Nerve</a></td>
                <td> - 10 Points</td>
            </tr>
            <tr>
                <td><a href="spendpoints.php?spend=awake">Refil Awake</a></td>
                <td> - 10 Points</td>
            </tr>
            <tr>
                <td><a href="spendpoints.php?spend=HP">Refil HP</a></td>
                <td> - 10 Points</td>
            </tr>
        </table>
    </td>
</tr>
