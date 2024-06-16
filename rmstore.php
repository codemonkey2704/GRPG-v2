<?php
declare(strict_types=1);
global $owner;
require_once __DIR__.'/inc/header.php';
$errors = [];
if (array_key_exists('cancel', $_GET)) {
    echo Message('You\'ve cancelled your purchase');
}[];
if (array_key_exists('success', $_GET)) {
    echo Message('Your purchase was successful');
}
if (array_key_exists('reset', $_GET)) {
    unset($_SESSION['customer']);
}
if (array_key_exists('update_customer', $_POST)) {
    $_POST['customer'] = array_key_exists('customer', $_POST) && is_string($_POST['customer']) ? strip_tags(trim($_POST['customer'])) : null;
    if (empty($_POST['customer'])) {
        $errors[] = 'You didn\'t enter a valid recipient name';
    }
    $id = Get_ID($_POST['customer']);
    if (!userExists($id)) {
        $errors[] = 'The recipient you selected doesn\'t exist';
    }
    if (count($errors)) {
        display_errors($errors);
    } else {
        $_SESSION['customer'] = $id;
    }
}
if (!array_key_exists('customer', $_SESSION)) {
    $_SESSION['customer'] = $user_class->id;
}
$target = new User($_SESSION['customer']);
$db->query('SELECT * FROM rmstore_packs WHERE enabled = 1 ORDER BY cost ');
$db->execute();
$packs = $db->fetch();
?><tr>
    <th class="content-head">Respected Mobsters</th>
</tr>
<tr>
    <td class="content">
        * For those days you will gain energy and nerve twice as quick.<br /> * For those days you will gain 4% bank interest instead of 2%.
    </td>
</tr>
<tr>
    <th class="content-head">Stuff To Buy</th>
</tr>
<tr>
    <td class="content">
        <form action="rmstore.php" method="post" class="pure-form pure-form-aligned">
            <div class="pure-info-message">You're currently purchasing an RMStore Upgrade for <?php echo $target->id == $user_class->id ? 'yourself' : $target->formattedname.' <span class="small italic">- [<a href="rmstore.php?reset">Reset</a>]</span>'; ?></div>
            <?php echo csrf_create('custom_for'); ?>
            <div class="pure-control-group">
                <label for="customer">Purchase upgrade for:</label>
                <input type="text" name="customer" id="customer" placeholder="<?php echo format($target->username); ?>" />
            </div>
            <div class="pure-controls">
                <button type="submit" name="update_customer" class="pure-button pure-button-primary">Update recipient</button>
            </div>
        </form>
    </td>
</tr><?php
if (RMSTORE_BOGOF == true && RMSTORE_DISCOUNT > 0) {
    echo Message('There\'s '.RMSTORE_DISCOUNT.'% off all RMStore Upgrades <em>and</em> they\'re on a &ldquo;Buy One Get One Free&rdquo; offer!');
} elseif (RMSTORE_DISCOUNT > 0) {
    echo Message('There\'s '.RMSTORE_DISCOUNT.'% off all RMStore Upgrades!');
} elseif (RMSTORE_BOGOF == true) {
    echo Message('All RMStore Upgrades are Buy One Get One Free!');
}
?><tr>
    <td class="content">
        <table width="100%" cellspacing="1">
            <tr style="background:#910503;text-align:center;">
                <td>Package</td>
                <td>RM Days</td>
                <td>Points</td>
                <td>Prostitutes</td>
                <td>Items</td>
                <td>Cost</td>
                <td>Purchase</td>
            </tr><?php
if ($packs !== null) {
        foreach ($packs as $pack) {
            $cost = $pack['cost'];
            if (RMSTORE_DISCOUNT > 0) {
                $cost -= ($pack['cost'] / 100) * RMSTORE_DISCOUNT;
            } ?><tr style="background:#181818;text-align:center;">
                        <td><?php echo format($pack['name']); ?></td>
                        <td><?php echo $pack['days'] ? format($pack['days']) : '-'; ?></td>
                        <td><?php echo $pack['points'] ? format($pack['points']) : '-'; ?></td>
                        <td><?php echo $pack['prostitutes'] ? format($pack['prostitutes']) : '-'; ?></td>
                        <td><?php
        if ($pack['items']) {
            $itemsArray = explode(',', $pack['items']);
            foreach ($itemsArray as $what) {
                [$qty, $item] = explode(':', $what);
                if (itemExists($item)) {
                    echo format($qty).'x '.item_popup($item).'<br />';
                }
            }
        } else {
            echo '-';
        } ?></td>
                        <td><?php echo $pack['cost'] == $cost ? formatCurrency($pack['cost']) : '<span class="strike">'.formatCurrency($pack['cost']).'</span><br /><span class="green">'.formatCurrency($cost).'</span>'; ?></td>
                        <td>
                            <form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="pure-form" target="new">
                                <input type="hidden" name="cmd" value="_xclick" />
                                <input type="hidden" name="business" value="<?php echo PAYPAL_ADDRESS; ?>" />
                                <input type="hidden" name="item_name" value="<?php echo $pack['name']; ?>" />
                                <input type="hidden" name="item_number" value="<?php echo $pack['id']; ?>" />
                                <input type="hidden" name="custom" value="<?php echo $user_class->id.':'.$_SESSION['customer']; ?>" />
                                <input type="hidden" name="amount" value="<?php echo formatCurrency($cost, ''); ?>" />
                                <input type="hidden" name="no_shipping" value="1" />
                                <input type="hidden" name="no_note" value="1" />
                                <input type="hidden" name="notify_url" value="<?php echo BASE_URL; ?>ipn/notify.php" />
                                <input type="hidden" name="currency_code" value="<?php echo RMSTORE_CURRENCY; ?>" />
                                <input type="hidden" name="return" value="<?php echo BASE_URL; ?>rmstore.php?success" />
                                <input type="hidden" name="return_cancel" value="<?php echo BASE_URL; ?>rmstore.php?cancel" />
                                <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but23.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!" />
                                <img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
                            </form>
                        </td>
                    </tr><?php
        }
    } else {
        ?><tr>
                        <td colspan="7" class="centre" style="background:#181818;text-align:center;">There are no RMStore Upgrades available</td>
                    </tr><?php
    }
?></table>
    </td>
</tr>
<tr>
    <th class="content-head">Read This Or Die</th>
</tr>
<tr>
    <td class="content">
        If you have any questions, PM me (<?php echo $owner->formattedname; ?>).<br />
        Before you buy you must be clear of the following things:<br /><br />
        1. No refunds.<br />
        2. You can still be banned for breaking the rules whether you have donated or not.
    </td>
</tr>
