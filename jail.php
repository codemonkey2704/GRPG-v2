<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['jailbreak'] = array_key_exists('jailbreak', $_GET) && ctype_digit($_GET['jailbreak']) ? $_GET['jailbreak'] : null;
$errors = [];
if (!empty($_GET['jailbreak'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (!userExists($_GET['jailbreak'])) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $jailed_person = new User($_GET['jailbreak']);
    if (!$jailed_person->jail) {
        $errors[] = $jailed_person->formattedname.' isn\'t in jail';
    }
    if ($jailed_person->id == $user_class->id) {
        $errors[] = 'You can not bust yourself from jail';
    }
    $nerve = ($jailed_person->level * 10) - 10;
    if ($nerve < 5) {
        $nerve = 5;
    }
    if ($nerve > $user_class->nerve) {
        $errors[] = 'You don\'t have enough nerve. You\'ll need '.format($nerve).' to bust '.$jailed_person->formattedname;
    }
    if (!count($errors)) {
        $chance = mt_rand(1, (100 * $nerve - ($user_class->speed / 25)));
        $money = 785;
        $exp = 785;
        if ($chance <= 75) {
            $db->trans('start');
            $db->query('UPDATE users SET experience = experience + ?, crimesucceeded = crimesucceeded + 1, crimemoney = crimemoney + ?, money = money + ?, nerve = GREATEST(nerve - ?, 0) WHERE id = ?');
            $db->execute([$exp, $money, $money, $nerve, $user_class->id]);
            $db->query('UPDATE users SET jail = 0 WHERE id = ?');
            $db->execute([$jailed_person->id]);
            Send_Event($jailed_person->id, 'You\'ve been busted out of jail by {extra}', $user_class->id);
            $db->trans('end');
            echo Message('Success! You receive '.$exp.' exp and '.prettynum($money));
        } elseif ($chance >= 150) {
            $db->query('UPDATE users SET crimefailed = crimefailed + 1, jail = 10800, nerve = GREATEST(nerve - ?, 0) WHERE id = ?');
            $db->execute([$nerve, $user_class->id]);
            echo Message('You were caught. You were hauled off to jail for '.time_format(10800));
        } else {
            $db->query('UPDATE users SET crimefailed = crimefailed + 1, nerve = GREATEST(nerve - ?, 0) WHERE id = ?');
            $db->execute([$nerve, $user_class->id]);
            echo Message('You\'ve failed');
        }
    }
}
$db->query('SELECT id, lastactive FROM users WHERE jail > 0 ORDER BY jail DESC');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Jail</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        <table class="pure-table pure-table-horizontal" width="100%">
            <thead>
                <tr>
                    <th>Mobster</th>
                    <th>Time Left</th>
                    <th>Actions</th>
                </tr>
            </thead><?php
if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            $user_jail = new User($row['id']); ?><tr>
                    <td><?php echo $user_jail->formattedname; ?></td>
                    <td><?php echo time_format($user_jail->jail); ?></td>
                    <td><a href="jail.php?jailbreak=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Break Out</a></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="3" class="center">There's no-one in jail</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
