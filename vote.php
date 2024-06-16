<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!empty($_GET['id'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT COUNT(id) FROM users_votes WHERE site = ? AND userid = ?');
    $db->execute([$_GET['id'], $user_class->id]);
    if ($db->result()) {
        echo Message('You\'ve already voted on this site', 'Error', true);
    }
    $db->query('SELECT * FROM voting_sites WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('The site you selected doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if ($row['req_account_days_min'] && $row['req_account_days_min'] > ($user_class->age_int / 86400)) {
        echo Message('You\'re not old enough to use this voting site (account age)', 'Error', true);
    }
    if ($row['req_account_days_max'] && $row['req_account_days_max'] < ($user_class->age_int / 86400)) {
        echo Message('No offense meant, but you\'re too old to use this voting site (account age)', 'Error', true);
    }
    if ($row['req_donator_days'] && $row['req_donator_days'] > $user_class->rmdays) {
        echo Message('You don\'t have enough donation status time left to use this voting site', 'Error', true);
    }
    $query = '';
    if ($row['reward_cash']) {
        $query .= ', money = money + '.$row['reward_cash'];
    }
    if ($row['reward_points']) {
        $query .= ', points = points + '.$row['reward_points'];
    }
    if ($row['reward_donator_days']) {
        $query .= ', rmdays = rmdays + '.$row['reward_rmdays'];
    }

    $db->trans('start');
    if ($query) {
        $db->query('UPDATE users SET '.substr($query, 2).' WHERE id = ?');
        $db->execute([$user_class->id]);
    }
    if ($row['reward_items']) {
        $items = explode(',', $row['reward_items']);
        foreach ($items as $item) {
            [$itemID, $qty] = explode(':', $item);
            Give_Item($itemID, $user_class->id, $qty);
        }
    }
    $db->query('INSERT INTO users_votes (userid, site) VALUES (?, ?)');
    $db->execute([$user_class->id, $_GET['id']]);
    $db->trans('end');
    exit(header('Location: '.format($row['url'])));
}
?><h3 class="centre">Voting Sites</h3><?php
$voted = [];
$db->query('SELECT site FROM users_votes WHERE userid = ?');
$db->execute([$user_class->id]);
$sites = $db->fetch();
foreach ($sites as $vote) {
    $voted[] = $vote['site'];
}
$voted = array_unique($voted);
$db->query('SELECT * FROM voting_sites ORDER BY id ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Voting</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th width="20%">Site</th>
                    <th width="40%">Rewards</th>
                    <th width="40%">Requirements</th>
                </tr>
            </thead><?php
if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            $rewards = [];
            if ($row['reward_cash']) {
                $rewards[] = prettynum($row['reward_cash'], true);
            }
            if ($row['reward_crystals']) {
                $rewards[] = prettynum($row['reward_points']);
            }
            if ($row['reward_items']) {
                $items = explode(',', $row['reward_items']);
                foreach ($items as $item) {

                    [$itemID, $qty] = explode(':', $item);
                    $db->query('SELECT name FROM items WHERE id = ?', [$itemID]);
                    $it = $db->result();
                    $rewards[] = format($qty).'x '.$it;
                }
            }
            if ($row['reward_donator_days']) {
                $rewards[] = 'Donator Status Time: '.time_format($row['reward_donator_days'] * 86400);
            }
            //----------------------
            $reqs = [];
            if ($row['req_account_days_min']) {
                $reqs[] = 'Account age: at least '.time_format($row['req_account_days_min'] * 86400).' old';
            }
            if ($row['req_account_days_max']) {
                $reqs[] = 'Account age: at most '.time_format($row['req_account_days_max'] * 86400).' old';
            }
            if ($row['req_donator_days']) {
                $reqs[] = 'Donator Status Time: at least '.time_format($row['req_donator_days'] * 86400).' left';
            } ?><tr>
                    <td><?php echo !count($voted) || !in_array($row['id'], $voted) ? '<a href="vote.php?id='.$row['id'].'&amp;csrfg='.$csrfg.'" target="new" class="pure-button pure-button-green">'.format($row['title']).'</a>' : '<button class="pure-button pure-button-grey" disabled>'.format($row['title']).'</button>'; ?></td>
                    <td><?php echo count($rewards) ? implode('<br />', $rewards) : 'None'; ?></td>
                    <td><?php echo count($reqs) ? implode('<br />', $reqs) : 'None'; ?></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="3" class="centre">There are no voting sites</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
