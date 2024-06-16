<?php
declare(strict_types=1);
define('STAFF_FILE', true);
global $parser;
require_once __DIR__.'/inc/header.php';
if (isset($_SESSION['msg'])) {
    echo Message($_SESSION['msg'], 'Success');
    unset($_SESSION['msg']);
}
?>
<tr>
    <th class="content-head">Support Desk</th>
</tr>
<tr>
    <td class="content"><?php
if ($user_class->admin != 1) {
    echo Message("You don't have access", 'Error', true);
}
require_once __DIR__.'/inc/jbbcode/Parser.php';
switch ($_GET['action']) {
    case 'respond':
        if (empty($_GET['id'])) {
            echo Message("You didn't supply a valid ticket ID", 'Warning');
            ticketIndex($db);
        } else {
            respondToTicket($db);
        }
        break;
    case 'view':
        if (empty($_GET['id'])) {
            echo Message("You didn't supply a valid ticket ID", 'Warning');
            ticketIndex($db);
        } else {
            viewTicket($db, $parser);
        }
        break;
    case 'status':
        if (empty($_GET['id'])) {
            echo Message("You didn't supply a valid ticket ID", 'Warning');
            ticketIndex($db);
        } else {
            changeTicketStatus($db);
        }
        break;
    case 'assign':
        if (empty($_GET['id'])) {
            echo Message("You didn't supply a valid ticket ID", 'Warning');
            ticketIndex($db);
        } else {
            assignTicketToUser($db, $user_class);
            ticketIndex($db);
        }
        break;
    case 'validation-email':
        if ($_GET['id'] === null) {
            echo Message('You didn\'t supply a valid ticket ID', 'Warning');
            ticketIndex($db);
        } else {
            handleValidationEmailFailure($db, $_GET['id'], $user_class);
        }
        break;
    default:
        ticketIndex($db);
        break;
} ?>
    </td>
</tr><?php

