<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
if (!$user_class->gang) {
    echo Message('You\'re not in a gang', 'Error', true);
}
$gang_class = new Gang($user_class->gang);
if ($gang_class->leader != $user_class->id) {
    echo Message('You don\'t have authorization to be here.', 'Error', true);
}
$errors = [];
$_GET['dismiss'] = array_key_exists('dismiss', $_GET) && ctype_digit($_GET['dismiss']) ? $_GET['dismiss'] : null;
if (!empty($_GET['dismiss'])) {
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($_GET['dismiss'] == $user_class->id) {
        $errors[] = 'You can\'t kick yourself from the gang';
    }
    if (!userExists($_GET['dismiss'])) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $target = new User($_GET['dismiss']);
    if ($target->gang != $gang_class->id) {
        $errors[] = $target->formattedname.' isn\'t in your gang';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET gang = 0 WHERE id = ?');
        $db->execute([$_GET['dismiss']]);
        Send_Event($_GET['dismiss'], 'You were kicked from '.$gang_class->formattedname);
        $db->trans('end');
        echo Message('You\'ve kicked '.$target->formattedname.' from the gang');
    }
}
$db->query('SELECT id FROM users WHERE gang = ? AND id <> ? ORDER BY experience DESC');
$db->execute([$gang_class->id, $user_class->id]);
$rows = $db->fetch();
?><tr>
    <th class="content-head"><?php echo $gang_class->formattedname; ?></th>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
?><tr>
    <td class="content">
        <table width="100%">
            <tr>
                <td>Mobster</td>
                <td>Kick Out</td>
            </tr><?php
if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            $gang_member = new User($row['id']); ?><tr>
                    <td><?php echo $gang_member->formattedname; ?></td>
                    <td><a href="managegang.php?dismiss=<?php echo $row['id']; ?>&amp;csrfg=<?php echo $csrfg; ?>">Kick Out</a></td>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="2" class="center">There aren't any other members of your gang</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
