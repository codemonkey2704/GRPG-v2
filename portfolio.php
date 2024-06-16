<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$errors = [];
if (array_key_exists('sellstocks', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['stocks_id'] = isset($_POST['stocks_id']) && ctype_digit($_POST['stocks_id']) ? $_POST['stocks_id'] : null;
    if (empty($_POST['stocks_id'])) {
        $errors[] = 'You didn\'t select a valid stock to sell';
    }
    $db->query('SELECT id, cost FROM stocks WHERE id = ?');
    $db->execute([$_POST['stocks_id']]);
    if (!$db->count()) {
        $errors[] = 'The stock you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $_POST['amount'] = array_key_exists('amount', $_POST) && ctype_digit(str_replace(',', '', $_POST['amount'])) ? str_replace(',', '', $_POST['amount']) : null;
    if (empty($_POST['amount'])) {
        $errors[] = 'You didn\'t enter a valid amount of stock to sell';
    }
    $myShares = Check_Share($_POST['stocks_id'], $user_class->id);
    if ($_POST['amount'] > $myShares) {
        $errors[] = 'You don\'t have that many shares';
    }
    if (!count($errors)) {
        $costbefore = $row['cost'] * $_POST['amount'];
        $firmcut = ceil($costbefore * .1);
        $totalcost = $costbefore - $firmcut;
        $db->trans('start');
        $db->query('UPDATE users SET money = money + ? WHERE id = ?');
        $db->execute([$totalcost, $user_class->id]);
        Take_Share($_POST['stocks_id'], $user_class->id, $_POST['amount']);
        $db->trans('end');
        $user_class->money += $totalcost;
        echo Message('You\'ve sold '.format($_POST['amount']).' share'.s($_POST['amount']).' for a total of '.prettynum($totalcost, true).' ('.prettynum($row['cost'], true).' per share x '.format($_POST['amount']).' share'.s($_POST['amount']).' - '.prettynum($firmcut, true).' transaction fee)');
    }
}
$db->query('SELECT companyid, amount, cost, company_name
FROM shares
INNER JOIN stocks ON stocks.id = companyid
WHERE userid = ?
ORDER BY company_name ');
$db->execute([$user_class->id]);
$rows = $db->fetch();
?><tr>
    <td class="content" align="center">
        <img src='images/stock-market.png' />
    </td>
</tr>
<tr>
    <th class="content-head">Your Portfolio</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">Here you can view, compare, and sell your shares.</td>
</tr>
<tr>
    <th class="content-head">View Stocks</th>
</tr>
<tr>
    <td class="content">
        <table width="100%">
            <tr>
                <th width="35%">Company Name</th>
                <th width="20%">Cost per Share</th>
                <th width="10%"># Held</th>
                <th width="15%">Total Value</th>
                <th width="20%">Sell</th>
            </tr><?php
if ($rows !== null) {
        $csrf = csrf_create();
        foreach ($rows as $row) {
            ?><tr>
                    <td><?php echo format($row['company_name']); ?></td>
                    <td><?php echo prettynum($row['cost'], true); ?></td>
                    <td><?php echo format($row['amount']); ?></td>
                    <td><?php echo prettynum($row['amount'] * $row['cost'], true); ?></td>
                    <td>
                        <form action="portfolio.php" method="post" class="pure-form pure-form-aligned">
                            <?php echo $csrf; ?>
                            <input type="hidden" name="stocks_id" value="<?php echo $row['companyid']; ?>" />
                            <fieldset>
                                <div class="pure-control-group">
                                    <input type="text" name="amount" size="3" maxlength="20" value="<?php echo format($row['amount']); ?>" />
                                </div>
                            </fieldset>
                            <div class="pure-controls">
                                <button type="submit" name="sellstocks" class="pure-button pure-button-primary">Sell</button>
                            </div>
                        </form>
                    </td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="5" class="center">You have nothing in your portfolio</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
