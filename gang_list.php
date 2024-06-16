<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id FROM gangs ORDER BY experience DESC');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Gang List</th>
</tr>
<tr>
    <td class="content">
        <table width='100%'>
            <tr>
                <td>Rank</td>
                <td>Gang</td>
                <td>Members</td>
                <td>Leader</td>
                <td>Level</td>
            </tr><?php
if ($rows !== null) {
        $rank = 1;
        foreach ($rows as $row) {
            $gang = new Gang($row['id'], true); ?><tr>
                    <td><?php echo $rank; ?></td>
                    <td><?php echo $gang->formattedname; ?></td>
                    <td><?php echo format($gang->members); ?></td>
                    <td><?php echo $gang->formattedleader; ?></td>
                    <td><?php echo format($gang->level); ?></td>
                </tr><?php
        ++$rank;
        }
    } else {
        ?><tr>
                    <td colspan="5" class="center">There are no gangs</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
