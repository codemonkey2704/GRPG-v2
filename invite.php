<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
$gang_class = new Gang($user_class->gang);
if ($gang_class->leader != $user_class->id) {
    echo Message('You don\'t have access.', 'Error', true);
}
$errors = [];
if (array_key_exists('invite', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['username'] = array_key_exists('username', $_POST) && is_string($_POST['username']) ? strip_tags(trim($_POST['username'])) : null;
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t enter a valid player name';
    }
    $id = Get_ID($_POST['username']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $target = new User($id);
    if ($target->gang) {
        $errors[] = $target->formattedname.' is already in '.($target->gang == $gang_class->id ? 'your' : 'a').' gang';
    }
    $db->query('SELECT COUNT(id) FROM ganginvites WHERE playerid = ? AND gangid = ?');
    $db->execute([$target->id, $gang_class->id]);
    if ($db->result()) {
        $errors[] = 'You\'ve already invited '.$target->formattedname.' to join '.$gang_class->formattedname;
    }
    if (!count($errors)) {
        $db->query('INSERT INTO ganginvites (playerid, gangid) VALUES (?, ?)');
        $db->execute([$target->id, $gang_class->id]);
        echo Message('You\'ve invited '.$target->formattedname.' to join '.$gang_class->formattedname);
    }
}
?><tr>
    <th class="content-head">Invite Player To Join <?php echo $gang_class->formattedname; ?></th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        <form action="invite.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="username">Mobster's name</label>
                    <input type="text" name="username" id="username" size="15" />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="invite" class="pure-button pure-button-primary">Send Invite</button>
            </div>
        </form>
    </td>
</tr>
