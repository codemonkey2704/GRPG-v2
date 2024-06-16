<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$_GET['csrfg'] = array_key_exists('csrfg', $_GET) && ctype_alnum($_GET['csrfg']) ? $_GET['csrfg'] : null;
if (array_key_exists('pull', $_GET)) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $won = false;
    if ($user_class->money < 100) {
        echo Message('You don\'t have enough money to play slots.', 'Error', true);
    }
    $money = $user_class->money - 100;
    $slot[1] = '<img src="images/slots/7.png">';
    $slot[2] = '<img src="images/slots/bar.png">';
    $slot[3] = '<img src="images/slots/cherries.png">';
    $slot1 = mt_rand(1, 3);
    $slot2 = mt_rand(1, 3);
    $slot3 = mt_rand(1, 3);
    if ($slot1 == $slot2 && $slot2 == $slot3) {
        $money += 1000;
        $won = true;
    }
    $db->query('UPDATE users SET money = ? WHERE id = ?');
    $db->execute([$money, $user_class->id]); ?><tr>
        <th class="content-head">Spin Results</th>
    </tr>
    <tr>
        <td class="content" class="center"><?php echo $slot[$slot1].$slot[$slot2].$slot[$slot3]; ?></td>
    </tr><?php
    echo Message($won ? 'You won '.prettynum(900, true) : 'You didn\'t win');
}
?><tr>
    <th class="content-head">Slot Machine</th>
</tr>
<tr>
    <td class="content">
        <?php $csrfg = csrf_create('csrfg', false); ?>
        So, you fancy a try at the slot machine? Well, it just <?php echo prettynum(100, true); ?> a pull, so have at it.<br /><br />
        <a href="slots.php?pull=lever&amp;csrfg=<?php echo $csrfg; ?>">Pull Lever</a>
    </td>
</tr>
