<?php
declare(strict_types=1);

define('GRPG_INC', true);
ini_set('log_errors', true);
ini_set('error_log', __DIR__.'/ipn_errors.log');
// instantiate the IpnListener class
require_once __DIR__.'/ipnlistener.php';
$listener = new ipnlistener();
// Connect to game
require_once dirname(__DIR__).'/inc/dbcon.php';
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {
    /** @noinspection ForgottenDebugOutputInspection */
    error_log($e->getMessage());
    exit;
}
$errorMail = 'errors@'.rtrim(str_replace(['http://', 'https://', 'www.'], '', BASE_URL), '/');
function sendError($sub, $listener)
{
    global $errorMail, $db;
    $db->query('INSERT INTO rmstore_packs_errors (subject, message) VALUES (?, ?)');
    $db->execute([$sub, $listener->getTextReport()]);
    // Comment out the line below if you *don't* want to be emailed at errors@yoursite.tld whenever an error is detected here
    mail($errorMail, 'IPN: '.$sub, $listener->getTextReport());
    exit(0);
}
if ($verified) {
    /*
     *    Start main IPN validation
    */
    if (strtolower($_POST['payment_status']) !== 'completed') { // Validate payment status
        sendError('Invalid Status', $listener);
    }
    $_POST['txn_id'] = array_key_exists('txn_id', $_POST) && ctype_alnum($_POST['txn_id']) ? $_POST['txn_id'] : null;
    if (empty($_POST['txn_id'])) { // Validate transaction ID
        sendError('Transaction ID Missing', $listener);
    }
    $db->query('SELECT COUNT(id) FROM rmstore_ipn WHERE transaction_id = ?');
    $db->execute([$_POST['txn_id']]);
    if ($db->result()) { // Prevent repeat transaction
        sendError('Repeat Transaction', $listener);
    }
    if (empty($_POST['receiver_email']) || !filter_var($_POST['receiver_email'], FILTER_VALIDATE_EMAIL)) { // Validate recipient email address
        sendError('No recipient address', $listener);
    }
    if (strtolower($_POST['receiver_email']) != strtolower(PAYPAL_ADDRESS)) { // Ensure recipient's email address matches the game's PayPal address
        sendError('Recipient address doesn\'t match game settings', $listener);
    }
    if (empty($_POST['mc_currency'])) { // Validate currency is sent
        sendError('Currency not supplied', $listener);
    }
    if ($_POST['mc_currency'] != RMSTORE_CURRENCY) { // Ensure sent currency matches game's RMStore currency
        sendError('Currency Mismatch: (expected: '.RMSTORE_CURRENCY.', got: '.$_POST['mc_currency'].')', $listener);
    }
    if (empty($_POST['item_number']) || !ctype_digit($_POST['item_number'])) { // Validate item number is sent
        sendError('Invalid Pack ID', $listener);
    }
    /*
     *    End main validation
     *    --------------------
     *    Start pack validation
    */
    $db->query('SELECT * FROM rmstore_packs WHERE id = ?'); // Grab pack details
    $db->execute([$_POST['item_number']]);
    if (!$db->count()) {
        sendError('Invalid ID', $listener);
    }
    $pack = $db->fetch(true);
    $cost = $pack['cost'];
    if (RMSTORE_DISCOUNT > 0) { // Decrement discount (percentage) if applicable
        $cost -= ($pack['cost'] / 100) * RMSTORE_DISCOUNT;
    }
    if ($_POST['mc_gross'] != $cost) { // Validate payment amount
        sendError('Cost Mismatch', $listener);
    }
    if (empty($_POST['custom'])) { // Validate custom field is sent
        sendError('UserID Not Supplied', $listener);
    }
    [$buyer, $recipient] = explode(':', $_POST['custom']); // Assign "custom" parts to easy variables
    if (!$buyer) { // Validate buyer
        sendError('Buyer ID not given', $listener);
    }
    if (!$recipient) {
        $recipient = $buyer;
    }
    $db->query('SELECT username FROM users WHERE id = ?');
    $db->execute([$recipient]);
    if (!$db->count()) {
        sendError('User Not Found', $listener);
    }
    $user = $db->count() ? $db->result() : 'Unknown user';
    $buyRecSame = $buyer == $recipient;
    if (RMSTORE_BOGOF == true) { // Apply BOGOF offer if applicable
        $pack['days'] *= 2;
        $pack['money'] *= 2;
        $pack['points'] *= 2;
        $pack['prostitutes'] *= 2;
    }
    /*
     *    End pack validation
    */
    // Credit pack
    $db->trans('start');
    if ($pack['days'] || $pack['money'] || $pack['points']) {
        $db->query('UPDATE users SET money = money + ?, points = points + ?, rmdays = rmdays + ?, hookers = hookers + ? WHERE id = ?');
        $db->execute([$pack['money'], $pack['points'], $pack['days'], $pack['prostitutes'], $recipient]);
    }
    if ($pack['items']) {
        $itemQty = explode(',', $pack['items']);
        foreach ($itemQty as $what) {
            [$item, $qty] = array_pad(explode(':', $what), 2, 1);
            if (itemExists($item)) {
                if (RMSTORE_BOGOF == true) {
                    $qty *= 2;
                }
                Give_Item($item, $recipient, $qty);
            }
        }
    }
    $db->query('INSERT INTO rmstore_ipn (userid, recipient, transaction_id, payer_email, pack_id, pack_cost, paid_amount, discount) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $db->execute([$buyer, $recipient, $_POST['txn_id'], $_POST['payer_email'], $_POST['item_number'], $pack['cost'], $cost, RMSTORE_DISCOUNT]);
    Send_Event($buyer, 'Your RMStore Upgrade has been credited to '.($buyRecSame ? 'you' : '{extra}').'', $buyRecSame ? 0 : $recipient);
    if (!$buyRecSame) {
        Send_Event($recipient, '{extra} purchased the RMStore Upgrade: '.format($pack['name']).' for you', $buyer);
    }
    global $owner;
    // Comment out the line below if you don't want owner to be notified via event upon successful transaction
    Send_Event($owner->id, 'Donation from {extra}. Pack '.format($_POST['item_number']), $buyer);
    $db->trans('end');
    // Comment out the mail(...) line below if you *don't* want to be notified via email (to errors@yoursite.tld) on a successful transaction
    // Recommending you leave this active so if there's an issue with the IPN (for example, when PayPal updated their systems and almost every IPN went down), you'll be notified
    mail($errorMail, 'Verified IPN', $listener->getTextReport());
} else {
    sendError('Possible Fraud Attempt', $listener);
}
