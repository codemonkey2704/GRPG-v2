<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
checkUserStatus();
$cost = settings('drive_travel_cost');
$db->query('SELECT COUNT(id) FROM cars WHERE userid = ?');
$db->execute([$user_class->id]);
$cnt = $db->result();
if (!$cnt) {
    echo Message('You don\'t have a car', 'Error', true);
}
$errors = [];
$_GET['go'] = array_key_exists('go', $_GET) && ctype_digit($_GET['go']) ? $_GET['go'] : null;
if (!empty($_GET['go'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, name, levelreq FROM cities WHERE id = ?');
    $db->execute([$_GET['go']]);
    if (!$db->count()) {
        $errors[] = 'The city you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    if ($_GET['go'] == $user_class->city) {
        $errors[] = 'You\'re already in '.format($user_class->cityname);
    }
    if ($row['levelreq'] > 0 && $row['levelreq'] > $user_class->level) {
        $errors[] = 'You\'re not experienced enough to go to '.format($row['name']);
    }
    if ($cost > $user_class->money) {
        $errors[] = 'You don\'t have enough cash to fill your tank';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET city = ?, money = GREATEST(money - '.$cost.', 0) WHERE id = ?');
        $db->execute([$_GET['go'], $user_class->id]);
        echo Message('Yo\'ve refilled your tank for '.prettynum($cost, true).' and drove to '.format($row['name']));
    }
}
$db->query('SELECT id, name, levelreq FROM cities WHERE levelreq <= ? AND id != ? ORDER BY levelreq , name ');
$db->execute([$user_class->level, $user_class->city]);
$rows = $db->fetch();
?><tr>
    <th class="content-head">Drive</th>
</tr>
<tr>
    <td class="content">Tired of <?php echo $user_class->cityname; ?>? Pay <?php echo prettynum($cost, true); ?> for gas for your car and you can drive anywhere you want.</td>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        <table class="pure-table pure-table-horizontal" width="100%">
            <thead>
                <tr>
                    <th width="33%">Location</th>
                    <th width="34%">Level Requirement</th>
                    <th width="33%">Travel</th>
                </tr>
            </thead><?php
if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            ?><tr>
                    <td><?php echo format($row['name']); ?></td>
                    <td><?php echo format($row['levelreq']); ?></td>
                    <td><a href="drive.php?go=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Drive</a></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="3" class="center">There are no other cities you can currently reach</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
