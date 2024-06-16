<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id FROM users WHERE lastactive >= DATE_SUB(NOW(), INTERVAL 5 MINUTE) ORDER BY lastactive DESC');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Users Online In The Last 5 Minutes</th>
</tr>
<tr>
    <td class="content">
        <table width="100%">
            <thead>
                <tr>
                    <th width="50%">Mobster</th>
                    <th width="50%">Last Active</th>
                </tr>
            </thead><?php
foreach ($rows as $row) {
    $user_online = new User($row['id']); ?>
    <tr>
        <td><?php echo $user_online->formattedname; ?></td>
        <td><?php echo howlongago($user_online->lastactive); ?></td>
    </tr><?php
}
?></table>
    </td>
</tr>
