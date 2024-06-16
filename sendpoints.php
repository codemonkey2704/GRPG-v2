<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['person'] = array_key_exists('person', $_GET) && ctype_digit($_GET['person']) ? $_GET['person'] : null;
if (array_key_exists('submit', $_POST)) {
    $errors = [];
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['theirid'] = array_key_exists('theirid', $_POST) && ctype_digit($_POST['theirid']) ? $_POST['theirid'] : null;
    if (empty($_POST['theirid'])) {
        $errors[] = 'You didn\'t select a valid recipient';
    }
    if (!userExists($_POST['theirid'])) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $target = new User($_POST['theirid']);
    $_POST['amount'] = array_key_exists('amount', $_POST) && ctype_digit(str_replace(',', '', $_POST['amount'])) ? str_replace(',', '', $_POST['amount']) : null;
    if (empty($_POST['amount'])) {
        $errors[] = 'You didn\'t enter a valid amount';
    }
    if ($_POST['amount'] > $user_class->points) {
        $errors[] = 'You don\'t have enough points';
    }
    if (count($errors)) {
        display_errors($errors);
    } else {
        $db->trans('start');
        $db->query('UPDATE users SET points = GREATEST(points - ?, 0) WHERE id = ?');
        $db->execute([$_POST['amount'], $user_class->id]);
        $db->query('UPDATE users SET points = points + ? WHERE id = ?');
        $db->execute([$_POST['amount'], $target->id]);
        Send_Event($target->id, '{extra} sent you '.format($_POST['amount']).' point'.s($_POST['amount']), $user_class->id);
        $db->trans('end');
        echo Message('You\'ve sent '.format($_POST['amount']).' point'.s($_POST['amount']).' to '.$target->formattedname);
    }
}
?><tr>
    <th class="content-head">Send Points</th>
</tr>
<tr>
    <td class="content">
        <form action="sendpoints.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="amount">Amount of points</label>
                    <input type="text" name="amount" id="amount" size="22" />
                </div>
                <div class="pure-control-group">
                    <label for="theirid">Player ID</label>
                    <input type="text" name="theirid" id="theirid" size="22" value="<?php echo $_GET['person']; ?>" />
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Send Points</button>
            </div>
        </form>
    </td>
</tr>
