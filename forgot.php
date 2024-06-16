<?php
declare(strict_types=1);
global $securimage;
require_once __DIR__.'/inc/nliheader.php';
$errors = [];
$_POST['email'] = array_key_exists('email', $_POST) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
$_GET['token'] = array_key_exists('token', $_GET) && ctype_alnum($_GET['token']) ? $_GET['token'] : null;
if (!empty($_GET['token'])) {
    $db->query('SELECT id, userid, email FROM forgot_password WHERE token = ?');
    $db->execute([$_GET['token']]);
    if (!$db->count()) {
        $errors[] = 'Invalid token';
    }
    $row = $db->fetch(true);
    if (array_key_exists('submit', $_POST)) {
        if (!csrf_check('step_2', $_POST)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        if (defined('CAPTCHA_FORGOT_PASSWORD') && CAPTCHA_FORGOT_PASSWORD == true) {
            $_POST['captcha_code'] = array_key_exists('captcha_code', $_POST) && ctype_alnum($_POST['captcha_code']) ? $_POST['captcha_code'] : null;
            if (empty($_POST['captcha_code'])) {
                $errors[] = 'You didn\'t enter a valid captcha code';
            }
            if (!$securimage->check($_POST['captcha_code'])) {
                $errors[] = 'Invalid captcha code';
            }
        }
        if (empty($_POST['email'])) {
            $errors[] = 'You didn\'t enter a valid email address';
        }
        if ($_POST['email'] != $row['email']) {
            $errors[] = 'Invalid token/email combination';
        }
        $_POST['pass'] = array_key_exists('pass', $_POST) && is_string($_POST['pass']) ? $_POST['pass'] : null;
        if (empty($_POST['pass'])) {
            $errors[] = 'You didn\'t enter a valid password';
        }
        $_POST['conf'] = array_key_exists('conf', $_POST) && is_string($_POST['conf']) ? $_POST['conf'] : null;
        if (empty($_POST['conf'])) {
            $errors[] = 'You didn\'t enter a valid confirmation password';
        }
        if ($_POST['pass'] !== $_POST['conf']) {
            $errors[] = 'The passwords you entered didn\'t match';
        }
        if (!count($errors)) {
            $db->trans('start');
            $db->query('UPDATE users SET password = ? WHERE id = ?');
            $db->execute([password_hash($_POST['pass'], PASSWORD_BCRYPT), $row['userid']]);
            $db->query('DELETE FROM forgot_password WHERE email = ?');
            $db->execute([$_POST['email']]);
            $db->trans('end');
            echo Message('You\'ve changed your password');
        }
    } else {
        ?>
        <tr><th class="content-head">Password Reset</th></tr>
        <tr><td class="content">
            <form action="forgot.php?token=<?php echo $_GET['token']; ?>" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('step_2'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="email">Email</label>
                        <input type="text" name="email" id="email" />
                    </div>
                    <div class="pure-control-group">
                        <label for="pass">Password</label>
                        <input type="password" name="pass" id="pass" />
                    </div>
                    <div class="pure-control-group">
                        <label for="conf">Confirm Password</label>
                        <input type="password" name="conf" id="conf" />
                    </div><?php
                    if (defined('CAPTCHA_FORGOT_PASSWORD') && CAPTCHA_FORGOT_PASSWORD == true) {
                        ?>
                        <div class="pure-control-group">
                            <label for="captcha_code">Code</label>
                            <input type="text" name="captcha_code" size="10" maxlength="6" />
                        </div>
                        <div class="pure-control-group">
                            <img id="captcha" src="/inc/securimage/securimage_show.php" alt="CAPTCHA Image" /><br />
                            [<a href="#" onclick="document.getElementById('captcha').src = '/inc/securimage/securimage_show.php?' + Math.random(); return false">Different Image</a>]
                        </div> <?php
                    } ?>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="submit" class="pure-button pure-button-primary">Update Password</button>
                </div>
            </form>
        </td></tr> <?php
    }
    require_once __DIR__.'/inc/nlifooter.php';
    exit;
}
if (array_key_exists('submit', $_POST)) {
    if (!csrf_check('step_1', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (defined('CAPTCHA_FORGOT_PASSWORD') && CAPTCHA_FORGOT_PASSWORD == true) {
        $_POST['captcha_code'] = array_key_exists('captcha_code', $_POST) && ctype_alnum($_POST['captcha_code']) ? $_POST['captcha_code'] : null;
        if (empty($_POST['captcha_code'])) {
            $errors[] = 'You didn\'t enter a valid captcha code';
        }
        if (!$securimage->check($_POST['captcha_code'])) {
            $errors[] = 'Invalid captcha code';
        }
    }
    $_POST['name'] = array_key_exists('name', $_POST) && is_string($_POST['name']) ? strip_tags(trim($_POST['name'])) : null;
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter your username';
    }
    $db->query('SELECT id, email FROM users WHERE username = ?');
    $db->execute([$_POST['name']]);
    if (empty($_POST['email'])) {
        $errors[] = 'You didn\'t enter a valid email address';
    }
    $row = $db->fetch(true);
    $db->query('SELECT COUNT(id) FROM users WHERE email = ?');
    $db->execute([$_POST['email']]);
    if (!$db->count()) {
        $errors[] = 'An account with that email address doesn\'t exist';
    }
    if ($row['email'] != $_POST['email']) {
        $errors[] = 'Invalid combination';
    }
    if (!count($errors)) {
        $token = substr(md5((string)time()), 0, mt_rand(8, 10));
        $message = 'This message has been sent to you because you requested your gRPG account information to be updated.'."\n";
        $message .= 'Simply click this URL to start the password reset process: '.BASE_URL.'/forgot.php?token='.$token;
        $db->query('INSERT INTO forgot_password (userid, email, token) VALUES (?, ?, ?)');
        $db->execute([$row['id'], $row['email'], $token]);
        try {
            mail($row['email'], 'Account Info For gRPG', $message);
            echo Message('An email has been sent');
        } catch(Exception $e) {
            $errors[] = 'Failed to send email '.(DEBUG === true ? '; '.$e->getMessage() : '');
        }
    }
} ?>
<tr><th class="content-head">Account Recovery</th></tr><?php
if (count($errors)) {
    display_errors($errors);
} ?>
<tr><td class="content">
    Enter your e-mail address below and a confirmation token will be sent to your inbox. Don't forget to check your junk/bulk/spam folder if it doesn't arrive in your inbox.<br /><br />
    <form action="forgot.php" method="post" class="pure-form pure-form-aligned">
        <?php echo csrf_create('step_1'); ?>
        <fieldset>
            <div class="pure-control-group">
                <label for="name">Username</label>
                <input type="text" name="name" id="name" required autofocus />
            </div>
            <div class="pure-control-group">
                <label for="email">Email</label>
                <input type="text" name="email" id="email" />
            </div><?php
            if (defined('CAPTCHA_FORGOT_PASSWORD') && CAPTCHA_FORGOT_PASSWORD == true) {
                ?>
                <div class="pure-control-group">
                    <label for="captcha_code">Code</label>
                    <input type="text" name="captcha_code" size="10" maxlength="6" />
                </div>
                <div class="pure-control-group">
                    <img id="captcha" src="/inc/securimage/securimage_show.php" alt="CAPTCHA Image" /><br />
                    [<a href="#" onclick="document.getElementById('captcha').src = '/inc/securimage/securimage_show.php?' + Math.random(); return false">Different Image</a>]
                </div> <?php
            } ?>
        </fieldset>
        <div class="pure-controls">
            <button type="submit" name="submit" class="pure-button pure-button-primary">Send Reset Email</button>
        </div>
    </form>
</td></tr> <?php
require_once __DIR__.'/inc/nlifooter.php';
