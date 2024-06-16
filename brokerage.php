<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (array_key_exists('buystocks', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['stocks_id'] = array_key_exists('stocks_id', $_POST) && ctype_digit($_POST['stocks_id']) ? $_POST['stocks_id'] : null;
    if (empty($_POST['stocks_id'])) {
        echo Message('You didn\'t select a valid stock', 'Error', true);
    }
    $_POST['amount'] = array_key_exists('amount', $_POST) && ctype_digit(str_replace(',', '', $_POST['amount'])) ? str_replace(',', '', $_POST['amount']) : 1;
    $db->query('SELECT id, cost FROM stocks WHERE id = ?');
    $db->execute([$_POST['stocks_id']]);
    if (!$db->count()) {
        echo Message('Invalid stock', 'Error', true);
    }
    $row = $db->fetch(true);
    $cost = $row['cost'] * $_POST['amount'];
    $cut = ceil($cost * .1);
    $cost += $cut;
    if ($row['cost'] < 15) {
        echo Message('Due to current market regulations, you can only buy shares of stocks that are selling at $15 or more.', 'Error', true);
    }
    if ($cost > $user_class->money) {
        echo Message('You don\'t have enough money', 'Error', true);
    }
    $db->trans('start');
    $db->query('UPDATE users SET money = GREATEST(money - ?, 0) WHERE id = ?');
    $db->execute([$cost, $user_class->id]);
    Give_Share($_POST['stocks_id'], $user_class->id, $_POST['amount']);
    $db->trans('end');
    echo Message('You\'ve bought '.format($_POST['amount']).' share'.s($_POST['amount']).' for a total of '.prettynum($cost, true).' ('.prettynum($price, true).' per share X '.format($_POST['amount']).' share'.s($_POST['amount']).' + '.prettynum($cut).' transaction fee)');
}
$db->query('SELECT id, company_name, cost FROM stocks ORDER BY id ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <td class="content center">
        <img src="images/stock-market.png" />
    </td>
</tr>
<tr>
    <th class="content-head">Brokerage Firm</th>
</tr>
<tr>
    <td class="content">
        Welcome! We are here to help further your wealth, so if there is anything we can do, just let us know! Please keep in mind that we will be charging a 10% transaction fee on your stock exchange when you buy or sell. Thanks for being so understanding!
    </td>
</tr>

<tr>
    <th class="content-head">Buy Stocks</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <tr>
                <th width="5%">ID</th>
                <th width="35%">Company Name</th>
                <th width="25%">Cost per Share</th>
                <th width="35%">Buy</th>
            </tr><?php
if ($rows !== null) {
        $csrf = csrf_create();
        foreach ($rows as $row) {
            ?><tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo format($row['company_name']); ?></td>
                    <td><?php echo prettynum($row['cost'], true); ?></td>
                    <td>
                        <form action="brokerage.php" method="post" class="pure-form pure-form-aligned">
                            <?php echo $csrf; ?>
                            <input type="hidden" name="stocks_id" value="<?php echo $row['id']; ?>" />
                            <fieldset>
                                <div class="pure-control-group">
                                    <input type="text" name="amount" size="3" maxlength="20" value="<?php echo $row['amount']; ?>" />
                                </div>
                            </fieldset>
                            <div class="pure-controls">
                                <button type="submit" name="buystocks" class="pure-button pure-button-primary">Buy</button>
                            </div>
                        </form>
                    </td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="4" class="center">There are currently no stocks</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
