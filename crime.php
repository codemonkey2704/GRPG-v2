<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
checkUserStatus();
if ($_GET['id'] !== null) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT * FROM crimes WHERE id = ?', [$_GET['id']]);
    $row = $db->fetch(true);
    if ($row === null) {
        echo Message('That crime doesn\'t exist', 'Error', true);
    }
    $nerve = $row['nerve'];
    $stext = '[[We currently don\'t have a success message for this crime :( You can help us by submitting your idea for a message in the crime section of the forums!]]';
    $ctext = '[[We currently don\'t have a "You got caught" message for this crime :( You can help us by submitting your idea for a message in the crime section of the forums!]]';
    $ftext = '[[We currently do not have a failure message for this crime :( You can help us by submitting your idea for a message in the crime section of the forums!]]';
    $stexta = (array)explode('^', $row['stext']);
    $stext = !empty($stexta[0]) ? $stexta[array_rand($stexta)] : $stext;
    $ctexta = (array)explode('^', $row['ctext']);
    $ctext = !empty($ctexta[0]) ? $ctexta[array_rand($ctexta)] : $ctext;
    $ftexta = (array)explode('^', $row['ftext']);
    $ftext = !empty($ftexta[0]) ? $ftexta[array_rand($ftexta)] : $ftext;
    $chance = max(mt_rand(1, (int)(100 * $nerve - ($user_class->speed / 35))), 1);
    $money = round(25 * $nerve) + 15 * ($nerve - 1);
    if ($user_class->class === 'Thief') {
        $money = round(25 * $nerve) + 15 * ($nerve - 1) + 5;
    }
    $exp = $money;
    if ($nerve > $user_class->nerve) {
        echo Message('You don\'t have enough nerve for that crime', 'Error', true);
    } else {
        $csrfg = csrf_create('csrfg', false);
        if ($chance <= 75) {
            $db->query('UPDATE users SET experience = experience + ?, crimesucceeded = crimesucceeded + 1, crimemoney = crimemoney + ?, money = money + ?, nerve = GREATEST(nerve - ?, 0) WHERE id = ?');
            $db->execute([$exp, $money, $money, $nerve, $user_class->id]);
            echo Message($stext.'<br /><br /><span style="color:green;">Success! You receive '.$exp.' exp and '.prettynum($money, true).'.</span><br /><a href="crime.php?id='.$_GET['id'].'&amp;csrfg='.$csrfg.'">Retry</a> | <a href="crime.php">Back</a>', 'Error', true);
        } elseif ($chance >= 150) {
            $db->query('UPDATE users SET crimefailed = crimefailed + 1, jail = ?, nerve = GREATEST(nerve - ?, 0) WHERE id = ?');
            $db->execute([$_GET['id'] * 600, $nerve, $user_class->id]);
            echo Message($ctext.'<br /><br /><span style="color:red;">You were caught.</span> You were hauled off to jail for '.($_GET['id'] * 10).' minutes.', 'Error', true);
        } else {
            $db->query('UPDATE users SET crimefailed = crimefailed + 1, nerve = GREATEST(nerve - ?, 0) WHERE id = ?');
            $db->execute([$nerve, $user_class->id]);
            echo Message($ftext.'<br /><br /><span style="color:red;">You failed.</span><br /><a href="crime.php?id='.$_GET['id'].'&amp;csrfg='.$csrfg.'">Retry</a> | <a href="crime.php">Back</a>', 'Error', true);
        }
    }
}
if (!isset($csrfg)) {
    $csrfg = csrf_create('csrfg', false);
}
$db->query('SELECT id, name, nerve FROM crimes ORDER BY nerve ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Crime</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th width="50%">Name</th>
                    <th width="25%">Nerve</th>
                    <th width="25%">Action</th>
                </tr>
            </thead><?php
if ($rows !== null) {
        foreach ($rows as $row) {
            ?>
        <tr>
            <td><?php echo format($row['name']); ?></td>
            <td><?php echo format($row['nerve']); ?></td>
            <td>[<a href="crime.php?id=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Do</a>]</td>
        </tr><?php
        }
    } else {
        ?>
        <tr>
            <td colspan="3" class="center">There are no crimes</td>
        </tr><?php
    }
?></table>
    </td>
</tr>
