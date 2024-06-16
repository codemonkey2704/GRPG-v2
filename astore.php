<?php
declare(strict_types=1);
//*********************** The GRPG ***********************
//*$Id: astore.php,v 1.3 2007/07/24 02:52:48 cvs Exp $*
//********************************************************
require_once __DIR__.'/inc/header.php';
$errors = [];
$_GET['buy'] = array_key_exists('buy', $_GET) && ctype_digit($_GET['buy']) ? $_GET['buy'] : null;
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, name, cost FROM items WHERE id = ? AND buyable = 1');
    $db->execute([$_GET['buy']]);
    if (!$db->count()) {
        $errors[] = 'The item you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $item = item_popup($row['id'], $row['name']);
    if ($row['cost'] > $user_class->money) {
        $errors[] = 'You don\'t have enough money to buy '.aAn($row['name'], false).$item;
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
        $db->execute([$row['cost'], $user_class->id]);
        Give_Item($_GET['buy'], $user_class->id); //give the user their item they bought
        $db->trans('end');
        echo Message('You\'ve purchased '.aAn($row['name'], false).$item);
    }
}
$db->query('SELECT id, image, name, cost FROM items WHERE defense > 0 AND buyable = 1 ORDER BY defense ');
$db->execute();
if (!$db->count()) {
    echo Message('There are currently no armors available', 'Error', true);
}
$rows = $db->fetch();
?><tr>
    <th class="content-head">Armor</th>
</tr>
<tr>
    <td class="content">Welcome to Crazy Riley's Armor Emporium! Please take as much time as you would like to browse through my selection of goods.</td>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        <table width='100%'>
            <tr><?php
$cnt = 1;
$csrfg = csrf_create('csrfg', false);
foreach ($rows as $row) {
    ?><td width="25%" class="center">
                    <img src="<?php echo format($row['image']); ?>" width="100" height="100" class="shopitem" /><br />
                    <?php echo item_popup($row['id'], $row['name']); ?> [x1]<br />
                    <?php echo prettynum($row['cost'], true); ?><br />
                    [<a href="astore.php?buy=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Buy</a>]
                </td><?php
    if (!($cnt % 4)) {
        ?></tr>
                    <tr><?php
    }
    ++$cnt;
}
?></tr>
        </table>
    </td>
</tr>
