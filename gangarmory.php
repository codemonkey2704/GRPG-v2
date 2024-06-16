<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['buy'] = array_key_exists('buy', $_GET) && ctype_digit($_GET['buy']) ? $_GET['buy'] : null;
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
$errors = [];
$gang_class = new Gang($user_class->gang);
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($gang_class->leader != $user_class->id) {
        echo Message('You\'re not the gang leader', 'Error', true);
    }
    $db->query('SELECT itemid FROM gangarmory WHERE id = ?');
    $db->execute([$_GET['buy']]);
    if (!$db->count()) {
        $errors[] = 'Invalid item';
    }
    $item = $db->result();
    $db->query('SELECT name FROM items WHERE id = ?');
    $db->execute([$item]);
    if (!$db->count()) {
        $errors[] = 'Item doesn\'t exist';
    }
    $name = $db->result();
    if (!count($errors)) {
        $db->trans('start');
        Give_Item($item, $user_class->id);
        $db->query('DELETE FROM gangarmory WHERE id = ?');
        $db->execute([$_GET['buy']]);
        $db->trans('end');
        echo Message('You\'ve taken '.aAn($name).' from the armory');
    }
}
$db->query('SELECT gangarmory.id, itemid, name
    FROM gangarmory
    INNER JOIN items ON itemid = items.id
    ORDER BY gangarmory.id ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head"><?php echo $gang_class->formattedname; ?> Vault</th>
</tr>
<tr>
    <td class="content">Please note that only the gang leader can take items out of the gang armory.</td>
</tr>
<tr>
    <th class="content-head">Items In Vault</th>
</tr>
<tr>
    <td class="content"><?php
    $csrfg = csrf_create('csrfg', false);

if ($rows !== null) {
        foreach ($rows as $row) {
            $sub = $gang_class->leader == $user_class->id ? '<a href="gangarmory.php?buy='.$row['id'].'&amp;csrfg='.$csrfg.'">Take</a>' : '';
            echo $sub.' '.format($row['name']).'<br />';
        }
    } else {
        ?>The armory is empty<?php
    }
?></td>
</tr>
<tr>
    <th class="content-head">Add Items To Vault</th>
</tr>
<tr>
    <td class="content"><?php
$db->query('SELECT itemid, quantity, name
    FROM inventory
    INNER JOIN items ON itemid = items.id
    WHERE userid = ?
    ORDER BY name ');
$db->execute([$user_class->id]);
$rows = $db->fetch();
if ($rows !== null) {
        foreach ($rows as $row) {
            echo format($row['name']); ?> [<?php echo format($row['quantity']); ?>] <a href="addtoarmory.php?id=<?php echo $row['itemid']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Add</a><br /><?php
        }
    } else {
        ?>You have no items<?php
    }
?></td>
</tr>
