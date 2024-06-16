<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$cost = 5000;
if (array_key_exists('sell', $_GET)) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $errors = [];
    if (!$user_class->marijuana) {
        $errors[] = 'You don\'t have any';
    }
    if (!$user_class->rmdays) {
        $errors[] = 'You must be a Respected Mobster to sell drugs';
    }
    if (count($errors)) {
        display_errors($errors);
    } else {
        $cost = $user_class->marijuana * 100;
        $db->query('UPDATE users SET money = money + ?, marijuana = 0 WHERE id = ?');
        $db->execute([$cost, $user_class->id]);
        echo Message('You sold all your weed and got '.formatCurrency($cost));
    }
}
$_GET['buy'] = array_key_exists('buy', $_GET) && in_array($_GET['buy'], ['cocaine', 'potseeds']) ? $_GET['buy'] : null;
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $errors = [];
    if ($cost > $user_class->money) {
        $errors[] = 'You don\'t have enough money';
    }
    if (!$user_class->rmdays) {
        $errors[] = 'You must be a Respected Mobster to buy drugs';
    }
    if (count($errors)) {
        display_errors($errors);
    } else {
        $gains = ['cocaine' => 1, 'potseeds' => 100];
        $db->query('UPDATE users SET money = money - ?, '.$_GET['buy'].' = '.$_GET['buy'].' + ? WHERE id = ?');
        $db->execute([$cost, $gains[$_GET['buy']], $user_class->id]);
        echo Message('You\'ve purchased some '.ucfirst($_GET['buy']));
    }
}
?><tr>
    <th class="content-head">Shady-Looking Stranger</th>
</tr>
<tr>
    <td class="content"><?php
if ($user_class->rmdays) {
    $csrfg = csrf_create('csrfg', false); ?>Hey there buddy. Want to buy some cocaine? It'll make you faster and help you pull off those bigger crimes! Best of all it will last you 15 minutes!<br />
        Cocaine is only <?php echo prettynum($cost, true); ?>, so what are you waiting for? Or perhaps you want to get into the drug dealing business yourself...<br />
        For <?php echo prettynum($cost, true); ?> I will give enough seeds to plant an acre of sweet sticky weed. I will also buy weed at <?php echo prettynum(100, true); ?> and ounce.<br /><br />
        <a href="buydrugs.php?buy=cocaine&amp;csrfg=<?php echo $csrfg; ?>">Buy Cocaine</a> |
        <a href="buydrugs.php?buy=potseeds&amp;csrfg=<?php echo $csrfg; ?>">Buy Marijuana Seeds</a> |
        <a href="buydrugs.php?sell=pot&amp;csrfg=<?php echo $csrfg; ?>">Sell all Weed</a> |
        <a href="city.php">You're a bad man, I'm leaving!</a><?php
} else {
        ?>Hmm... How do I know you won't squeal? You aren't respected enough to buy from me. Come back when you are a respected mobster.<br /><br />
        <a href="city.php">Back to the city</a><?php
    }
?></td>
</tr>
