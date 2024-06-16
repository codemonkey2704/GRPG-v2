<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if ($user_class->city != 2) {
    echo Message('You\'re not in the right location', 'Error', true);
}
$_GET['buy'] = array_key_exists('buy', $_GET) && ctype_digit($_GET['buy']) ? $_GET['buy'] : null;
$errors = [];
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, name, cost, buyable FROM carlot WHERE id = ?');
    $db->execute([$_GET['buy']]);
    if (!$db->count()) {
        $errors[] = 'Invalid car';
    }
    $row = $db->fetch(true);
    if (!$row['buyable']) {
        $errors[] = 'That car can\'t be bought this way';
    }
    if ($row['cost'] > $user_class->money) {
        $errors[] = 'You don\'t have enough money to buy '.aAn($row['name']);
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
        $db->execute([$row['cost'], $user_class->id]);
        $db->query('INSERT INTO cars (userid, carid) VALUES (?, ?)');
        $db->execute([$user_class->id, $_GET['buy']]);
        $db->trans('end');
        echo Message('You\'ve purchased '.aAn($row['name']));
    }
}
$db->query('SELECT id, name, cost, image FROM carlot WHERE buyable = 1 ORDER BY cost ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Car Lot</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">Welcome to Big Bob's Used Car Lot! Just take your pick of any cars I have out in my lot.</td>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="center">
            <tr><?php
if ($rows !== null) {
        $cnt = 1;
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            ?><td<?php echo $cnt < 4 ? ' width="25%"' : ''; ?>>
                        <img src="<?php echo format($row['image']); ?>" width="100" heeight="100" style="border: 1px solid #333;" /><br />
                        <?php echo car_popup($row['name'], $row['id']); ?><br />
                        <?php echo prettynum($row['cost'], true); ?><br />
                        [<a href="carlot.php?buy=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Buy</a>]
                    </td><?php
        ++$cnt;
            if (!($cnt % 4)) {
                echo '</tr><tr>';
            }
        }
    } else {
        ?><td>There are no cars</td><?php
    }
?></tr>
        </table>
    </td>
</tr>
