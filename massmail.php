<?php
declare(strict_types=1);
define('STAFF_FILE', true);
require_once __DIR__.'/inc/header.php';
if (!$user_class->admin) {
    echo Message('You don\'t have access', 'Error', true);
}
if (array_key_exists('newmessage', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['subject'] = array_key_exists('subject', $_POST) && is_string($_POST['subject']) ? strip_tags(trim($_POST['subject'])) : 'MASS MAIL';
    $_POST['msgtext'] = array_key_exists('msgtext', $_POST) && is_string($_POST['msgtext']) ? strip_tags(trim($_POST['msgtext'])) : null;
    $_POST['setmsg'] = isset($_POST['setmsg']);
    if (empty($_POST['msgtext'])) {
        echo Message('You didn\'t enter a valid message', 'Error', true);
    }
    $db->query('SELECT id FROM users ORDER BY id ');
    $db->execute();
    $rows = $db->fetch();
    $rowCnt = count($rows);
    if ($rowCnt == 1) {
        echo Message('You\'re the only player, there\'s no point sending a mass message', 'Error', true);
    }
    $cnt = 0;
    $db->trans('start');
    foreach ($rows as $row) {
        $db->query('INSERT INTO pms (recipient, sender, timesent, subject, msgtext) VALUES (?, ?, ?, ?, ?)');
        $db->execute([$row['id'], $user_class->id, time(), $_POST['subject'], $_POST['msgtext']]);
        ++$cnt;
    }
    if ($_POST['setmsg'] && strlen($_POST['msgtext']) <= 200) {
        $db->query('UPDATE serverconfig SET messagefromadmin = ? WHERE id = 1');
        $db->execute([$_POST['msgtext']]);
    }
    $db->trans('end');
    echo Message('Players: '.format($rowCnt).'. Messages sent: '.format($cnt).($cnt == $rowCnt ? '. All players have received your message' : ''));
}
?><tr>
    <th class="content-head">Mass Mail</th>
</tr>
<tr>
    <td class="content">Here you can send a mass mail to every player in the game.</td>
</tr>
<tr>
    <th class="content-head">New Message</th>
</tr>
<tr>
    <td class="content">
        <form action="massmail.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="subject">Subject</label>
                    <input type="text" name="submit" id="subject" size="70" maxlength="75" value="MASS MAIL" />
                </div>
                <div class="pure-control-group">
                    <label for="msgtext">Message</label>
                    <textarea name="msgtext" id="msgtext" cols="53" rows="7"></textarea>
                </div>
                <div class="pure-contro-group">
                    <label for="setmsg" class="pure-checkbox">
                        <input type="checkbox" name="setmsg" id="setmsg" value="1" /> Set as scrolling banner message<span title="Only if 200 characters or less" style="pointer:cursor;">(?)</span>
                    </label>
                </div>
            </fieldset>
            <div class="pure-cnotrols">
                <button type="submit" name="newmessage" id="newmessage" class="pure-button pure-button-primary"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Send Message</button>
            </div>
        </form>
    </td>
</tr>
