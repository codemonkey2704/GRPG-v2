<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!csrf_check('csrfg', $_GET)) {
    echo Message(SECURITY_TIMEOUT_MESSAGE);
}
$_GET['attack'] = array_key_exists('attack', $_GET) && ctype_digit($_GET['attack']) ? $_GET['attack'] : null;
$errors = [];
if ($user_class->energypercent < 25) {
    $errors[] = 'You need to have at least 25% of your energy if you want to attack someone';
}
if ($user_class->jail) {
    $errors[] = 'You can\'t attack someone whilst in jail';
}
if ($user_class->hospital) {
    $errors[] = 'You can\'t attack someone whilst in hospital';
}
if (empty($_GET['attack'])) {
    $errors[] = 'You didn\'t choose someone to attack';
}
if ($_GET['attack'] == $user_class->id) {
    $errors[] = 'You can\'t attack yourself';
}
$db->query('SELECT COUNT(id) FROM users WHERE id = ?');
$db->execute([$_GET['attack']]);
if (!$db->result()) {
    $errors[] = 'That person doesn\'t exist';
}
$defender = new User($_GET['attack']);
if ($defender->city != $user_class->city) {
    $errors[] = 'You must be in the same city as the person you\'re attacking';
}
if ($defender->hospital) {
    $errors[] = 'You can\'t attack someone whilst they\'re in hospital';
}
if ($defender->jail) {
    $errors[] = 'You can\'t attack someone whilst they\'re in jail';
}
if ($user_class->level > 5 && $defender->level < 6) {
    $errors[] = 'You can\'t attack someone that is level 5 or below because you are higher than level 5.';
}
if (count($errors)) {
    display_errors($errors, true);
}
$yourhp = $user_class->hp;
$theirhp = $defender->hp;
?><tr>
    <th class="content-head">Fight House</th>
</tr>
<tr>
    <td class="content">You are in a fight with <?php echo $defender->formattedname; ?>.</td>
</tr>
<tr>
    <td class="content"><?php
$wait = $user_class->speed > $defender->speed ? 1 : 0;
$limit = 50; // limit of "rounds" - equates to half of value each (example: setting to 50 means 25 rounds each). Set to 0 to disable
$turns = 0;
while ($yourhp > 0 && $theirhp > 0) {
    ++$turns;
    $damage = $defender->moddedstrength - $user_class->moddeddefense;
    $damage = ($damage < 1) ? 1 : $damage;
    if ($wait == 0) {
        $yourhp -= $damage;
        echo $defender->formattedname; ?> hit you for <?php echo $damage; ?> damage using their <?php echo $defender->weaponname ? format($defender->weaponname) : 'fists'; ?>.<br /><?php
    } else {
        $wait = 0;
    }
    if ($yourhp > 0) {
        $damage = $user_class->moddedstrength - $defender->moddeddefense;
        $damage = ($damage < 1) ? 1 : $damage;
        $theirhp -= $damage; ?>You hit <?php echo $defender->formattedname; ?> for <?php echo $damage; ?> damage using your <?php echo $user_class->weaponname ? format($user_class->weaponname) : 'fists'; ?>.<br /><?php
    }
    if ($theirhp <= 0) { // attacker won
        $winner = $user_class->id;
        $theirhp = 0;
        $moneywon = floor($defender->money / 10);
        $expwon = 150 - (25 * ($user_class->level - $defender->level));
        $expwon = ($expwon < 0) ? 0 : $expwon;
        $newexp = $expwon + $user_class->exp;
        $db->trans('start');
        $db->query('UPDATE users SET experience = experience + ?, money = money + ?, battlewon = battlewon + 1, battlemoney = battlemoney + ? WHERE id = ?');
        $db->execute([$expwon, $moneywon, $moneywon, $user_class->id]);
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0), hwho = ?, hhow = \'wasattacked\', hwhen = ?, hospital = 1200, battlelost = battlelost + 1, battlemoney = battlemoney - ? WHERE id = ?');
        $db->execute([$moneywon, $user_class->id, date('g:i:sa'), $moneywon, $defender->id]);
        Send_Event($defender->id, 'You were hospitalized by {extra} for 20 minutes.', $user_class->id);
        //give gang exp
        if ($user_class->gang != 0) {
            $db->query('UPDATE gangs SET experience = experience + ? WHERE id = ?');
            $db->execute([$expwon, $user_class->gang]);
        }
        $db->trans('end');
        echo Message('You hospitalized '.$defender->formattedname.'. You gain '.prettynum($expwon).' exp and stole '.prettynum($moneywon, true));
    }
    if ($yourhp <= 0) { // defender won
        $winner = $defender->id;
        $yourhp = 0;
        $moneywon = floor($user_class->money / 10);
        $expwon = 100 - (25 * ($defender->level - $user_class->level));
        $expwon = ($expwon < 0) ? 0 : $expwon;
        $db->trans('start');
        $db->query('UPDATE users SET experience = experience + ?, money = money + ?, battlewon = battlewon + 1, battlemoney = battlemoney + ? WHERE id = ?');
        $db->execute([$expwon, $moneywon, $moneywon, $defender->id]);
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0), hwho = ?, hhow = \'attacked\', hwhen = ?, hospital = 1200, battlelost = battlelost + 1, battlemoney = battlemoney - ? WHERE id = ?');
        $db->execute([$moneywon, $defender->id, date('g:i:sa'), $moneywon, $user_class->id]);
        Send_Event($user_class->id, 'You were hospitalized by {extra} for 20 minutes.', $defender->id);
        //give gang exp
        if ($defender->gang != 0) {
            $db->query('UPDATE gangs SET experience = experience + ? WHERE id = ?');
            $db->execute([$expwon, $$defender->gang]);
        }
        $db->trans('end');
        echo Message($defender->formattedname.' hospitalized you and stole '.prettynum($moneywon, true).' from you.');
    }
    if ($limit > 0 && $turns >= $limit) {
        echo Message('You couldn\'t do enough damage to one another. This ended in a stalemate');
        break;
    }
}
//put defense log into gang
if ($defender->gang != 0) {
    $db->query('INSERT INTO ganglog (gangid, attacker, defender, winner) VALUES (?, ?, ?, ?)');
    $db->execute([$defender->gang, $user_class->id, $defender->id, $winner]);
}
//update users
$newenergy = $user_class->energy - floor($user_class->energy * .10);
$db->query('UPDATE users SET hp = ? WHERE id = ?');
$db->execute([$theirhp, $defender->id]);
$db->query('UPDATE users SET hp = ?, energy = GREATEST(energy - (energy * .1), 0) WHERE id = ?');
$db->execute([$yourhp, $user_class->id]);
?></td>
</tr>
