<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
require_once __DIR__.'/inc/page.class.php';
$_GET['action'] = array_key_exists('action', $_GET) && in_array($_GET['action'], ['add', 'view', 'status']) ? $_GET['action'] : null;
$errors = [];
$status = [
    'open' => '<span class="red">Open</span>',
    'pending' => '<span class="yellow">Pending</span>',
    'closed' => '<span class="green">Closed</span>',
    'locked' => '<span class="gray">Locked</span>',
];
?><tr>
    <th class="content-head">Support Tickets</th>
</tr>
<tr>
    <td class="content">
        Got an issue? Have you found a bug? Does something seem "off"?<br />
        If so, you're in the right place. Simply open a ticket and we'll do what we can to resolve your situation<br /><br />
        <a href="tickets.php?action=add">Click here to open a ticket</a>
    </td>
</tr><?php
if (empty($_GET['action'])) {
    $db->query('SELECT COUNT(id) FROM tickets WHERE userid = ?');
    $db->execute([$user_class->id]);
    $cnt = $db->result();
    if (!$cnt) {
        echo Message('You haven\'t opened any tickets', 'Error', true);
    }
    $pages = new Paginator($cnt);
    $db->query('SELECT id, subject, status, time_added FROM tickets WHERE userid = ? ORDER BY FIELD(status, \'pending\', \'open\', \'closed\', \'locked\') , time_added DESC' .$pages->limit);
    $db->execute([$user_class->id]);
    $rows = $db->fetch(); ?><tr>
        <td class="content">
            <?php echo $pages->display_pages(); ?>
            <table class="pure-table" width="100%">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="34%">Ticket</th>
                        <th width="33%">Time Opened</th>
                        <th width="28%">Status</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            $date = new DateTime($row['time_added']); ?><tr>
                        <td><?php echo format($row['id']); ?></td>
                        <td><a href="tickets.php?action=view&amp;id=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><?php echo format($row['subject']); ?></a></td>
                        <td><?php echo $date->format('F d, Y, H:i:s'); ?></td>
                        <td><?php echo $status[$row['status']]; ?></td>
                    </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="4" class="centre">There are no tickets on this page</td>
                </tr><?php
    } ?></table>
            <?php echo $pages->display_pages(); ?>
        </td>
    </tr><?php
} elseif ($_GET['action'] === 'view') {
        if (empty($_GET['id'])) {
            $errors[] = 'You didn\'t select a valid ticket';
        }
        $db->query('SELECT id, subject, body, status, time_added, userid FROM tickets WHERE id = ?');
        $db->execute([$_GET['id']]);
        if (!$db->count()) {
            $errors[] = 'The ticket you selected doesn\'t exist';
        }
        $ticket = $db->fetch(true);
        if ($ticket['userid'] != $user_class->id) {
            $errors[] = 'That ticket doesn\'t belong to you';
        }
        if (array_key_exists('submit', $_POST)) {
            if (!csrf_check('csrf', $_POST)) {
                echo Message(SECURITY_TIMEOUT_MESSAGE);
            }
            $_POST['response'] = array_key_exists('response', $_POST) && is_string($_POST['response']) ? strip_tags(trim($_POST['response'])) : null;
            if (empty($_POST['response'])) {
                $errors[] = 'You didn\'t enter a valid response';
            }
            $db->query('SELECT COUNT(id) FROM tickets_responses WHERE ticket_id = ? AND body = ? AND userid = ? ORDER BY time_added DESC LIMIT 1');
            $db->execute([$ticket['id'], $_POST['response'], $user_class->id]);
            if ($db->result()) {
                $errors[] = 'Double submission detected';
            }
            if (!count($errors)) {
                $db->query('INSERT INTO tickets_responses (userid, body, ticket_id) VALUES (?, ?, ?)');
                $db->execute([$user_class->id, $_POST['response'], $ticket['id']]);
                echo Message('Your response has been posted', 'Success');
            }
        }
        if (count($errors)) {
            display_errors($errors);
        } else {
            $db->query('SELECT userid, body FROM tickets_responses WHERE ticket_id = ? ORDER BY time_added DESC');
            $db->execute([$_GET['id']]);
            $rows = $db->fetch(); ?><tr>
            <th class="content-head">Ticket: #<?php echo $ticket['id']; ?> &middot; <?php echo format($ticket['subject']); ?></th>
        </tr>
        <tr>
            <td class="content"><?php echo nl2br(format($ticket['body'])); ?></td>
        </tr>
        <tr>
            <td class="content"><?php
        if (in_array($ticket['status'], ['open', 'pending'])) {
            ?><form action="tickets.php?action=view&amp;id=<?php echo $ticket['id']; ?>" method="post" class="pure-form pure-form-aligned">
                        <?php echo csrf_create(); ?>
                        <div class="pure-control-group">
                            <label for="response">Response</label>
                            <textarea name="response" id="response" rows="5" cols="70" placeholder="Please be as clear and concise as you can" required autofocus></textarea>
                        </div>
                        <div class="pure-controls">
                            <button type="submit" name="submit" class="pure-button pure-button-primary">Send Response</button>
                        </div>
                    </form><?php
        } ?><table class="pure-table pure-table-horizontal" width="100%">
                    <thead>
                        <tr>
                            <th width="25%">Poster</th>
                            <th width="25%">Response</th>
                        </tr>
                    </thead><?php
        if ($rows !== null) {
            foreach ($rows as $row) {
                $date = new DateTime($row['time_added']);
                $poster = $row['userid'] == $user_class->id ? $user_class : new User($row['userid']); ?><tr>
                            <td>
                                <?php echo $poster->formattedname; ?><br />
                                <?php echo $date->format('F d, Y H:i:s'); ?>
                            </td>
                            <td><?php echo nl2br(format($row['content'])); ?></td>
                        </tr><?php
            }
        } else {
            ?><tr>
                        <td colspan="2" class="center">There are no responses</td>
                    </tr><?php
        } ?></table>
            </td>
        </tr><?php
        }
    } elseif ($_GET['action'] === 'add') {
        ?><tr>
        <th class="content-head">Opening a support ticket</th>
    </tr><?php
    if (array_key_exists('submit', $_POST)) {
        if (!csrf_check('csrf', $_POST)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        $strs = ['subject', 'ticket'];
        foreach ($strs as $what) {
            $_POST[$what] = array_key_exists($what, $_POST) && is_string($_POST[$what]) ? strip_tags(trim($_POST[$what])) : null;
            if (empty($_POST[$what])) {
                $errors[] = 'You didn\'t enter a valid '.$what.' content';
            }
        }
        $db->query('SELECT COUNT(id) FROM tickets WHERE userid = ? AND subject = ? AND body = ?');
        $db->execute([$user_class->id, $_POST['subject'], $_POST['ticket']]);
        if ($db->result()) {
            $errors[] = 'You\'ve already submitted this ticket';
        }
        if (!count($errors)) {
            $db->query('INSERT INTO tickets (userid, subject, body) VALUES (?, ?, ?)');
            $db->execute([$user_class->id, $_POST['subject'], $_POST['ticket']]);
            echo Message('Your ticket has been created', 'Error', true);
        }
    }
        if (count($errors)) {
            display_errors($errors);
        } ?><tr>
        <td class="content">
            <form action="tickets.php?action=add" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create(); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="subject">Subject</label>
                        <input type="text" name="subject" id="subject" required autofocus />
                    </div>
                    <div class="pure-control-group">
                        <label for="ticket">Ticket</label>
                        <textarea name="ticket" id="ticket" rows="5" cols="53" placeholder="Be as clear and concise as possible. If you're reporting a bug, please include the URL and a description of what you were doing too - it'll help us rectify the bug faster" required></textarea>
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="submit" class="pure-button pure-button-primary">Open Ticket</button>
                </div>
            </form>
        </td>
    </tr><?php
    } elseif ($_GET['action'] === 'status') {
        if (empty($_GET['id'])) {
            $errors[] = 'You didn\'t select a valid ticket';
        }
        $db->query('SELECT id, subject, status, userid FROM tickets WHERE id = ?');
        $db->execute([$_GET['id']]);
        if (!$db->count()) {
            $errors[] = 'The ticket you selected doesn\'t exist';
        }
        $row = $db->fetch(true);
        if ($row['userid'] != $user_class->id) {
            $errors[] = 'That ticket doesn\'t belong to you';
        }
        if (!in_array($row['status'], ['open', 'pending', 'closed'])) {
            $errors[] = 'You can\'t change the status of this ticket';
        }
        if (!count($errors)) {
            $status = $row['status'] === 'closed' ? 'open' : 'close';
            $status2 = $row['status'] === 'closed' ? 'opened' : 'closed';
            if (array_key_exists('ans', $_GET)) {
                if (!csrf_check('csrfg', $_GET)) {
                    echo Message(SECURITY_TIMEOUT_MESSAGE);
                }
                $db->trans('start');
                $db->query('INSERT INTO tickets_responses (userid, body) VALUES (0, \'Ticket '.$status2.' by user\')');
                $db->execute();
                $db->query('UPDATE tickets SET status = IF(status = \'closed\', \'open\', \'closed\') WHERE id = ?');
                $db->execute([$row['id']]);
                $db->trans('end');
                echo Message('Ticket ID #'.$row['id'].' has been marked as '.$status2);
            } else {
                ?><tr>
                <td class="content">
                    Are you sure you want to <?php echo $status; ?> ticket #<?php echo $row['id']; ?>: <?php echo format($row['subject']); ?>?<br />
                    <a href="tickets.php?action=status&amp;id=<?php echo $row['id']; ?>&amp;ans=yes&amp;csrfg=<?php echo csrf_create('csrfg', false); ?>" class="pure-button pure-button-primary">Yes, I'm sure</a>
                </td>
            </tr><?php
            }
        }
    }
