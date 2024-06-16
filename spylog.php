<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT * FROM spylog WHERE id = ? ORDER BY age DESC LIMIT 25');
$db->execute([$user_class->id]);
$rows = $db->fetch();
?><tr>
    <th class="content-head">Spy Log</th>
</tr>
<tr>
    <td class="content">
        <table width="100%">
            <tr>
                <td>When</td>
                <td>Username</td>
                <td>Strength</td>
                <td>Defense</td>
                <td>Speed</td>
                <td>Bank</td>
                <td>Points</td>
            </tr><?php
if ($rows !== null) {
        foreach ($rows as $row) {
            $profile_class = new User($row['spyid']); ?><tr>
                    <td><?php echo howlongago($row['age']); ?></td>
                    <td><?php echo $profile_class->formattedname; ?></td>
                    <td><?php echo $row['strength'] > -1 ? format($row['strength']) : 'Failed'; ?></td>
                    <td><?php echo $row['defense'] > -1 ? format($row['defense']) : 'Failed'; ?></td>
                    <td><?php echo $row['speed'] > -1 ? format($row['speed']) : 'Failed'; ?></td>
                    <td><?php echo $row['bank'] > -1 ? format($row['bank']) : 'Failed'; ?></td>
                    <td><?php echo $row['points'] > -1 ? format($row['points']) : 'Failed'; ?></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="7" class="center">You haven't hired a Private Investigator yet</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
