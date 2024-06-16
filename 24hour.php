<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id, UNIX_TIMESTAMP(lastactive) AS lastactive FROM users ORDER BY lastactive DESC'); // Get users online within the last 24 hours
$db->execute();
$rows = $db->fetch();
$lines = [];
?><tr>
    <th class="content-head">Users Online In The Last 24 Hours</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="45%">Mobster</th>
                    <th width="50%">Last Active</th>
                </tr>
            </thead><?php
foreach ($rows as $row) {
    $user_online = new User($row['id']); ?>
    <tr>
        <td><?php echo $user_online->id; ?></td>
        <td><?php echo $user_online->formattedname; ?></td>
        <td><?php echo howlongago($user_online->lastactive); ?></td>
    </tr><?php
}
?></table>
    </td>
</tr>
