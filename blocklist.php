<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->rmdays) {
    echo Message('You don\'t have access', 'Error', true);
}
?><tr>
    <th class="content-head">Block List</th>
</tr><?php
if (array_key_exists('errors_blocklist', $_SESSION)) {
    display_errors($_SESSION['errors_blocklist']);
    unset($_SESSION['errors_blocklist']);
}
$_GET['action'] = array_key_exists('action', $_GET) && ctype_alpha($_GET['action']) ? $_GET['action'] : null;
switch ($_GET['action']) {
    case 'add':
        addListing($db, $user_class);
    break;
    case 'edit':
        editListing($db, $user_class);
    break;
    case 'del':
    case 'delete':
        deleteListing($db, $user_class);
    break;
    default:
        blockListIndex($db, $user_class);
    break;
}
function blockListIndex($db, $user_class)
{
    global $set;
    $db->query('SELECT id, blocked_id, comment, time_added FROM users_blocked WHERE userid = ? ORDER BY time_added ');
    $db->execute([$user_class->id]);
    $rows = $db->fetch(); ?><tr>
        <td class="content">
            <table class="pure-table pure-table-horizontal">
                <thead>
                    <tr>
                        <th width="40%">Blocked <span class="right"><a href="blocklist.php?action=add" class="pure-button pure-button-green" title="Block Mobster"><i class="fa fa-plus"></i></a></th>
                        <th width="40%">Comment</th>
                        <th width="20%">Actions</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            $blocked = new User($row['blocked_id']);
            $date = new DateTime($row['time_added']); ?><tr>
                        <td>
                            <?php echo $blocked->formattedname; ?><br />
                            <span class="small italic">(blocked: <?php echo $date->format('F d, Y H:i:s'); ?>)</span>
                        </td>
                        <td><?php echo $row['comment'] ? nl2br(format($row['comment'])) : '<em>None</em>'; ?></td>
                        <td>
                            <a href="blocklist.php?action=edit&amp;id=<?php echo $row['id']; ?>" class="pure-button pure-button-yellow"><i class="fa fa-pencil"></i></a>
                            <a href="blocklist.php?action=del&amp;id=<?php echo $row['id']; ?>" class="pure-button pure-button-red"><i class="fa fa-trash"></i></a>
                        </td>
                    </tr><?php
        }
    } else {
        echo '<tr><td colspan="3" class="centre">You haven\'t blocked anyone</td></tr>';
    } ?></table>
        </td>
    </tr><?php
}
function addListing($db, $user_class)
{
    if (array_key_exists('submit', $_POST)) {
        if (!csrf_check('add', $_POST)) {
            $_SESSION['errors_blocklist'][] = SECURITY_TIMEOUT_MESSAGE;
            exit(header('Location: blocklist.php?action=add'));
        }
        $errors = [];
        $_POST['comment'] = array_key_exists('comment', $_POST) && is_string($_POST['comment']) ? trim($_POST['comment']) : '';
        $_POST['user'] = array_key_exists('user', $_POST) && ctype_digit($_POST['user']) ? $_POST['user'] : null;
        if (empty($_POST['user'])) {
            $errors[] = 'You didn\'t select a valid mobster';
        }
        if ($_POST['user'] == $user_class->id) {
            $errors[] = 'You can\'t block yourself :/';
        }
        $db->query('SELECT id, admin FROM users WHERE id = ?');
        $db->execute([$_POST['user']]);
        if (!$db->count()) {
            $errors[] = 'The mobster you selected doesn\'t exist';
        }
        $row = $db->fetch(true);
        $target = new User($_POST['user']);
        if ((int)$row['admin'] === 1) {
            $errors[] = 'You can\'t block staff members';
        }
        $db->query('SELECT COUNT(id) FROM users_blocked WHERE userid = ? AND blocked_id = ?');
        $db->execute([$user_class->id, $_POST['user']]);
        if ($db->result()) {
            $errors[] = 'You\'ve already blocked '.$target->formattedname;
        }
        if (count($errors)) {
            $_SESSION['errors_blocklist'] = $errors;
            exit(header('Location: blocklist.php?action=add'));
        }

        $db->query('INSERT INTO users_blocked (userid, blocked_id, comment) VALUES (?, ?, ?)');
        $db->execute([$user_class->id, $_POST['user'], $_POST['comment']]);
        echo Message($target->formattedname.' has been added to your block list - don\'t worry, they haven\'t been informed');
        exit(blockListIndex($db, $user_class));
    } ?><tr>
        <td class="content">
            <form action="blocklist.php?action=add" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('add'); ?>
                <div class="pure-control-group">
                    <label for="user">Mobster</label>
                    <?php echo listMobsters('user', $_GET['id'], [$user_class->id]); ?>
                </div>
                <div class="pure-control-group">
                    <label for="comment">Comment</label>
                    <textarea name="comment" id="comment" rows="5" cols="40" placeholder="Optional"></textarea>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="submit" class="pure-button pure-buttuon-primary">Block Mobster</button>
                </div>
            </form>
        </td>
    </tr><?php
}
function editListing($db, $user_class)
{
    $errors = [];
    if (empty($_GET['id'])) {
        $errors[] = 'You didn\'t select a valid listing';
    }
    $db->query('SELECT id, userid, blocked_id, comment FROM users_blocked WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        $errors[] = 'The listing you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $blocked = new User($row['blocked_id']);
    if ($row['userid'] != $user_class->id) {
        $errors[] = 'That listing doesn\'t belong to you';
    }
    if (count($errors)) {
        $_SESSION['errors_blocklist'] = $errors;
        exit(header('Location: blocklist.php'.(!empty($_GET['action']) ? '?action='.$_GET['action'] : '')));
    }

    if (array_key_exists('submit', $_POST)) {
        if (!csrf_check('edit', $_POST)) {
            $_SESSION['errors_blocklist'][] = SECURITY_TIMEOUT_MESSAGE;
            exit(header('Location: blocklist.php?action='.(!empty($_GET['action']) ? '?action='.$_GET['action'] : '')));
        }
        $_POST['comment'] = array_key_exists('comment', $_POST) && is_string($_POST['comment']) ? trim($_POST['comment']) : '';
        $db->query('UPDATE users_blocked SET comment = ? WHERE id = ?');
        $db->execute([$_POST['comment'], $_GET['id']]);
        echo Message('You\'ve updated your comment for '.$blocked->formattedname);
        exit(blockListIndex($db, $user_class));
    } ?>
    <tr>
        <td class="content">
            Updating your comment on <?php echo $blocked->formattedname; ?><br />
            <form action="blocklist.php?action=edit&amp;id=<?php echo $_GET['id']; ?>" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('edit'); ?>
                    <div class="pure-control-group">
                    <label for="comment">Comment</label>
                    <textarea name="comment" id="comment" rows="5" cols="40" placeholder="Optional"><?php echo format($row['comment']); ?></textarea>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="submit" class="pure-button pure-button-primary">Update Comment</button>
                </div>
            </form>
        </td>
    </tr><?php
}
function deleteListing($db, $user_class)
{
    $errors = [];
    if (empty($_GET['id'])) {
        $errors[] = 'You didn\'t select a valid listing';
    }
    $db->query('SELECT id, userid, blocked_id FROM users_blocked WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        $errors[] = 'The listing you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $blocked = new User($row['blocked_id']);
    if ($row['userid'] != $user_class->id) {
        $errors[] = 'That listing doesn\'t belong to you';
    }
    if (count($errors)) {
        $_SESSION['errors_blocklist'] = $errors;
        exit(header('Location: blocklist.php'));
    }

    if (array_key_exists('ans', $_GET)) {
        if (!csrf_check('csrf', $_GET)) {
            $_SESSION['errors_blocklist'][] = SECURITY_TIMEOUT_MESSAGE;
            exit(header('Location: blocklist.php?action='.(!empty($_GET['action']) ? '?action='.$_GET['action'] : '')));
        }
        $db->query('DELETE FROM users_blocked WHERE id = ?');
        $db->execute([$_GET['id']]);
        echo Message('You\'ve removed '.$blocked->formattedname.' from your blocklist');
    } else {
        ?><tr>
            <td class="content">
                Are you sure you want to remove <?php echo $blocked->formattedname; ?> from your blocklist?<br />
                <a href="blocklist.php?action=del&amp;id=<?php echo $_GET['id']; ?>&amp;ans=yes&amp;csrf=<?php echo csrf_create('csrf', false); ?>" class="pure-button pure-button-primary"><i class="fa fa-thumbs-up" aria-hidden="true"></i> Yes, I'm sure</a> &middot; <a href="blocklist.php">No, go back</a>
            </td>
        </tr><?php
    }
    blockListIndex($db, $user_class);
}
?>  </td>
</tr>
