<?php
declare(strict_types=1);
global $securimage;
error_reporting(E_ALL);
require_once __DIR__.'/inc/nliheader.php';
$csrfError = false;
if(array_key_exists('activate', $_GET)) {
    echo Message('You haven\'t activated your account', 'Error', true);
}
if (array_key_exists('submit', $_POST)) {
    if (csrf_check('csrf_login', $_POST)) {
        if (defined('CAPTCHA_LOGIN') && CAPTCHA_LOGIN === true) {
            $_POST['captcha_code'] = array_key_exists('captcha_code', $_POST) && ctype_alnum($_POST['captcha_code']) ? $_POST['captcha_code'] : null;
            if (empty($_POST['captcha_code'])) {
                echo Message('You didn\'t enter a valid captcha code', 'Error', true);
            }
            if (!$securimage->check($_POST['captcha_code'])) {
                echo Message('Invalid captcha code', 'Error', true);
            }
        }
        $_POST['username'] = array_key_exists('username', $_POST) && is_string($_POST['username']) ? strtolower(strip_tags(trim($_POST['username']))) : null;
        if (empty($_POST['username'])) {
            echo Message('You didn\'t enter your username', 'Error', true);
        }
        $_POST['password'] = array_key_exists('password', $_POST) && is_string($_POST['password']) ? $_POST['password'] : null;
        if (empty($_POST['password'])) {
            echo Message('You didn\'t enter your password', 'Error', true);
        }
        $db->query('SELECT id, password, ban FROM users WHERE LOWER(username) = ?', [$_POST['username']]);
        $row = $db->fetch(true);
        if ($row === null) {
            echo Message('That account wasn\'t found', 'Error', true);
        }
        if ($row['ban']) {
            echo Message('You\'ve been banned from the game', 'Error', true);
        }
        $db->query('SELECT COUNT(id) FROM pending_validations WHERE username = ?', [$_POST['username']]);
        if ($db->result()) {
            echo Message('You haven\'t validated your email address', 'Error', true);
        }
        if (!password_verify($_POST['password'], $row['password'])) {
            if (!MD5_COMPATIBILITY) {
                echo Message('Invalid details', 'Error', true);
            }
            if (md5($_POST['password']) != $row['password']) {
                echo Message('Invalid details', 'Error', true);
            }
        }
        if (MD5_COMPATIBILITY && MD5_COMPAT_UPDATE && $row['password'] === md5($_POST['password'])) {
            $db->query('UPDATE users SET password = ? WHERE id = ?', [password_hash($_POST['password'], PASSWORD_BCRYPT), $row['id']]);
        }
        $_SESSION['id'] = (int)$row['id'];
        ob_end_clean();
        header('Location: index.php');
        exit;
    } else {
        $csrfError = true;
    }
}
?><tr>
    <th class="content-head">.: Login :.</th>
</tr><?php
if ($csrfError) {
    echo Message(SECURITY_TIMEOUT_MESSAGE, 'Error', null);
}
?><tr>
    <td class="content">
        <form action="login.php" name="login" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create('csrf_login'); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" size="22" required autofocus />
                </div>
                <div class="pure-control-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" size="22" required autocomplete="off" />
                </div><?php
if (defined('CAPTCHA_LOGIN') && CAPTCHA_LOGIN == true) {
    ?><div class="pure-control-group">
                        <label for="captcha_code">Code</label>
                        <input type="text" name="captcha_code" size="10" maxlength="6" />
                    </div>
                    <div class="pure-control-group">
                        <img id="captcha" src="/inc/securimage/securimage_show.php" alt="CAPTCHA Image" /><br />
                        [<a href="#" onclick="document.getElementById('captcha').src = '/inc/securimage/securimage_show.php?' + Math.random(); return false">Different Image</a>]
                    </div><?php
} ?>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Login</button>
            </div>
        </form><br />
        <div class="center">&copy; GenericRPG 2007-<?php echo date('Y'); ?> GRPG Dev Team</div>
    </td>
</tr><?php
require_once __DIR__.'/inc/nlifooter.php';
