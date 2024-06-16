<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['accept'] = array_key_exists('accept', $_GET) && ctype_digit($_GET['accept']) ? $_GET['accept'] : null;
$_GET['delete'] = array_key_exists('delete', $_GET) && ctype_digit($_GET['delete']) ? $_GET['delete'] : null;
if (!empty($_GET['accept'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT COUNT(id) FROM gangs WHERE id = ?');
    $db->execute([$_GET['accept']]);
    if (!$db->result()) {
        echo Message('That gang doesn\'t exist', 'Error', true);
    }
    $db->query('SELECT COUNT(id) FROM ganginvites WHERE gangid = ? AND playerid = ?');
    $db->execute([$_GET['accept'], $user_class->id]);
    if (!$db->result()) {
        echo Message('That invite doesn\'t exist', 'Error', true);
    }
    $gang_class = new Gang($_GET['accept']);
    $db->trans('start');
    $db->query('UPDATE users SET gang = ? WHERE id = ?');
    $db->execute([$_GET['accept'], $user_class->id]);
    $db->query('DELETE FROM ganginvites WHERE playerid = ?');
    $db->execute([$user_class->id]);
    $db->trans('end');
    echo Message('You\'ve joined '.$gang_class->formattedname);
}
if (!empty($_GET['delete'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT COUNT(id) FROM gangs WHERE id = ?');
    $db->execute([$_GET['delete']]);
    if (!$db->result()) {
        echo Message('That gang doesn\'t exist', 'Error', true);
    }
    $db->query('SELECT COUNT(id) FROM ganginvites WHERE gangid = ? AND playerid = ?');
    $db->execute([$_GET['delete'], $user_class->id]);
    if (!$db->result()) {
        echo Message('That invite doesn\'t exist', 'Error', true);
    }
    $gang_class = new Gang($_GET['delete']);
    $db->query('DELETE FROM ganginvites WHERE playerid = ? AND gangid = ?');
    $db->execute([$user_class->id, $_GET['delete']]);
    echo Message('You\'ve abstained from joining '.$gang_class->formattedname);
}
$db->query('SELECT id, gangid FROM ganginvites WHERE playerid = ? ORDER BY gangid ');
$db->execute([$user_class->id]);
$rows = $db->fetch();
?><tr>
    <th class="content-head">Gang Invitations</th>
</tr><?php
if ($rows === null) {
    echo Message('You have no pending gang invites', 'Error', true);
}
$csrfg = csrf_create('csrfg', false);
foreach ($rows as $row) {
    $invite_class = new Gang($row['gangid']); ?><tr>
        <td class="content">
            <?php echo $invite_class->formattedname; ?> -
            <a href="ganginvites.php?accept=<?php echo $row['gangid']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Accept</a> &middot;
            <a href="ganginvites.php?delete=<?php echo $row['gangid']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Delete</a>
        </td>
    </tr><?php
}
