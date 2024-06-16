<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT * FROM (
    (SELECT COUNT(id) AS total FROM users) AS total,
    (SELECT COUNT(id) AS upgraded FROM users WHERE rmdays > 0) AS upgraded)');
$db->execute();
$stats = $db->fetch(true);
?><tr>
    <th class="content-head">World Stats (more will be added soon)</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" cellpadding="4" cellspacing="0">
            <tr>
                <td class="textl" width="15%">Mobsters:</td>
                <td class="textr" width="35%"><?php echo format($stats['total']); ?></td>
                <td class="textl" width="15%">Respected Mobsters:</td>
                <td class="textr" width="35%"><?php echo format($stats['upgraded']); ?></td>
           </tr>
        </table>
    </td>
</tr>
