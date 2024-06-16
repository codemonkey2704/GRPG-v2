<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (empty($_GET['id'])) {
    echo Message('You didn\'t select a valid item', 'Error', true);
}
if (!Check_Item($_GET['id'], $user_class->id)) {
    echo Message('You don\'t own that item', 'Error', true);
}
$db->query('SELECT id, name FROM items WHERE id = ?');
$db->execute([$_GET['id']]);
if (!$db->count()) {
    echo Message('That item doesn\'t exist', 'Error', true);
}
$row = $db->fetch(true);
$errors = [];
if (array_key_exists('put', $_GET)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['price'] = array_key_exists('price', $_POST) && ctype_digit(str_replace(',', '', $_POST['price'])) ? str_replace(',', '', $_POST['price']) : null;
    if (empty($_POST['price'])) {
        $errors[] = 'You didn\'t enter a valid price';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('INSERT INTO itemmarket (itemid, userid, cost) VALUES (?, ?, ?)');
        $db->execute([$_GET['id'], $user_class->id, $_POST['price']]);
        Take_Item($_GET['id'], $user_class->id);
        $db->trans('end');
        echo Message('You\'ve added '.aAn($row['name'], false).' '.item_popup($row['id'], $row['name']).' to the market at the price of '.prettynum($_POST['price'], true), 'Error', true);
    }
}
?><tr>
    <th class="content-head">Put An Item On The Market</th>
</tr>
<tr>
    <td class="content" class="center">
        You are selling <?php echo item_popup($row['id'], $row['name']); ?><br /><br />
        <form action="putonmarket.php?id=<?php echo $_GET['id']; ?>&amp;put=true" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="price">Cost</label>
                    $<input type="text" name="price" id="price" size="10" maxlength="10" />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="market" class="pure-button pure-button-primary">Add</button>
            </div>
        </form>
        <a href="inventory.php">Back</a><br />
    </td>
</tr>
