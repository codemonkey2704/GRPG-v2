<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['person'] = array_key_exists('person', $_GET) && ctype_digit($_GET['person']) ? $_GET['person'] : null;
if (empty($_GET['id'])) {
    echo Message('You didn\'t select a valid item', 'Error', true);
}
$db->query('SELECT id, name FROM items WHERE id = ?');
$db->execute([$_GET['id']]);
if (!$db->count()) {
    echo Message('That item doesn\'t exist', 'Error', true);
}
$row = $db->fetch(true);
$item = item_popup($row['id'], $row['name']);
if (!Check_Item($_GET['id'], $user_class->id)) {
    echo Message('You don\'t have any '.$item.'s', 'Error', true);
}
if (array_key_exists('submit', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['theirid'] = array_key_exists('theirid', $_POST) && ctype_digit($_POST['theirid']) ? $_POST['theirid'] : null;
    if (empty($_POST['theirid'])) {
        echo Message('You didn\'t select a valid recipient', 'Error', true);
    }
    if (!userExists($_POST['theirid'])) {
        echo Message('The player you selected doesn\'t exist', 'Error', true);
    }
    $target = new User($_POST['theirid']);
    $db->trans('start');
    Take_Item($row['id'], $user_class->id);
    Give_Item($row['id'], $target->id);
    Send_Event($target->id, '{extra} sent you '.aAn($row['name'], false).' '.$item, $user_class->id);
    $db->trans('end');
    echo Message('You\'ve sent '.aAn($row['name'], false).' '.$item.' to '.$target->formattedname, 'Error', true);
}
?><tr>
    <th class="content-head">Send <?php echo aAn($row['name'], false).' '.$item; ?></th>
</tr>
<tr>
    <td class="content">
        <form action="senditem.php?id=<?php echo $row['id']; ?>" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="theirid">Mobster ID</label>
                    <input type="text" name="theirid" id="theirid" size="22" value="<?php echo $_GET['person']; ?>" />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Send Item</button>
            </div>
        </form>
    </td>
</tr>
