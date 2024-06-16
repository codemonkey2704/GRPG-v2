<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['buy'] = array_key_exists('buy', $_GET) && ctype_digit($_GET['buy']) ? $_GET['buy'] : null;
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT itemid, userid, itemmarket.cost, name
    FROM itemmarket
    INNER JOIN items ON itemid = items.id
    WHERE itemmarket.id = ?');
    $db->execute([$_GET['buy']]);
    if (!$db->count()) {
        echo Message('The market listing you selected doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if ($row['userid'] == $user_class->id) {
        $db->trans('start');
        $db->query('DELETE FROM itemmarket WHERE id = ?');
        $db->execute([$_GET['buy']]);
        Give_Item($row['itemid'], $user_class->id);
        $db->trans('end');
        echo Message('You\'ve removed your '.item_popup($row['itemid'], $row['name']).' from the market', 'Error', true);
    }
    if ($row['cost'] > $user_class->money) {
        echo Message('You don\'t have enough money', 'Error', true);
    }
    $db->trans('start');
    $db->query('UPDATE users SET money = money + ? WHERE id = ?');
    $db->execute([$row['cost'], $row['userid']]);
    Send_Event($row['userid'], '{extra} purchased your '.item_popup($row['itemid'], $row['name']).' from the market for '.prettynum($row['cost']), $user_class->id);
    $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
    $db->execute([$row['cost'], $user_class->id]);
    Give_Item($row['itemid'], $user_class->id);
    $db->query('DELETE FROM itemmarket WHERE id = ?');
    $db->execute([$_GET['buy']]);
    $db->trans('end');
    echo Message('You\'ve bought '.aAn($row['name'], false).' '.item_popup($row['itemid'], $row['name']));
}
$db->query('SELECT itemmarket.id, itemid, itemmarket.cost, userid, name
FROM itemmarket
INNER JOIN items ON itemid = items.id
ORDER BY cost ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Item Market</th>
</tr>
<tr>
    <td class="content"><?php
if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            $user_item = new User($row['userid']);
            echo '<a href="itemmarket.php?buy='.$row['id'].'&amp;csrfg='.$csrfg.'">'.($row['userid'] == $user_class->id ? 'Remove Item' : 'Buy').'</a> '.item_popup($row['itemid'], $row['name']).' - '.prettynum($row['cost'], true).' from '.$user_item->formattedname.'<br />';
        }
    } else {
        ?>There are no items listed on the Item Market<?php
    }
?></td>
</tr>
