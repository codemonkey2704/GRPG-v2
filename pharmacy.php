<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$drug = ['no-doze' => ['cost' => 10000, 'col' => 'nodoze'], 'steroids' => ['cost' => 2500, 'col' => 'genericsteroids']];
$errors = [];
$_GET['buy'] = array_key_exists('buy', $_GET) && in_array(strtolower($_GET['buy']), ['no-doze', 'steroids']) ? strtolower(trim($_GET['buy'])) : null;
if (!empty($_GET['buy'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($drug[$_GET['buy']]['cost'] > $user_class->money) {
        $errors[] = 'You don\'t have enough money';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET money = GREATEST(money - ?, 0), '.$drug[$_GET['buy']]['col'].' = '.$drug[$_GET['buy']]['col'].' + 1 WHERE id = ?');
        $db->execute([$drug[$_GET['buy']]['cost'], $user_class->id]);
        echo Message('You\'ve purchased a '.($_GET['buy'] === 'no-doze' ? 'No-Doze' : 'Steroid'));
    }
}
?><tr>
    <th class="content-head">Pharmacy</th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
$csrfg = csrf_create('csrfg', false);
?><tr>
    <td class="content">
        How may I help you? We offer quite a bit of medical supplies here for all your medical needs. I am of course assuming that these drugs won't be abused... We have a strict no drug-abuse policy here in Generica...
    </td>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="center">
            <tr>
                <td width="25%">
                    <img src="images/noimage.png" width="100" height="100" style="border: 1px solid #333;"><br />
                    No-Doze<br/>
                    <?php echo prettynum($drug['no-doze']['cost'], true); ?><br />
                    [<a href="pharmacy.php?buy=No-Doze&amp;csrfg=<?php echo $csrfg; ?>">Buy</a>]
                </td>
                <td width="25%">
                    <img src="images/noimage.png" width="100" height="100" style="border: 1px solid #333;"><br />
                    Generic Steroids<br />
                    <?php echo prettynum($drug['steroids']['cost'], true); ?><br />
                    [<a href="pharmacy.php?buy=Steroids&amp;csrfg=<?php echo $csrfg; ?>">Buy</a>]
                </td>
            </tr>
        </table>
    </td>
</tr>
