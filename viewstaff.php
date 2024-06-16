<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id FROM users WHERE admin > 0 ORDER BY admin , id ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Town Hall (Staff)</th>
</tr>
<tr>
    <td class="content"><?php
foreach ($rows as $row) {
    $staff = new User($row['id']); ?><div><?php echo $staff->formattedname; ?></div><?php
}
?></td>
</tr>
