<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id, lastactive, hhow, hwhen, hwho FROM users WHERE hospital > 0 ORDER BY hospital DESC');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Hospital</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th>Mobster</th>
                    <th>Care Time</th>
                    <th>Reason</th>
                    <th>Hospitalized</th>
                </tr>
            </thead><?php
if ($rows !== null) {
        foreach ($rows as $row) {
            $user_hospital = new User($row['id']);
            $user_attacker = $row['hwho'] ? new User($row['hwho']) : (object) ['formattedname' => '<em>Unknown</em>'];
            $how = $row['hhow'] === 'wasattacked' ? 'Was attacked by' : 'Attacked'; ?><tr>
                    <td><?php echo $user_hospital->formattedname; ?></td>
                    <td><?php echo time_format($user_hospital->hospital); ?></td>
                    <td><?php echo $how.' '.$user_attacker->formattedname; ?> and lost</td>
                    <td><?php echo $row['hwhen']; ?></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="4" class="center">There's no-one in hospital</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
