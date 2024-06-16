<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['buy'] = array_key_exists('buy', $_GET) && ctype_digit($_GET['buy']) ? $_GET['buy'] : null;
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, name, cost FROM houses WHERE id = ?');
    $db->execute([$_GET['buy']]);
    if (!$db->count()) {
        echo Message('The house you selected doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    $cost = $row['cost'];
    if ($user_class->house) {
        $db->query('SELECT cost FROM houses WHERE id = ?');
        $db->execute([$user_class->house]);
        $cost -= ($db->result() * .75);
        echo Message('You have sold your house for 75% of what it was worth ('.prettynum($cost, true).'). That amount will go towards the purchase of the new property');
    }
    if ($cost > $user_class->money) {
        echo Message('You don\'t have enough money to buy that house');
    } else {
        $db->query('UPDATE users SET house = ?, money = GREATEST(money - ?, 0) WHERE id = ?');
        $db->execute([$_GET['buy'], $cost, $user_class->id]);
        echo Message('You\'ve purchased and moved into '.format($row['name']));
    }
}
$db->query('SELECT id, name, awake, cost FROM houses ORDER BY id ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Move House</th>
</tr><?php
if ($user_class->house > 0) {
    ?><tr>
        <td class="content center"><a href="house.php?action=sell">Sell Your House</a></td>
    </tr><?php
}
?><tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th width="45%">Type</th>
                    <th width="15%">Awake</th>
                    <th width="20%">Cost</th>
                    <th width="20%">Move</th>
                </tr>
            </thead><?php
if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            ?><tr>
                    <td><?php echo format($row['name']); ?></td>
                    <td><?php echo format($row['awake']); ?></td>
                    <td><?php echo prettynum($row['cost'], true); ?></td>
                    <td><?php echo $row['id'] > $user_class->house ? '<a href="house.php?buy='.$row['id'].'&amp;csrfg='.$csrfg.'">Move In</a>' : '&nbsp;'; ?></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="4" class="center">There are no properties</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
