<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id, name, cost FROM items WHERE id = ?');
$db->execute([$_GET['id']]);
$row = $db->fetch(true);
if ($row === null) {
    echo Message('That item doesn\'t exist', 'Error', true);
}
$row['id'] = (int)$row['id'];
$item = item_popup($row['id'], $row['name']);
if (!Check_Item($row['id'], $user_class->id)) {
    echo Message('You don\'t have any '.$item.'s', 'Error', true);
}
$price = $row['cost'] * .6;
if (array_key_exists('confirm', $_GET)) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->trans('start');
    $db->query('UPDATE users SET money = money + ? WHERE id = ?');
    $db->execute([$price, $user_class->id]);
    Take_Item($row['id'], $user_class->id);
    $db->trans('end');
    echo Message('You\'ve sold '.aAn($row['name'], false).' '.$item.' for '.prettynum($price, true), 'Error', true);
}
?><tr>
    <th class="content-head">Sell Item</th>
</tr>
<tr>
    <td class="content">
        Are you sure that you want to sell <?php echo aAn($row['name'], false).' '.$item; ?> for <?php echo prettynum($price, true); ?><br />
        <a href="sellitem.php?id=<?php echo $row['id']; ?>&amp;confirm&amp;csrfg=<?php echo csrf_create('csrfg', false); ?>">Yes</a>
    </td>
</tr>
