<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['go'] = array_key_exists('go', $_GET) && ctype_digit($_GET['go']) ? $_GET['go'] : null;
  $errors = [];
$cost = settings('bus_travel_cost');
if (!empty($_GET['go'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }

    $db->query('SELECT id, name, levelreq FROM cities WHERE id = ?');
    $db->execute([$_GET['go']]);
    if (!$db->count()) {
        $errors[] = 'Invalid city';
    }
    $city = $db->fetch(true);
    if ($user_class->jail) {
        $errors[] = 'You can\'t board a bus whilst in jail';
    }
    if ($user_class->hospital) {
        $errors[] = 'You can\'t board a bus whilst in hospital';
    }
    if ($_GET['go'] == $user_class->city) {
        $errors[] = 'You\'re already in '.format($city['name']);
    }
    if ($city['levelreq'] > $user_class->level) {
        $errors[] = 'You\'re not a high enough level to travel to '.format($city['name']);
    }
    if ($cost > $user_class->money) {
        $errors[] = 'You can\'t afford a bus ticket';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET city = ?, money = GREATEST(money - ?, 0) WHERE id = ?');
        $db->execute([$_GET['go'], $cost, $user_class->id]);
        echo Message('You paid '.prettynum($cost, true).' and arrived at your destination.', 'Error', true);
    }
}
$db->query('SELECT id, name, levelreq FROM cities WHERE ? >= levelreq AND id <> ? ORDER BY levelreq ', [$user_class->level, $user_class->city]);
$rows = $db->fetch();
?><tr>
    <th class="content-head">Bus Station</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">Tired of <?php echo format($user_class->cityname); ?>? For <?php echo prettynum($cost, true); ?> you can get a bus ticket to anywhere you want to go.</td>
</tr>
<tr>
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
                    <td><a href="bus.php?go=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Buy Ticket</a></td>
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
