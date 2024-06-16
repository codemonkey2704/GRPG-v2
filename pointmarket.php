<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_POST['points_id'] = array_key_exists('points_id', $_POST) && ctype_digit($_POST['points_id']) ? $_POST['points_id'] : null;
$_POST['amount'] = array_key_exists('amount', $_POST) && ctype_digit(str_replace(',', '', $_POST['amount'])) ? str_replace(',', '', $_POST['amount']) : null;
$errors = [];
if (array_key_exists('buypoints', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['points_id'])) {
        $errors[] = 'Invalid point listing';
    }
    if (empty($_POST['amount'])) {
        $errors[] = 'You didn\'t enter a valid amount';
    }
    $db->query('SELECT id, price, amount, owner FROM pointsmarket WHERE id = ?');
    $db->execute([$_POST['points_id']]);
    if (!$db->count()) {
        $errors[] = 'The listing you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    if ($_POST['amount'] > $row['amount']) {
        $errors[] = 'There aren\'t that many points on this listing';
    }
    if (!count($errors) && $row['owner'] == $user_class->id) {
        $db->trans('start');
        $db->query('UPDATE users SET points = points + ? WHERE id = ?');
        $db->execute([$_POST['amount'], $user_class->id]);
        if ($row['amount'] > $_POST['amount']) {
            $db->query('UPDATE pointsmarket SET amount = GREATEST(amount - ?, 0) WHERE id = ?');
            $db->execute([$_POST['amount'], $_POST['points_id']]);
        } else {
            $db->query('DELETE FROM pointsmarket WHERE id = ?');
            $db->execute([$_POST['points_id']]);
        }
        $db->trans('end');
        echo Message('You\'ve removed '.format($_POST['amount']).' point'.s($_POST['amount']).' from your listing', 'Error', true);
    }
    $cost = $_POST['amount'] * $row['price'];
    if ($cost > $user_class->money) {
        $errors[] = 'You don\'t have enough money';
    }
    $owner = new User($row['owner']);
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0), points = points + ? WHERE id = ?');
        $db->execute([$cost, $_POST['amount'], $user_class->id]);
        if ($row['amount'] > $_POST['amount']) {
            $db->query('UPDATE pointsmarket SET amount = GREATEST(amount - ?, 0) WHERE id = ?');
            $db->execute([$_POST['amount'], $_POST['points_id']]);
        } else {
            $db->query('DELETE FROM pointsmarket WHERE id = ?');
            $db->execute([$_POST['points_id']]);
        }
        $db->query('UPDATE users SET money = money + ? WHERE id = ?');
        $db->execute([$cost, $owner->id]);
        Send_Event($owner->id, '{extra} bought '.format($_POST['amount']).' point'.s($_POST['amount']).' from your listing for '.prettynum($cost, true), $user_class->id);
        $db->trans('end');
        echo Message('You\'ve purchased '.format($_POST['amount']).' point'.s($_POST['amount']).' from '.$owner->formattedname.'\'s listing');
    }
}
if (array_key_exists('addpoints', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['amount'])) {
        $errors[] = 'You didn\'t enter a valid amount';
    }
    if ($_POST['amount'] > $user_class->points) {
        $errors[] = 'You don\'t have that many points';
    }
    $_POST['price'] = array_key_exists('price', $_POST) && ctype_digit(str_replace(',', '', $_POST['price'])) ? str_replace(',', '', $_POST['price']) : null;
    if (empty($_POST['price'])) {
        $errors[] = 'You didn\'t enter a valid price';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET points = GREATEST(points - ?, 0) WHERE id = ?');
        $db->execute([$_POST['amount'], $user_class->id]);
        $db->query('INSERT INTO pointsmarket (owner, amount, price) VALUES (?, ?, ?)');
        $db->execute([$user_class->id, $_POST['amount'], $_POST['price']]);
        $db->trans('end');
        $user_class->points -= $_POST['amount'];
        echo Message('You\'ve added '.format($_POST['amount']).' point'.s($_POST['amount']).' for '.prettynum($_POST['price'], true).' each (total: '.prettynum($_POST['price'] * $_POST['amount'], true).')');
    }
}
$db->query('SELECT * FROM pointsmarket ORDER BY price DESC');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head">Point Market</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
$csrf = csrf_create();
?><tr>
    <td class="content">
        Use this form to add points to the points market.<br /><br />
        <form action="pointmarket.php" method="post" class="pure-form pure-form-aligned">
            <?php echo $csrf; ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="amount">Amount of points</label>
                    <input type="text" name="amount" id="amount" size="10" maxlength="20" value="<?php echo format($user_class->points); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="price">Price per point</label>
                    $<input type="text" name="price" id="price" size="10" maxlength="20" />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="addpoints" class="pure-button pure-button-primary">Add Points</button>
            </div>
        </form>
    </td>
</tr>
<tr>
    <td class="content"><?php
if ($rows !== null) {
        foreach ($rows as $row) {
            $user_points = new User($row['owner']);
            echo $user_points->formattedname; ?> - <?php echo format($row['amount']); ?> point<?php echo s($row['amount']); ?> for <?php echo prettynum($row['price'], true); ?> per point<br />
            <form action="pointmarket.php" method="post" class="pure-form pure-form-aligned">
                <?php echo $csrf; ?>
                <input type="hidden" name="points_id" value="<?php echo $row['id']; ?>" />
                <fieldset>
                    <div class="pure-control-group">
                        <input type="text" name="amount" size="3" maxlength="20" value="<?php echo format($row['amount']); ?>" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="buypoints" class="pure-button pure-button-primary"><?php echo $user_class->id == $user_points->id ? 'Remove' : 'Buy'; ?></button>
                </div>
            </form><?php
        }
    } else {
        ?>There are no listings<?php
    }
?></td>
</tr>
