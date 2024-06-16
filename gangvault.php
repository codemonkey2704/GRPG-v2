<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
$errors = [];
$gang_class = new Gang($user_class->gang);
if (array_key_exists('deposit', $_POST) || array_key_exists('deposit_points', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $curr = !array_key_exists('points', $_GET) ? 'money' : 'points';
    $currVault = !array_key_exists('points', $_GET) ? 'moneyvault' : 'pointsvault';
    $_POST['damount'] = array_key_exists('damount', $_POST) && ctype_digit(str_replace(',', '', $_POST['damount'])) ? str_replace(',', '', $_POST['damount']) : null;
    if (empty($_POST['damount'])) {
        $errors[] = 'You didn\'t enter a valid amount';
    }
    if ($_POST['damount'] > $user_class->$curr) {
        $errors[] = 'You don\'t have that much '.$curr;
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET '.$curr.' = GREATEST('.$curr.' - ?, 0) WHERE id = ?');
        $db->execute([$_POST['damount'], $user_class->id]);
        $db->query('UPDATE gangs SET '.$currVault.' = '.$currVault.' + ? WHERE id = ?');
        $db->execute([$_POST['damount'], $gang_class->id]);
        $db->trans('end');
        $gang_class->$currVault += $_POST['damount'];
        $user_class->$curr -= $_POST['damount'];
        echo Message('You\'ve deposited '.(!empty($_POST['deposit']) ? prettynum($_POST['damount'], true) : points($_POST['damount'])));
    }
}
if ((array_key_exists('withdraw', $_POST) || array_key_exists('withdraw_points', $_POST)) && $gang_class->leader == $user_class->id) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $curr = !empty($_POST['withdraw']) ? 'money' : 'points';
    $currVault = !empty($_POST['withdraw']) ? 'moneyvault' : 'pointsvault';
    $_POST['wamount'] = array_key_exists('wamount', $_POST) && ctype_digit(str_replace(',', '', $_POST['wamount'])) ? str_replace(',', '', $_POST['wamount']) : null;
    if (empty($_POST['wamount'])) {
        $errors[] = 'You didn\'t enter a valid amount';
    }
    if ($_POST['wamount'] > $gang_class->$currVault) {
        $errors[] = 'The vault doesn\'t have that much '.$curr;
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE gangs SET '.$currVault.' = GREATEST('.$currVault.' - ?, 0) WHERE id = ?');
        $db->execute([$_POST['wamount'], $gang_class->id]);
        $db->query('UPDATE users SET '.$curr.' = '.$curr.' + ? WHERE id = ?');
        $db->execute([$_POST['wamount'], $user_class->id]);
        $db->trans('end');
        $gang_class->$currVault -= $_POST['wamount'];
        $user_class->$curr += $_POST['wamount'];
        echo Message('You\'ve withdrawn '.(!empty($_POST['withdraw']) ? prettynum($_POST['wamount'], true) : points($_POST['wamount'])).' from the gang vault');
    }
}
$csrf = csrf_create();
?><tr>
    <th class="content-head"><?php echo $gang_class->formattedname; ?> Vault</th>
</tr>
<tr>
    <td class="content">
        Welcome to the gang vault. There is currently  <?php echo prettynum($gang_class->moneyvault, true); ?> and <?php echo format($gang_class->pointsvault); ?> point<?php echo s($gang_class->pointsvault); ?> in the gang vault.<br /><br />
        <table width="100%" class="pure-table pure-table-horizontal">
            <thead>
                <tr>
                    <th width="50%">Money</th>
                    <th width="50%">Points</th>
                </tr>
            </thead>
            <tr>
                <td>
                    <form action="gangvault.php" method="post" class="pure-form pure-form-aligned">
                        <?php echo $csrf; ?>
                        <fieldset>
                            <div class="pure-control-group">
                                <label for="damount">Money</label>
                                <input type="text" name="damount" id="damount" value="<?php echo format($user_class->money); ?>" size="10" maxlength="20" />
                            </div>
                        </fieldset>
                        <div class="pure-controls">
                            <button type="submit" name="deposit" class="pure-button pure-button-primary">Deposit</button>
                        </div>
                    </form>
                </td>
                <td>
                    <form action="gangvault.php?points" method="post" class="pure-form pure-form-aligned">
                        <?php echo $csrf; ?>
                        <fieldset>
                            <div class="pure-control-group">
                                <label for="damount_points">Points</label>
                                <input type="text" name="damount" id="damount_points" value="<?php echo format($user_class->points); ?>" size="10" maxlength="20" />
                            </div>
                        </fieldset>
                        <div class="pure-controls">
                            <button type="submit" name="deposit_points" class="pure-button pure-button-primary">Deposit</button>
                        </div>
                    </form>
                </td>
            </tr><?php
            if ($gang_class->leader == $user_class->id) {
                ?><tr>
                    <td>
                        <form action="gangvault.php" method="post" class="pure-form pure-form-aligned">
                            <?php echo $csrf; ?>
                            <fieldset>
                                <div class="pure-control-group">
                                    <label for="wamount">Money</label>
                                    <input type="text" name="wamount" id="wamount" value="<?php echo format($gang_class->moneyvault); ?>" size="10" maxlength="20" />
                                </div>
                            </fieldset>
                            <div class="pure-controls">
                                <button type="submit" name="withdraw" value="1" class="pure-button pure-button-primary">Withdraw</button>
                            </div>
                        </form>
                    </td>
                    <td>
                        <form action="gangvault.php?points" method="post" class="pure-form pure-form-aligned">
                            <?php echo $csrf; ?>
                            <fieldset>
                                <div class="pure-control-group">
                                    <label for="wamount_points">Points</label>
                                    <input type="text" name="wamount" id="wamount_points" value="<?php echo format($gang_class->pointsvault); ?>" size="10" maxlength="20" />
                                </div>
                            </fieldset>
                            <div class="pure-controls">
                                <button type="submit" name="withdraw_points" class="pure-button pure-button-primary">Withdraw</button>
                            </div>
                        </form>
                    </td>
                </tr><?php
            }
        ?></table>
    </td>
</tr>
