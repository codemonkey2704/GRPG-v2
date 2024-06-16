<?php
declare(strict_types=1);
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$errors = [];
if (array_key_exists('submit', $_POST)) {
    $_POST['oldpass'] = array_key_exists('oldpass', $_POST) && is_string($_POST['oldpass']) ? $_POST['oldpass'] : null;
    if (empty($_POST['oldpass'])) {
        $errors[] = 'You didn\'t enter your old password';
    }
    $db->query('SELECT password FROM users WHERE id = ?');
    $db->execute([$user_class->id]);
    $oldPass = $db->result();
    if (!password_verify($_POST['oldpass'], $oldPass)) {
        if (!MD5_COMPATIBILITY) {
            $errors[] = 'Your old password was incorrect';
        } elseif (md5($_POST['oldpass']) != $oldPass) {
            $errors[] = 'Your old password was incorrect';
        }
    }
    $_POST['newpass'] = array_key_exists('newpass', $_POST) && is_string($_POST['newpass']) ? $_POST['newpass'] : null;
    if (empty($_POST['newpass'])) {
        $errors[] = 'You didn\'t enter a new password';
    }
    $_POST['confpass'] = array_key_exists('confpass', $_POST) && is_string($_POST['confpass']) ? $_POST['confpass'] : null;
    if (empty($_POST['confpass'])) {
        $errors[] = 'You didn\'t enter a password confirmation';
    }
    if ($_POST['newpass'] !== $_POST['confpass']) {
        $errors[] = 'The passwords you entered didn\'t match';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET password = ? WHERE id = ?');
        $db->execute([password_hash($_POST['newpass'], PASSWORD_BCRYPT), $user_class->id]);
        echo Message('You\'ve updated your password', 'Success', true);
    }
}
?><tr>
    <th class="content-head">Change Password</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        <form action="cpassword.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="oldpass">Current Password</label>
                    <input type="password" name="oldpass" id="oldpass" required autofocus />
                </div>
                <div class="pure-control-group">
                    <label for="newpass">New Password</label>
                    <input type="password" name="newpass" id="newpass" required />
                </div>
                <div class="pure-control-group">
                    <label for="confpass">Confirm Password</label>
                    <input type="password" name="confpass" id="confpass" required />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Update Password</button>
            </div>
        </form>
    </td>
</tr>
