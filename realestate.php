<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_POST['amount'] = array_key_exists('amount', $_POST) && ctype_digit(str_replace(',', '', $_POST['amount'])) ? str_replace(',', '', $_POST['amount']) : null;
$db->query('SELECT * FROM cities WHERE id = ?');
$db->execute([$user_class->city]);
$row = $db->fetch(true);
$errors = [];
if (array_key_exists('buyland', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['amount'])) {
        $errors[] = 'You didn\'t enter a valid amount';
    }
    if ($_POST['amount'] > $row['landleft']) {
        $errors[] = 'There isn\'t that much land left';
    }
    $cost = $row['landprice'] * $_POST['amount'];
    if ($cost > $user_class->money) {
        $errors[] = 'You don\'t have enough money';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
        $db->execute([$cost, $user_class->id]);
        $db->query('UPDATE cities SET landleft = GREATEST(landleft - ?, 0) WHERE id = ?');
        $db->execute([$_POST['amount'], $user_class->city]);
        Give_Land($user_class->city, $user_class->id, $_POST['amount']);
        $db->trans('end');
        $row['landleft'] -= $_POST['amount'];
        echo Message('You\'ve purchased '.format($_POST['amount']).' acres of land in '.format($user_class->cityname).' for '.prettynum($cost, true));
    }
}
?><tr>
    <th class="content-head">Real Estate Agency Of Generica</th>
</tr>
<tr>
    <td class="content">Welcome to REAG! If we have any land left available, you can purchase it from here.</td>
</tr>
<tr>
    <td class="content">
        Land available from REAG in <?php echo format($user_class->cityname); ?>: <?php echo format($row['landleft']); ?> acre<?php echo s($row['landleft']);
if ($row['landleft'] > 0) {
    ?><form action="realestate.php" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create(); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="amount">Acres to purchase</label>
                        <input type="text" name="amount" id="amount" size="3" maxlength="20" value="<?php echo format($row['landleft']); ?>" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="buyland" class="pure-button pure-button-primary">Buy Land At <?php echo prettynum($row['landprice'], true); ?> Per Acre</button>
                </div>
            </form><?php
}
?></td>
</tr>
