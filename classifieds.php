<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$errors = [];
if (array_key_exists('submit', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['title'] = array_key_exists('title', $_POST) && is_string($_POST['title']) ? $_POST['title'] : null;
    if (empty($_POST['title'])) {
        $errors[] = 'You didn\'t enter a valid title';
    }
    $_POST['message'] = array_key_exists('message', $_POST) && is_string($_POST['message']) ? $_POST['message'] : null;
    if (empty($_POST['message'])) {
        $errors[] = 'You didn\'t enter a valid message';
    }
    $cost = (strlen($_POST['title']) + strlen($_POST['message'])) * 50;
    if (array_key_exists('confirm', $_POST)) {
        if ($cost > $user_class->money) {
            $errors[] = 'You don\'t have enough money for that!';
        }
        if (!count($errors)) {
            $db->trans('start');
            $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
            $db->execute([$cost, $user_class->id]);
            $db->query('INSERT INTO ads (poster, title, message) VALUES (?, ?, ?)');
            $db->execute([$user_class->id, $_POST['title'], $_POST['message']]);
            $db->trans('end');
            echo Message('You\'ve posted a classified ad for '.prettynum($cost, true));
        }
    } else {
        $csrf = csrf_create(); ?><tr>
            <th class="content-head">Posting an Ad</th>
        </tr>
        <tr>
            <td class="content">
                Are you sure you want to post that ad?<br />
                It'll cost you <?php echo prettynum($cost, true); ?> in total.<br />
                <form action="classifieds.php" method="post" class="pure-form pure-form-aligned">
                    <?php echo $csrf; ?>
                    <input type="hidden" name="title" value="<?php echo $_POST['title']; ?>" />
                    <input type="hidden" name="message" value="<?php echo $_POST['message']; ?>" />
                    <input type="hidden" name="confirm" value="true" />
                    <div class="pure-controls">
                        <button type="submit" name="submit" class="pure-button pure-button-primary">Yes, post it</button>
                    </div>
                </form><br />
                <a href="classifieds.php">No, go back</a>
            </td>
        </tr><?php
    }
}
$db->query('SELECT poster, title, message FROM ads ORDER BY time_added DESC LIMIT 10');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Classified Ads</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
if (!isset($csrf)) {
    $csrf = csrf_create();
}
?><tr>
    <td class="content">Here you can post any thing your heart desires. Careful though, as it costs <?php echo prettynum(50, true); ?> per character in the title and in the message.</td>
</tr>
<tr>
    <td class="content">
        <form action="classifieds.php" method="post" class="pure-form pure-form-aligned">
            <?php echo $csrf; ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" size="40" maxlength="100" />
                </div>
                <div class="pure-control-group">
                    <label for="message">Message</label>
                    <textarea name="message" id="message" cols="60" rows="4"></textarea>
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Post</button>
            </div>
        </form>
    </td>
</tr><?php
if ($rows !== null) {
    foreach ($rows as $row) {
        $user_ads = new User($row['poster']); ?><tr>
            <td class="content">
                <table width="100%" class="pure-table pure-table-horizontal">
                    <tr>
                        <th width="12.5%">Title</th>
                        <td width="37.5%"><?php echo format($row['title']); ?></td>
                        <th width="12.5%">Poster</th>
                        <td width="37.5%"><?php echo $user_ads->formattedname; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" class="center"><?php echo nl2br(format($row['message'])); ?></td>
                    </tr>
                </table>
            </td>
        </tr><?php
    }
} else {
    echo Message('There are no ads');
}
