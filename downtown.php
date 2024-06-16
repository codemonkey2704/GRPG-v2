<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->searchdowntown) {
    echo Message('You\'ve already searched down town as much as you can today', 'Error', true);
}
?><tr>
    <th class="content-head">Search Downtown</th>
</tr>
<tr>
    <td class="content"><?php
$total = 0;
$ret = '';
for ($i = 1; $i <= 100; ++$i) {
    $rand = mt_rand(0, 20);
    $total += $rand;
    $ret .= $i.') '.($rand ? 'You found '.prettynum($rand, true) : 'You didn\'t find anything').'<br />';
}
$db->query('UPDATE users SET money = money + ?, searchdowntown = 0 WHERE id = ?');
$db->execute([$total, $user_class->id]);
?>You found a total of <?php echo prettynum($total, true); ?> whilst searching down town
    </td>
</tr>
