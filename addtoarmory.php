<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
if (empty($_GET['id'])) {
    echo Message('No item selected', 'Error', true);
}
if (!itemExists($_GET['id'])) {
    echo Message('Invalid item', 'Error', true);
}
$name = $db->result();
$item = item_popup($_GET['id']);
if (array_key_exists('put', $_GET)) { //if they are trying to put something up
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (!Check_Item($_GET['id'], $user_class->id)) {
        echo Message('You don\'t have any of those', 'Error', true);
    }
    $db->trans('start');
    $db->query('INSERT INTO gangarmory (itemid, gangid) VALUES (?, ?)');
    $db->execute([$_GET['id'], $user_class->gang]);
    Take_Item($_GET['id'], $user_class->id); //take one of the items they put up away from them
    $db->trans('end');
    echo Message('You\'ve added '.aAn($name, false).$item.' to the gang armory!<br /><br /><a href="gangarmory.php">Back to armory</a>', 'Error', true);
}
?><tr>
    <th class="content-head">Add An Item To The Gang Armory</th>
</tr>
<tr>
    <td class="content center">
        You are adding <?php echo aAn($name, false).$item; ?> to the gang armory.<br /><br />
        <a href="addtoarmory.php?id=<?php echo $_GET['id']; ?>&amp;put=true&amp;csrfg=<?php echo csrf_create('csrfg', false); ?>">Continue</a> | <a href="gangarmory.php">Back</a>
    </td>
</tr>
