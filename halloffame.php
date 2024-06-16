<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['view'] = array_key_exists('view', $_GET) && in_array($_GET['view'], ['experience', 'strength', 'defense', 'speed', 'money', 'points']) ? $_GET['view'] : 'experience';
$db->query('SELECT id FROM users ORDER BY '.$_GET['view'].' DESC LIMIT 50');
$db->execute();
$rows = $db->fetch();
$rank = 0;
?><tr>
    <th class="content-head">Hall Of Fame</th>
</tr>
<tr>
    <td class="content">
        <a href="halloffame.php?view=experience"<?php echo $_GET['view'] === 'experience' ? ' class="bold"' : ''; ?>>Level</a> |
        <a href="halloffame.php?view=strength"<?php echo $_GET['view'] === 'strength' ? ' class="bold"' : ''; ?>>Strength</a> |
        <a href="halloffame.php?view=defense"<?php echo $_GET['view'] === 'defense' ? ' class="bold"' : ''; ?>>Defense</a> |
        <a href="halloffame.php?view=speed"<?php echo $_GET['view'] === 'speed' ? ' class="bold"' : ''; ?>>Speed</a> |
        <a href="halloffame.php?view=money"<?php echo $_GET['view'] === 'money' ? ' class="bold"' : ''; ?>>Money</a> |
        <a href="halloffame.php?view=points"<?php echo $_GET['view'] === 'points' ? ' class="bold"' : ''; ?>>Points</a>
    </td>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Mobster</th>
                    <th>Level</th>
                    <th>Money</th>
                    <th>Gang</th>
                    <th class="center">Online</th>
                </tr>
            </thead><?php
foreach ($rows as $row) {
    ++$rank;
    $user_hall = new User($row['id']); ?><tr>
                    <td><?php echo $rank; ?></td>
                    <td><?php echo $user_hall->formattedname; ?></td>
                    <td><?php echo $user_hall->level; ?></td>
                    <td><?php echo prettynum($user_hall->money, true); ?></td>
                    <td><?php echo $user_hall->formattedgang; ?></td>
                    <td><?php echo $user_hall->formattedonline; ?></td>
                </tr><?php
}
?></table>
    </td>
</tr>
