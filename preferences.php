<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$genderOpts = ['Male', 'Female', 'Other'];
$errors = [];
if (array_key_exists('submit', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['avatar'] = array_key_exists('avatar', $_POST) && is_string($_POST['avatar']) ? $_POST['avatar'] : null;
    if (!empty($_POST['avatar']) && !isImage($_POST['avatar'])) {
        $errors[] = 'The avatar you selected hasn\'t validated as an image!';
    }
    $_POST['quote'] = array_key_exists('quote', $_POST) && is_string($_POST['quote']) ? strip_tags(trim($_POST['quote'])) : null;
    $_POST['gender'] = array_key_exists('gender', $_POST) && in_array($_POST['gender'], $genderOpts) ? $_POST['gender'] : null;
    if (!count($errors)) {
        $db->query('UPDATE users SET avatar = ?, quote = ?, gender = ? WHERE id = ?');
        $db->execute([$_POST['avatar'], $_POST['quote'], $_POST['gender'], $user_class->id]);
        $user_class->avatar = (string)$_POST['avatar'];
        $user_class->gender = (string)$_POST['gender'];
        $user_class->quote = (string)$_POST['quote'];
        echo Message('Your preferences have been saved.');
    }
}
?><tr>
    <th class="content-head">Account Preferences</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        <form action="preferences.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="avatar">Avatar Image Location</label>
                    <input type="text" name="avatar" id="avatar" value="<?php echo format($user_class->avatar); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="quote">Quote</label>
                    <input type="text" name="quote" id="quote" value="<?php echo format($user_class->quote); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="gender">Gender</label>
                    <select name="gender" id="gender"><?php
foreach ($genderOpts as $opt) {
    printf('<option value="%1$s"%2$s>%1$s</option>', $opt, $user_class->gender == $opt ? ' selected' : '');
}
?></select>
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Save Preferences</button>
            </div>
        </form>
    </td>
</tr>
