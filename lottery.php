<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$errors = [];
if (array_key_exists('buy', $_GET)) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT COUNT(userid) FROM lottery WHERE userid = ?');
    $db->execute([$user_class->id]);
    $cnt = $db->result();
    if ($cnt >= 5) {
        $errors[] = 'You already have '.$cnt.' tickets';
    }
    if (1000 > $user_class->money) {
        $errors[] = 'You don\'t have enough money';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('INSERT INTO lottery (userid) VALUES (?)');
        $db->execute([$user_class->id]);
        $db->query('UPDATE users SET money = GREATEST(money - 1000, 0) WHERE id = ?');
        $db->execute([$user_class->id]);
        $db->trans('end');
        echo Message('You\'ve bought a lottery ticket');
    }
}
$db->query('SELECT COUNT(userid) FROM lottery');
$db->execute();
$tickets = $db->result();
$tickets = $tickets > 0 ? $tickets : 0;
$pot = $tickets * 750;
?><tr>
    <th class="content-head">Daily Lottery</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        Do you want to buy a ticket for the daily lottery? You can buy up to 5 tickets a day for <?php echo prettynum(1000, true); ?> a ticket. The more people that enter, the more that the winner will win. If your ticket is drawn at the end of the day, you win 75% of the ticket revenue!<br /><br />
        <a href="lottery.php?buy&amp;csrfg=<?php echo csrf_create('csrfg', false); ?>">Buy Ticket</a>
    </td>
</tr>
<tr>
    <td class="content">
        There <?php echo $tickets == 1 ? 'has' : 'have'; ?> been <?php echo format($tickets); ?> Lotto Ticket<?php echo s($tickets); ?> bought today<br />
        The pot is currently standing at <?php echo prettynum($pot, true); ?>
    </td>
</tr>
