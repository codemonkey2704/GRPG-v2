<?php
declare(strict_types=1);
/* Character class mod v1.00
 * this file gym.php is
 * already part of grpg script
 * and has been modified and
 * secured for the above
 * mentioned mod.
*/
require_once __DIR__.'/inc/header.php';
checkUserStatus();
$db->query('SELECT COUNT(id) FROM effects WHERE userid = ?');
$db->execute([$user_class->id]);
if ($db->result()) {
    echo Message('You can\'t train at the gym if you have an effect.', 'Error', true);
}
$_POST['type'] = array_key_exists('type', $_POST) && in_array($_POST['type'], [1, 2, 3]) ? $_POST['type'] : null;
$_POST['energy'] = array_key_exists('energy', $_POST) && ctype_digit($_POST['energy']) ? $_POST['energy'] : null;
if (array_key_exists('train', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['energy'])) {
        echo Message('Please enter a valid amount to train', 'Error', true);
    }
    if ($_POST['energy'] > $user_class->energy) {
        echo Message('You don\'t have that much energy.', 'Error', true);
    }
    if ($user_class->class === 'Mastermind') {
        $strengthbonus = .03;
        $defensebonus = .03;
        $speedbonus = .02;
    } elseif ($user_class->class === 'Assassin') {
        $strengthbonus = .1;
        $defensebonus = .01;
        $speedbonus = .02;
    } elseif ($user_class->class === 'Bodyguard') {
        $strengthbonus = .03;
        $defensebonus = .1;
        $speedbonus = .02;
    } elseif ($user_class->class === 'Smuggler') {
        $strengthbonus = .01;
        $defencebonus = .02;
        $speedbonus = .1;
    } elseif ($user_class->class === 'Thief') {
        $strengthbonus = .02;
        $defensebonus = .01;
        $speedbonus = .1;
    }
    $db->trans('start');
    $awakeEquation = floor($_POST['energy'] * ($user_class->awake / 100 * 3.14 / 2));
    if ($_POST['type'] == 1) /* Strength Train */ {
        $strength = $awakeEquation + floor($awakeEquation * $strengthbonus) + mt_rand($user_class->level, $user_class->level * 10);
        $db->query('UPDATE users SET strength = strength + ? WHERE id = ?');
        $db->execute([$strength, $user_class->id]);
        echo Message('You trained with '.$_POST['energy'].' energy and received '.format($strength).' strength.');
    } elseif ($_POST['type'] == 2) /* Defense Train */ {
        $defense = $awakeEquation + floor($awakeEquation * $defensebonus) + mt_rand($user_class->level, $user_class->level * 10);
        $db->query('UPDATE users SET defense = defense + ? WHERE id = ?');
        $db->execute([$defense, $user_class->id]);
        echo Message('You trained with '.$_POST['energy'].' energy and received '.format($defense).' defense.');
    } elseif ($_POST['type'] == 3) /* Speed Train */ {
        $speed = $awakeEquation + floor($awakeEquation * $speedbonus) + mt_rand($user_class->level, $user_class->level * 10);
        $db->query('UPDATE users SET speed = speed + ? WHERE id = ?');
        $db->execute([$speed, $user_class->id]);
        echo Message('You trained with '.$_POST['energy'].' energy and received '.format($speed).' speed');
    }
    $db->query('UPDATE users SET awake = GREATEST(awake - ?, 0), energy = GREATEST(energy - ?, 0) WHERE id = ?');
    $db->execute([$_POST['energy'] * 2, $_POST['energy'], $user_class->id]);
    $db->trans('end');
    $user_class = new User($_SESSION['id']);
}
?><tr>
    <th class="content-head">Gym</th>
</tr>
<tr>
    <td class="content">You can currently train <?php echo format($user_class->energy); ?> time<?php echo s($user_class->energy); ?></td>
</tr>
<tr>
    <td class="content">
        <form action="gym.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <input type="text" name="energy" value="<?php echo $user_class->energy; ?>" size="5" maxlength="5" />
                </div>
                <div class="pure-control-group">
                    <select name="type">
                        <option value="1">Strength</option>
                        <option value="2">Defense</option>
                        <option value="3">Speed</option>
                    </select>
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="train" class="pure-button pure-button-primary">Train</button>
            </div>
        </form>
    </td>
</tr>
<tr>
    <th class="content-head">Attributes</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <tr>
                <th width="12.5%">Strength:</th>
                <td width="37.5%"><?php echo format($user_class->strength); ?></td>
                <th width="12.5%">Defense:</th>
                <td width="37.5%"><?php echo format($user_class->defense); ?></td>
            </tr>
            <tr>
                <th>Speed:</th>
                <td><?php echo format($user_class->speed); ?></td>
                <th>Total:</th>
                <td><?php echo format($user_class->totalattrib); ?></td>
            </tr>
        </table>
    </td>
</tr>
