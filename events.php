<?php
declare(strict_types=1);
//*********************** The GRPG ***********************
//*$Id: events.php,v 1.2 2007/07/22 07:40:50 cvs Exp $*
//********************************************************
require_once __DIR__.'/inc/header.php';
$db->query('SELECT COUNT(id) FROM events WHERE recipient = ? AND viewed = 1');
$db->execute([$user_class->id]);
$cnt = $db->result();
if (array_key_exists('deleteall', $_GET)) {
    if (!csrf_check('csrfa', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($cnt) {
        $db->query('DELETE FROM events WHERE recipient = ? AND viewed = 1');
        $db->execute([$user_class->id]);
        echo Message(prettynum($cnt).' event'.s($cnt).' ha'.($cnt == 1 ? 's' : 've').' been deleted');
    } else {
        echo Message('You have no events to delete');
    }
}
$_POST['event_id'] = array_key_exists('event_id', $_POST) && ctype_digit($_POST['event_id']) ? $_POST['event_id'] : null;
if (array_key_exists('delete', $_POST)) {
    if (!csrf_check('csrfg', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, recipient FROM events WHERE id = ?');
    $db->execute([$_POST['event_id']]);
    if (!$db->count()) {
        echo Message('That event doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if ($row['recipient'] != $user_class->id) {
        echo Message('That isn\'t your event', 'Error', true);
    }
    $db->query('DELETE FROM events WHERE id = ?');
    $db->execute([$_POST['event_id']]);
    echo Message('Event deleted.');
}
$db->query('SELECT id, time_added, content, extra FROM events WHERE recipient = ? ORDER BY time_added DESC');
$db->execute([$user_class->id]);
$rows = $db->fetch();
$db->query('SELECT COUNT(id) FROM events WHERE viewed = 0 AND recipient = ?');
$db->execute([$user_class->id]);
if ($db->result()) {
    $db->query('UPDATE events SET viewed = 1 WHERE recipient = ?');
    $db->execute([$user_class->id]);
}
?><tr>
    <th class="content-head">Event Log</th>
</tr>
<tr>
    <td class="content center"><a href="events.php?deleteall&amp;csrfa=<?php echo csrf_create('csrfa', false); ?>">Delete All My Events</a></td>
</tr>
<tr>
    <td class="content"><?php
if ($rows !== null) {
        $csrf = csrf_create('csrfg');
        foreach ($rows as $row) {
            if (preg_match('/\{extra\}/', $row['content'])) {
                if ($row['extra'] > 0) {
                    $event_class = new User($row['extra']);
                    $row['content'] = str_replace(['{extra}', '{unknown}'], $event_class->formattedname, $row['content']);
                } else {
                    $row['content'] = str_replace(['{extra}', '{unknown}'], '[Unknown]', $row['content']);
                }
            }
            $date = new DateTime($row['time_added']); ?><table width="100%" class="pure-table pure-table-horizontal">
                <tr>
                    <th>Received</th>
                    <td colspan="3"><?php echo $date->format(DEFAULT_DATE_FORMAT); ?></td>
                </tr>
                <tr>
                    <td colspan="4" class="textm">Event:&nbsp;&nbsp;<?php echo $row['content']; ?></td>
                </tr>
            </table>
            <table width="100%" class="center">
                <tr>
                    <td>
                        <form action="events.php" method="post" class="pure-form">
                            <?php echo $csrf; ?>
                            <input type="hidden" name="event_id" value="<?php echo $row['id']; ?>" />
                            <div class="pure-controls">
                                <button type="submit" name="delete" class="pure-button pure-button-primary">Delete</button>
                            </div>
                        </form>
                    </td>
                </tr>
            </table><?php
        }
    } else {
        ?>You don't have any events<?php
    }
?></td>
</tr>
