<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
$gang_class = new Gang($user_class->gang);
if ($gang_class->leader != $user_class->id) {
    echo Message('You don\'t have authorization to be here.', 'Error', true);
}
if (array_key_exists('submit', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['changedesc'] = array_key_exists('changedesc', $_POST) && is_string($_POST['changedesc']) ? strip_tags(trim($_POST['changedesc'])) : '';
    $db->query('UPDATE gangs SET description = ? WHERE id = ?');
    $db->execute([$_POST['changedesc'], $gang_class->id]);
    $gang_class->description = format($_POST['changedesc']);
    echo Message('You\'ve changed the gang message.');
}
?><tr>
    <th class="content-head">Change Gang Message</th>
</tr>
<tr>
    <td class="content">
        <form action="changedesc.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="changedesc">Gang Message</label>
                    <textarea name="changedesc" id="changedesc" cols="53" rows="7"><?php echo $gang_class->description; ?></textarea>
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Change</button>
            </div>
        </form>
    </td>
</tr>
