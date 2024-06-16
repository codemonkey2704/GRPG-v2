<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$nums = ['bet_id', 'amount'];
foreach ($nums as $what) {
    $_POST[$what] = array_key_exists($what, $_POST) && ctype_digit($_POST[$what]) ? $_POST[$what] : null;
}
if (array_key_exists('takebet', $_POST)) {
    if (empty($_POST['bet_id'])) {
        echo Message('What\'s going on here??', 'Error', true);
    }
    $db->query('SELECT id, amount, owner FROM 5050game WHERE id = ?');
    $db->execute([$_POST['bet_id']]);
    if (!$db->count()) {
        echo Message('Invalid Bet.', 'Error', true);
    }
    $worked = $db->fetch(true);
    if ($worked['owner'] == $user_class->id) {
        echo Message('You can\'t take your own bet.', 'Error', true);
    }
    $user_points = new User($worked['owner']);
    if ($worked['amount'] > $user_class->money) {
        echo Message('You don\'t have enough money to match their bet.', 'Error', true);
    }
    $db->trans('start');
    $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
    $db->execute([$worked['amount'], $user_class->id]);
    $winner = mt_rand(0, 1);
    $worked['amount'] *= 2;
    if ($winner) { //the person who accepted the bid won
        $db->query('UPDATE users SET money = money + ? WHERE id = ?');
        $db->execute([$worked['amount'], $user_class->id]);
        Send_Event($user_points->id, 'You lost the '.prettynum($worked['amount'], true).' bid you placed.');
    } else { //original poster wins
        $db->query('UPDATE users SET money = money + ? WHERE id = ?');
        $db->execute([$worked['amount'], $user_points->id]);
        Send_Event($user_points->id, 'You won the '.prettynum($worked['amount'], true).' bid you placed.');
        echo Message('You have lost.');
    }
    $db->query('DELETE FROM 5050game WHERE id = ?');
    $db->execute([$worked['id']]);
    $db->trans('end');
}
if (array_key_exists('makebet', $_POST)) {
    if (!csrf_check('makebet_csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE, 'Error'.true);
    }
    if (empty($_POST['amount'])) {
        echo Message('What\'s going on here?', 'Error', true);
    }
    if ($_POST['amount'] > $user_class->money) {
        echo Message('You don\'t have that much money.', 'Error', true);
    }
    if ($_POST['amount'] < 1000) {
        echo Message('Please enter a valid amount of money.', 'Error', true);
    }
    $db->trans('start');
    $db->query('INSERT INTO 5050game (owner, amount) VALUES (?, ?)');
    $db->execute([$user_class->id, $_POST['amount']]);
    $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
    $db->execute([$_POST['amount'], $user_class->id]);
    $db->trans('end');
    $user_class = new User($_SESSION['id']);
    echo Message('You\'ve added '.prettynum($_POST['amount'], true));
}
$db->query('SELECT id, owner, amount FROM 5050game ORDER BY amount DESC');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">50/50 Chance Game</th>
</tr>
<tr>
    <td class="content">
        This game is simple. 2 people bet the same amount of money, then a winner is randomly picked. The winner recieves all of the money!
    </td>
</tr>
<tr>
    <td class="content">
        <form action="5050game.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create('makebet_csrf'); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="amount">Amount of money to bid  (minimum of <?php echo prettynum(1000, true); ?> bet)</label>
                    $<input type="text" name="amount" id="amount" size="10" maxlength="20" value="<?php echo $user_class->money; ?>" />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="makebet" class="pure-button pure-button-primary">Make Bet</button>
            </div>
        </form>
    </td>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th width="33%">Challenger</th>
                    <th width="34%">Amount</th>
                    <th width="33%">Action</th>
                </tr>
            </thead><?php
if ($rows !== null) {
        $csrf = csrf_create();
        foreach ($rows as $row) {
            $user_points = new User($row['owner']); ?><tr>
                    <td><?php echo $user_points->formattedname; ?></td>
                    <td><?php echo prettynum($row['amount'], true); ?></td>
                    <td><?php
        if ($user_class->id != $user_points->id) {
            ?><form action="5050game.php" method="post" class="pure-form">
                            <?php echo $csrf; ?>
                            <input type="hidden" name="bet_id" value="<?php echo $row['id']; ?>" />
                            <div class="pure-controls">
                                <button type="submit" name="takebet" class="pure-button pure-button-primary">Take Bet</button>
                            </div>
                        </form><?php
        } ?></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="3" class="center">There currently aren't any challenges</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
