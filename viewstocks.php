<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id, company_name, cost FROM stocks ORDER BY id ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <td class="content" align="center">
        <img src="images/stock-market.png" />
    </td>
</tr>
<tr>
    <th class="content-head">View Stock Market</th>
</tr>
<tr>
    <td class="content">
        <table width="100%">
            <tr>
                <th width="5%">ID</th>
                <th width="70%">Company Name</th>
                <th width="25%">Cost per Share</th>
            </tr><?php
if ($rows !== null) {
        foreach ($rows as $row) {
            ?><tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo format($row['company_name']); ?></td>
                    <td><?php echo prettynum($row['cost'], true); ?></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="3" class="center">There are no stocks</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
