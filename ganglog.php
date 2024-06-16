<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
$gang_class = new Gang($user_class->gang);
$db->query('SELECT * FROM attlog WHERE gangid = ? ORDER BY time_added DESC');
$db->execute([$user_class->gang]);
$rows = $db->fetch();
?><tr>
    <th class="content-head"><?php echo $gang_class->formattedname; ?> Defense Log</th>
</tr>
<tr>
    <td class="content"><?php
if ($rows !== null) {
        $cache = [];
        foreach ($rows as $row) {
            if (!array_key_exists($row['attacker'], $cache)) {
                $att = new User($row['attacker']);
                $cache[$row['attacker']] = $att->formattedname;
            }
            if (!array_key_exists($row['defender'], $cache)) {
                $def = new User($row['defender']);
                $cache[$row['defender']] = $def->formattedname;
            }
            if (!array_key_exists($row['winner'], $cache)) {
                $win = new User($row['winner']);
                $cache[$row['winner']] = $win->formattedname;
            }
            $date = new DateTime($row['time_added']);
            echo $cache[$row['attacker']].' attacked '.$cache[$row['defender']].' and '.$cache[$row['winner']].' won - '.$date->format(DEFAULT_DATE_FORMAT).'<br />';
        }
    } else {
        ?>There have been no attacks<?php
    }
?></td>
</tr>