function ticketIndex($db)
{
    global $pages;
    $db->query('SELECT COUNT(id) FROM tickets');
    $db->execute();
    $cnt = $db->result();
    if (!$cnt) {
        echo Message('There are no tickets', 'Information', true);
    }
    $pages = new Paginator($cnt);
    $db->query("SELECT * FROM tickets ORDER BY FIELD(status, 'pending', 'open', 'closed') , id DESC " .$pages->limit);
    $db->execute(); ?><div class='paginate'><?php echo $pages->display_pages(); ?></div>
    <table class="pure-table pure-table-horizontal" width="100%">
        <tr>
            <th width="5%">ID</th>
            <th width="45%">Subject/Sender</th>
            <th width="25%">Date</th>
            <th width="25%">Status</th>
        </tr><?php
        if ($db->count()) {
            $rows = $db->fetch();
            foreach ($rows as $row) {
                $date = new DateTime($row['time_added']);
                $reporter = new User($row['userid']);
                $db->query('SELECT userid FROM ticketreplies WHERE ticketid = ? ORDER BY time_added DESC LIMIT 1');
                $db->execute([$row['id']]);
                if ($db->count()) {
                    $responder = new User($db->result());
                } else {
                    $responder = (object) ['formattedname' => 'No-one'];
                } ?><tr>
                    <td>#<?php echo format($row['id']); ?></td>
                    <td>
                        <a href="managetickets.php?action=view&amp;id=<?php echo $row['id']; ?>"><?php echo format($row['subject']); ?></a><br />
                        <strong>Reporter:</strong> <?php echo $reporter->formattedname; ?><br /><?php
                        if ($row['status'] !== 'closed') {
                            echo '<strong>Last Responder:</strong> '.$responder->formattedname;
                        } ?></td>
                    <td><?php echo $date->format('d F Y, g:i:sa'); ?></td>
                    <td><?php
                    if ($row['status'] !== 'closed') {
                        ?><form action="managetickets.php?action=status&amp;id=<?php echo $row['id']; ?>" method="post"><?php
                            $opts = [
                                'open' => '<span class="red">Open</span>',
                                'pending' => '<span class="orange">Pending</span>',
                                'closed' => '<span class="green-light">Closed</span>',
                            ];
                        foreach ($opts as $opt => $disp) {
                            printf("<input type='radio' name='status' value='%s'%s /> %s<br />", $opt, $opt == $row['status'] ? " checked='checked'" : '', $disp);
                        } ?><input type="submit" name="submit" value="Change Status" />
                        </form><?php
                    } else {
                        echo '<span class="green-light">Closed</span>';
                    } ?></td>
                </tr><?php
            }
        } else {
            echo "<tr><td colspan='4' class='center'>There are no tickets</td></tr>";
        } ?></table>
    <div class="paginate"><?php echo $pages->display_pages(); ?></div><?php
}
function changeTicketStatus($db)
{
    $db->query('SELECT status, userid FROM tickets WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('That ticket doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if ($row['status'] == $_POST['status']) {
        echo Message('That ticket has already been marked as '.ucfirst(strtolower($_POST['status'])), 'Error', true);
    }
    if (!in_array($_POST['status'], ['open', 'pending', 'closed'])) {
        echo Message('You didn\'t select a valid status', 'Error', true);
    }
    $db->trans('start');
    $db->query('UPDATE tickets SET status = ? WHERE id = ?');
    $db->execute([$_POST['status'], $_GET['id']]);
    Send_Event($row['userid'], 'Your ticket (<a href="tickets.php?action=view&amp;id='.$_GET['id'].'">ID #'.format($_GET['id']).'</a>) has been marked as '.ucfirst(strtolower($_POST['status'])));
    $_SESSION['msg'] = 'You\'ve marked ticket ID #'.$_GET['id'].' as '.ucfirst(strtolower($_POST['status']));
    $db->query('INSERT INTO ticketreplies (ticketid, userid, body) VALUES (?, 0, ?)');
    $db->execute([$_GET['id'], 'Ticket marked as '.strtolower($_POST['status']).' by staff']);
    $db->trans('end');
    if (isset($_GET['view'])) {
        header('Location: managetickets.php'.($_POST['status'] !== 'closed' ? '?action=view&id='.$_GET['id'] : ''));
    } else {
        header('Location: managetickets.php');
    }
}
function viewTicket($db, $parser)
{
    $db->query('SELECT * FROM tickets WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('That ticket doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    $reporter = new User($row['userid']);
    $db->query('SELECT * FROM ticketreplies WHERE ticketid = ? ORDER BY id DESC');
    $db->execute([$_GET['id']]);
    $rows = $db->fetch(); ?><table id='mttable' width='100%'>
        <tr>
            <th colspan="2"><?php echo format($row['subject']); ?> (by <?php echo $reporter->formattedname; ?>) [<a href="managetickets.php?action=assign&amp;id=<?php echo $_GET['id']; ?>">Reassign ticket</a>]</th>
        </tr>
        <tr>
            <td colspan="2"><?php echo format($row['body'], true); ?></td>
        </tr>
        <tr>
            <th>Mark Ticket</th>
            <td>
                <form action="managetickets.php?action=status&amp;id=<?php echo $_GET['id']; ?>&amp;view=true" method="post" class="pure-form">
                    <div class="pure-control-group">
                        <label for="status">Status</label><?php
                        $opts = [
                            'open' => "<span style='color:red;'>Open</span>",
                            'pending' => "<span style='color:orange;'>Pending</span>",
                            'closed' => "<span style='color:green;'>Closed</span>",
                        ];
    foreach ($opts as $opt => $disp) {
        printf('<input type="radio" name="status" value="%s"%s /> %s&nbsp;&nbsp;', $opt, $opt == $row['status'] ? ' checked' : '', $disp);
    } ?>&nbsp;&nbsp;
                    </div>
                    <div class="pure-controls">
                        <button type="submit" name="submit" class="pure-button pure-button-primary">Change Status</button>
                    </div>
                </form>
            </td>
        </tr>
        <tr>
            <th width="25%">Response</th>
            <td width="75%">
                <form action="managetickets.php?action=respond&amp;id=<?php echo $_GET['id']; ?>" method="post" class="pure-form pure-form-aligned">
                    <div class="pure-control-group">
                        <label for="response">Response</label>
                        <textarea name="response" id="response" rows="12" cols="70"></textarea>
                    </div>
                    <div class="pure-controls">
                        <button type="submit" name="submit" class="pure-button pure-button-primary">Respond</button>
                    </div>
                </form>
            </td>
        </tr>
        <tr>
            <th colspan="2">Responses</th>
        </tr>
        <tr>
            <th>Responder</th>
            <th>Message</th>
        </tr><?php
        if ($rows !== null) {
            $cache = [];
            foreach ($rows as $row) {
                $date = new DateTime($row['time_added']);
                if ($row['userid']) {
                    if (array_key_exists($row['userid'], $cache)) {
                        $responder = $cache[$row['userid']];
                    } else {
                        $responder = new User($row['userid']);
                        $cache[$row['userid']] = $responder;
                    }
                    $parser->parse(format($row['body'], true)); ?><tr>
                        <td>
                            <a href="profiles.php?id=<?php echo $responder->id; ?>"><img height="100" width="100" style="border:1px solid #000000" src="<?php echo $responder->avatar; ?>" /></a><br /><?php echo $responder->formattedname; ?><br />
                            <strong>Date:</strong> <?php echo $date->format('d F Y, g:i:sa'); ?>
                        </td>
                        <td><?php echo $parser->getAsHTML(); ?></td>
                    </tr><?php
                } else {
                    ?><tr>
                        <td colspan="2" class="center info"><?php echo format($row['body'], true); ?> at <?php echo $date->format('H:i:s'); ?> on <?php echo $date->format('d/m/Y'); ?></td>
                    </tr><?php
                }
            }
        } else {
            echo "<tr><td colspan='2' class='center'>There are no responses</td></tr>";
        } ?></table><?php
}
function respondToTicket($db)
{
    global $user_class;
    $db->query('SELECT userid, status FROM tickets WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('That ticket doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    $db->trans('start');
    $db->query('INSERT INTO ticketreplies (ticketid, userid, body) VALUES (?, ?, ?)');
    $db->execute([$_GET['id'], $user_class->id, $_POST['response']]);
    if ($row['status'] === 'open') {
        $db->query('UPDATE tickets SET status = \'pending\' WHERE id = ?');
        $db->execute([$_GET['id']]);
        $db->query('INSERT INTO ticketreplies (ticketid, userid, body) VALUES (?, 0, ?)');
        $db->execute([$_GET['id'], 'Ticket automatically marked as pending by staff response']);
    }
    $db->query('UPDATE tickets SET time_last_response = NOW() WHERE id = ?');
    $db->execute([$_GET['id']]);
    Send_Event($row['userid'], 'You\'ve received a response to your ticket (<a href="tickets.php?action=view&amp;id='.$_GET['id'].'">ID #'.$_GET['id'].'</a>)');
    $db->trans('end');
    $_SESSION['msg'] = 'You\'ve responded to ticket ID #'.$_GET['id'];
    header('Location: managetickets.php');
}
function assignTicketToUser($db, $user_class)
{
    $db->query('SELECT id, status, userid FROM tickets WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message("That ticket doesn't exist", 'Error', true);
    }
    $row = $db->fetch(true);
    if (array_key_exists('submit', $_POST)) {
        $_POST['user'] = isset($_POST['user']) && ctype_digit($_POST['user']) ? $_POST['user'] : null;
        if (empty($_POST['user'])) {
            echo Message("You didn't select a valid player", 'Error', true);
        }
        $db->query('SELECT id FROM users WHERE id = ?');
        $db->execute([$_POST['user']]);
        if (!$db->count()) {
            echo Message('That player doesn\'t exist', 'Error', true);
        }
        $user = new User($_POST['user']);
        if ($_POST['user'] == $row['userid']) {
            echo Message('That ticket has already been assigned to '.$user->formattedname, 'Error', true);
        }
        $db->query('UPDATE tickets SET userid = ? WHERE id = ?');
        $db->execute([$_POST['user'], $_GET['id']]);
        Send_Event($_POST['user'], 'Ticket ID #'.format($_GET['id']).' has been assigned to you by {extra}', $user_class->id);
        echo Message('You\'ve assigned ticket ID #'.format($_GET['id']).' to '.$user->formattedname, 'Success');
    } else {
        $db->query('SELECT id, username FROM users ORDER BY id ');
        $db->execute();
        $users = $db->fetch(); ?>
        <form action="managetickets.php?action=assign&amp;id=<?php echo $_GET['id']; ?>" method="post">
            <div class="pure-control-group">
                <label for="user">Assign to</label>
                <select name="user" id="user"><?php
                    foreach ($users as $user) {
                        printf("<option value='%u'%s>%s</option>", $user['id'], $user['id'] == $row['userid'] ? ' selected' : '', format($user['username']));
                    } ?>
                </select>
            </div>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Re-assign ticket</button>
            </div>
        </form><?php
    }
}
function handleValidationEmailFailure($db, $ticketID, $my)
{
    $db->query('SELECT id, userid, subject, body, status FROM tickets WHERE id = ?');
    $db->execute([$ticketID]);
    $row = $db->fetch(true);
    if ($row === null) {
        echo Message('The ticket you selected doesn\'t exist', 'Error', true);
    }
    if (!in_array($row['status'], ['open', 'pending'])) {
        echo Message('This ticket has already been handled', 'Error', true);
    }
    if ($row['subject'] !== 'Failed to send validation email') {
        echo Message('That ticket can\'t be handled like this', 'Error', true);
    }
    $target = new User($row['userid']);
    $_GET['sub'] = array_key_exists('sub', $_GET);
    if ($_GET['sub'] === 'decide') {
        $_GET['which'] = array_key_exists('which', $_GET) && in_array($_GET['which'], ['accept', 'decline']) ?? null;
        if ($_GET['which'] === null) {
            echo Message('You didn\'t make a valid decision', 'Error', true);
        }
        $db->trans('start');
        if ($_GET['which'] === 'decline') {
            $what = 'declined';
            $db->query('INSERT INTO ticketreplies (userid, ticketid, body) VALUES (?, ?, \'Validation declined\')');
            $db->execute([$my->id, $ticketID]);
        } else {
            $what = 'accepted';
            $db->query('INSERT INTO ticketreplies (userid, ticketid, body) VALUES (?, ?, \'Validation accepted and processed\')');
            $db->execute([$my->id, $ticketID]);
        }
        $db->query('UPDATE tickets SET status = \'closed\' WHERE id = ?');
        $db->execute([$ticketID]);
        $db->trans('end');
        echo Message('You\'ve '.$what.' the email validation from '.$target->formattedname, 'Success');
    }
    ticketIndex($db);
}
