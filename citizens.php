<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id, lastactive FROM users ORDER BY id ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Total Users</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th width="5%">ID</th>
                    <th width="95%">Mobster</th>
                </tr>
            </thead><?php
foreach ($rows as $row) {
    $online = new User($row['id']); ?><tr>
                <td><?php echo $online->id; ?></td>
                <td><?php echo $online->formattedname; ?></td>
            </tr><?php
}
?></table>
    </td>
</tr>
