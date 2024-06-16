<?php
declare(strict_types=1);
require_once __DIR__.'/inc/nliheader.php';
$_GET['email'] = array_key_exists('email', $_GET) && is_string($_GET['email']) && strlen($_GET['email']) > 0 ? $_GET['email'] : null;
$_GET['token'] = array_key_exists('token', $_GET) && is_string($_GET['token']) && strlen($_GET['token']) > 0 ? $_GET['token'] : null;
$output = '';
if ($_GET['email'] !== null) {
    if ($_GET['token'] !== null) {
        $email = base64_decode($_GET['email']);
        $db->query('SELECT * FROM pending_validations WHERE email = ? AND validation_code = ? AND time_added >= DATE_SUB(NOW(), INTERVAL 1 DAY)');
        $db->execute([$email, $_GET['token']]);
        $row = $db->fetch(true);
        if ($row !== null) {
            $db->trans('start');
            $db->query('INSERT INTO users (ip, username, password, email, class) VALUES (?, ?, ?, ?, ?)');
            $db->execute([$_SERVER['REMOTE_ADDR'], $row['username'], $row['password'], $row['email'], $row['class']]);
            $userid = $db->id();
            $_POST['referer'] = array_key_exists('referer', $_POST) && ctype_digit($_POST['referer']) && $_POST['referer'] > 0 ? $_POST['referer'] : null;
            if ($_POST['referer'] !== null) {
                $db->query('SELECT COUNT(id) FROM users WHERE id = ?');
                $db->execute([$_POST['referrer']]);
                if ($db->result()) {
                    $db->query('INSERT INTO referrals (referrer, referred) VALUES (?, ?)');
                    $db->execute([$_POST['referrer'], $userid]);
                }
            }
            $db->query('DELETE FROM pending_validations WHERE id = ?');
            $db->execute([$row['id']]);
            $db->trans('end');
            $output = 'Your account has been validated successfully! Redirecting to login page in 5 seconds. <meta http-equiv="refresh" content="5;url=login.php">';
        } else {
            $output = 'Either that email/token combination doesn\'t exist or it has expired';
        }
    } else {
        $output = 'You didn\'t supply a valid token';
    }
} else {
    $output = 'You didn\'t supply a valid email';
} ?>
<tr>
    <td class="content">
        <?php echo $output; ?>
        <div class="center">&copy; GenericRPG 2007-<?php echo date('Y'); ?> GRPG Dev Team</div>
    </td>
</tr><?php
require_once __DIR__.'/inc/nlifooter.php';
