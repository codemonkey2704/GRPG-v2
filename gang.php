<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
$gang_class = new Gang($user_class->gang);
if (array_key_exists('leave', $_GET)) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($gang_class->leader == $user_class->id) {
        echo Message('You can\'t leave your gang whilst you\'re the leader!', 'Error', true);
    }
    $db->query('UPDATE users SET gang = 0 WHERE id = ?');
    $db->execute([$user_class->id]);
    echo Message('You\'ve left '.$gang_class->formattedname, 'Error', true);
}
?><tr>
    <th class="content-head"><?php echo $gang_class->formattedname; ?></th>
</tr>
<tr>
    <td class="content"><?php echo format(nl2br($gang_class->description)); ?></td>
</tr><?php
if ($gang_class->leader == $user_class->id) {
    ?><tr>
        <th class="content-head">Gang Management</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="center">
                <tr>
                    <td width="33%"><a href="invite.php">Invite Player</a></td>
                    <td width="34%"><a href="managegang.php">Manage Gang Members</a></td>
                    <td width="33%"><a href="changedesc.php">Change Gang Message</a></td>
                </tr>
            </table>
        </td>
    </tr><?php
}
?><tr>
    <th class="content-head">Gang Actions</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="center">
            <tr>
                <td width="25%"><a href="viewgang.php?id=<?php echo $gang_class->id; ?>">View Gang</a></td>
                <td width="25%"><a href="gangarmory.php">Armory</a></td>
                <td width="25%"><a href="ganglog.php">Defense Log</a></td>
                <td width="25%"><a href="gangvault.php">Vault</a></td>
            </tr><?php
if ($user_class->id != $gang_class->leader) {
    ?><tr>
                    <td colspan="4" class="center"><a href="gang.php?leave&amp;csrfg=<?php echo csrf_create('csrfg', false); ?>">Leave Gang</a></td>
                </tr><?php
}
?></table>
    </td>
</tr>
