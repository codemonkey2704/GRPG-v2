<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!csrf_check('csrfg', $_GET)) {
    echo Message(SECURITY_TIMEOUT_MESSAGE);
}
if (empty($_GET['id'])) {
    echo Message('You didn\'t select a valid message', 'Error', true);
}
$db->query('SELECT id, recipient, sender, subject, msgtext, timesent FROM pms WHERE id = ?');
$db->execute([$_GET['id']]);
if (!$db->count()) {
    echo Message('The message you selected doesn\'t exist', 'Error', true);
}
$row = $db->fetch(true);
if ($row['recipient'] != $user_class->id) {
    echo Message('This isn\'t your message', 'Error', true);
}
$from_user_class = new User($row['sender']);
$db->query('UPDATE pms SET viewed = 1 WHERE id = ?');
$db->execute([$row['id']]);
$csrfg = csrf_create('csrfg', false);
?><tr>
    <th class="content-head">Mailbox</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <tr>
                <td width="15%">Subject:</td>
                <td width="45%"><?php echo format($row['subject']); ?></td>
                <td width="15%">Sender:</td>
                <td width="25%"><?php echo $from_user_class->formattedname; ?></td>
            </tr>
            <tr>
                <td>Received:</td>
                <td colspan="3"><?php echo date('F d, Y g:i:sa', $row['timesent']); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="textm">Message:<hr /><?php echo nl2br(format($row['msgtext'])); ?></td>
            </tr>
            <tr>
                <td colspan="4" class="center"><a href="pms.php?delete=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Delete</a> | <a href="pms.php?reply=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Reply</a></td>
            </tr>
            <tr>
                <td colspan="4" class="center"><a href="pms.php">Back To Mailbox</a></td>
            </tr>
        </table>
    </td>
</tr>
