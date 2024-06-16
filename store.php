<?php
declare(strict_types=1);
//*********************** The GRPG ***********************
//*$Id: store.php,v 1.3 2007/07/24 02:52:21 cvs Exp $*
//********************************************************
require_once __DIR__.'/inc/header.php';
$errors = [];
$_GET['buy'] = array_key_exists('buy', $_GET) && ctype_digit($_GET['buy']) ? $_GET['buy'] : null;
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, name, cost, buyable FROM items WHERE id = ?');
    $db->execute([$_GET['buy']]);
    if (!$db->count()) {
        $errors[] = 'Invalid item';
    }
    $row = $db->fetch(true);
    $item = item_popup($row['id'], $row['name']);
    if (!$row['buyable']) {
        $errors[] = 'The '.$item.' can\'t be bought this way';
    }
    if ($row['cost'] > $user_class->money) {
        $errors[] = 'You don\'t have enough money';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
        $db->execute([$row['cost'], $user_class->id]);
        Give_Item($_GET['buy'], $user_class->id);
        $db->trans('end');
        echo Message('You\'ve purchased '.aAn($row['name'], false).' '.$item);
    }
}
$csrfg = csrf_create('csrfg', false);
$cnt = 0;
$weapons = '';
$db->query('SELECT * FROM items WHERE buyable = 1 AND offense > 0 ORDER BY cost ');
$db->execute();
$rows = $db->fetch();
foreach ($rows as $row) {
    $weapons .= '
    <td width="25%" class="center">
        '.formatImage($row['image']).'<br />
        '.item_popup($row['id'], $row['name']).'<br />
        '.prettynum($row['cost'], true).'<br />
        [<a href="store.php?buy='.$row['id'].'&amp;csrfg='.$csrfg.'">Buy</a>]
    </td>';
    ++$cnt;
    if (!($cnt % 4)) {
        $weapons .= '</tr><tr>';
    }
}
?><tr>
    <th class="content-head">Weapons</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content"><?php
if ($cnt) {
        ?><table width="100%" class="pure-table pure-table-horizontal">
            <tr>
                <?php echo $weapons; ?>
            </tr>
        </table><?php
    } else {
        ?>There are no weapons available<?php
    }
?></td>
</tr>
