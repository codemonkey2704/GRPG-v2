<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$errors = [];
$_GET['harvest'] = array_key_exists('harvest', $_GET) && ctype_digit($_GET['harvest']) ? $_GET['harvest'] : null;
if (!empty($_GET['harvest'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT * FROM growing WHERE id = ?');
    $db->execute([$_GET['harvest']]);
    $row = $db->fetch(true);
    if (empty($row)) {
        $errors[] = 'That crop doesn\'t exist';
    }
    if ($row['userid'] != $user_class->id) {
        $errors[] = 'That isn\'t your crop';
    }
    if ($row['time_ended'] !== null && strtotime($row['time_ended']) > time()) {
        $errors[] = 'Your crop hasn\'t finished growing yet';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET marijuana = marijuana + ? WHERE id = ?');
        $db->execute([$row['cropamount'], $user_class->id]);
        Give_Land($row['cityid'], $row['userid'], $row['amount']);
        $db->query('DELETE FROM growing WHERE id = ?');
        $db->execute([$_GET['harvest']]);
        $db->trans('end');
        echo Message('You\'ve received '.format($row['cropamount']).' ounce'.s($row['cropamount']).' of maijuana');
    }
}
if (array_key_exists('plant', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['amount'] = array_key_exists('amount', $_POST) && ctype_digit($_POST['amount']) ? $_POST['amount'] : null;
    if (empty($_POST['amount'])) {
        $errors[] = 'You didn\'t enter a valid amount';
    }
    $qty = Check_Land($user_class->city, $user_class->id);
    if ($_POST['amount'] > $qty) {
        $errors[] = 'You don\'t have many acres of land';
    }
    $amnt = $_POST['amount'] * 100;
    if ($amnt > $user_class->potseeds) {
        $errors[] = ' You don\'t have enough marijuana seeds to plant that many acres of weed. You need 100 seeds per acre';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('INSERT INTO growing (userid, cityid, amount, croptype, cropamount, time_ended) VALUES (?, ?, ?, \'pot\', ?, ?)');
        $db->execute([$user_class->id, $user_class->city, $_POST['amount'], $amnt, db_timestamp(time() + 604800)]);
        $db->query('UPDATE users SET potseeds = GREATEST(potseeds - ?, 0) WHERE id = ?');
        $db->execute([$amnt, $user_class->id]);
        Take_Land($user_class->city, $user_class->id, $_POST['amount']);
        $db->trans('end');
        echo Message('You\'ve planted '.format($_POST['amount']).' acre'.s($_POST['amount']).' acres of marijuana');
    }
}
$db->query('SELECT amount FROM land WHERE city = ? AND userid = ?');
$db->execute([$user_class->city, $user_class->id]);
$amount = $db->result();
$amount = $amount > 0 ? $amount : 0;
?><tr>
    <th class="content-head">Manage Land</th>
</tr>
<tr>
    <td class="content">Here is where you can manage your acres of land.</td>
</tr>
<tr>
    <th class="content-head">Plant</th>
</tr>
<tr>
    <td class="content"><?php
if ($amount) {
    $available = floor($user_class->potseeds / 100); ?>You have <?php echo format($amount); ?> acre<?php echo s($amount); ?> of land in <?php echo $user_class->cityname; ?> and <?php echo $user_class->potseeds; ?> marijuana seed<?php echo s($user_class->potseeds); ?>,
        which is enough to grow <?php echo format($available); ?> acre<?php echo s($available); ?> of weed<br />
        <form action="fields.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="amount">Acres</label>
                    <input type="text" name="amount" id="amount" size="3" maxlength="20" />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="plant" class="pure-button pure-button-primary">Plant Pot Seeds</button>
            </div>
        </form><?php
} else {
        ?>You don't have any land here in <?php echo $user_class->cityname;
    }
?></td>
</tr>
<tr>
    <th class="content-head">Currently Growing</th>
</tr>
<tr>
    <td class="content"><?php
if ($amount > 0) {
    $csrfg = csrf_create('csrfg', false);
    $db->query('SELECT id, croptype, amount, cropamount, time_ended FROM growing WHERE cityid = ? AND userid = ?');
    $db->execute([$user_class->city, $user_class->id]);
    $rows = $db->fetch(); ?><table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Crop Type</th>
                    <th>Acres Planted</th>
                    <th>Total Plants Left (on all acres)</th>
                    <th>Time Left Until Harvest</th>
                </tr>
            </thead><?php
    foreach ($rows as $row) {
        $time = $row['time_ended'] !== null ? strtotime($row['time_ended']) : 0;
        ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo format($row['croptype']); ?></td>
                    <td><?php echo format($row['amount']); ?></td>
                    <td><?php echo format($row['cropamount']); ?></td>
                    <td><?php echo time() >= $time ? 'Ready! <a href="fields.php?harvest='.$row['id'].'&amp;csrfg='.$csrfg.'">Harvest Now</a>' : howlongtil($row['time_ended']); ?></td>
                </tr><?php
    } ?></table><?php
} else {
        ?>You don't currently have any land with crops growing on it.<?php
    }
?></td>
</tr>
