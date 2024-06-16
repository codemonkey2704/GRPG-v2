<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$errors = [];
$_GET['to'] = array_key_exists('to', $_GET) && is_string($_GET['to']) ? strip_tags(trim($_GET['to'])) : null;
$_GET['reply'] = array_key_exists('reply', $_GET) && ctype_digit($_GET['reply']) ? $_GET['reply'] : null;
$_GET['delete'] = array_key_exists('delete', $_GET) && ctype_digit($_GET['delete']) ? $_GET['delete'] : null;
if (!empty($_GET['delete'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT recipient FROM pms WHERE id = ?');
    $db->execute([$_GET['delete']]);
    $to = $db->result();
    if ($to != $user_class->id) {
        $errors[] = 'That isn\'t your message';
    }
    if (!count($errors)) {
        $db->query('DELETE FROM pms WHERE id = ?');
        $db->execute([$_GET['delete']]);
        echo Message('Message deleted!');
    }
}
if (array_key_exists('deleteall', $_GET)) {
    if (!csrf_check('csrfa', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT COUNT(id) FROM pms WHERE recipient = ?');
    $db->execute([$user_class->id]);
    $cnt = $db->result();
    if (!$cnt) {
        $errors[] = 'You don\'t have any message to delete';
    }
    if (!count($errors)) {
        $db->query('DELETE FROM pms WHERE recipient = ? AND viewed = 1');
        $db->execute([$user_class->id]);
        echo Message(format($cnt).' message'.s($cnt).' deleted');
    }
}
if (array_key_exists('newmessage', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['to'] = array_key_exists('to', $_POST) && is_string($_POST['to']) ? strip_tags(trim($_POST['to'])) : null;
    if (empty($_POST['to'])) {
        $errors[] = 'You didn\'t select a valid recipient';
    }
    $id = Get_ID($_POST['to']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $target = new User($id);
    $db->query('SELECT COUNT(id) FROM users_blocked WHERE (userid = ? AND blocked_id = ?) OR (blocked_id = ? AND userid = ?)');
    $db->execute([$user_class->id, $target->id, $user_class->id, $target->id]);
    if ($db->result()) {
        $errors[] = 'You can\'t message '.$target->formattedname;
    }
    $_POST['subject'] = array_key_exists('subject', $_POST) && is_string($_POST['subject']) ? strip_tags(trim($_POST['subject'])) : 'No subject';
    $_POST['msgtext'] = array_key_exists('msgtext', $_POST) && is_string($_POST['msgtext']) ? strip_tags(trim($_POST['msgtext'])) : null;
    if (empty($_POST['msgtext'])) {
        $errors[] = 'You didn\'t enter a valid message';
    }
    $db->query('SELECT COUNT(id) FROM pms WHERE subject = ? AND msgtext = ? AND sender = ? AND recipient = ? ORDER BY id DESC LIMIT 1');
    $db->execute([$_POST['subject'], $_POST['msgtext'], $user_class->id, $target->id]);
    if ($db->result()) {
        $errors[] = 'Double send detected';
    }
    if (!count($errors)) {
        $db->query('INSERT INTO pms (recipient, sender, timesent, subject, msgtext) VALUES (?, ?, ?, ?, ?)');
        $db->execute([$target->id, $user_class->id, time(), $_POST['subject'], $_POST['msgtext']]);
        echo Message('Your message has been sent to '.$target->formattedname);
    }
}
if (!empty($_GET['reply'])) {
    $db->query('SELECT recipient, sender, subject, msgtext FROM pms WHERE id = ?');
    $db->execute([$_GET['reply']]);
    if (!$db->count()) {
        $errors[] = 'The message you selected doesn\'t exist';
    }
    $mes = $db->fetch(true);
    if ($mes['recipient'] != $user_class->id) {
        $errors[] = 'That isn\'t your message';
    }
    $reply_class = new User($mes['sender']);
}
$db->query('SELECT * FROM pms WHERE recipient = ? ORDER BY timesent DESC');
$db->execute([$user_class->id]);
$rows = $db->fetch();
?><tr>
    <th class="content-head">Mailbox</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
$csrfa = csrf_create('csrfa', false);
$replyTo = isset($reply_class) ? $reply_class->username : '';
?><tr>
    <th class="content-head">New Message</th>
</tr>
<tr>
    <td class="content">
        <a href="pms.php?deleteall&amp;csrfa=<?php echo $csrfa; ?>">Delete All PMs In Your Inbox</a><br /><br />
        <form action="pms.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="to">Send To</label>
                    <input type="text" name="to" id="to" value="<?php echo !empty($_GET['to']) ? $_GET['to'] : $replyTo; ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="subject">Subject</label>
                    <input type="text" name="subject" id="subject" size="70" maxlength="75" value="<?php echo isset($mes) ? 'Re: '.ltrim('Re: ', format($mes['subject'])) : null; ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="msgtext">Message</label>
                    <textarea name="msgtext" id="msgtext" cols="53" rows="7"><?php echo isset($mes) ? "\n\n\n--------\n".format($mes['msgtext']) : ''; ?></textarea>
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="newmessage" class="pure-button pure-button-primary">Send</button>
            </div>
        </form>
    </td>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <td width="25%">Time Received</td>
                    <td width="25%">Subject</td>
                    <td width="25%">From</td>
                    <td width="20%">Viewed</td>
                    <td width="5%">Delete</td>
                </tr>
            </thead><?php
if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            $from_user_class = new User($row['sender']); ?><tr>
                    <td><?php echo date('F d, Y g:i:sa', $row['timesent']); ?></td>
                    <td><a href="viewpm.php?id=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><?php echo $row['subject'] ? format($row['subject']) : 'No subject'; ?></a></td>
                    <td><?php echo $from_user_class->formattedname; ?></td>
                    <td><?php echo !$row['viewed'] ? 'No' : 'Yes'; ?></td>
                    <td><a href="pms.php?delete=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Delete</a></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="5" class="center">You have no mail</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
