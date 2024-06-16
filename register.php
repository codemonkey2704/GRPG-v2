<?php
declare(strict_types=1);
require_once __DIR__.'/inc/nliheader.php';
error_reporting(E_ALL);
$classes = ['Mastermind', 'Assassin', 'Bodyguard', 'Smuggler', 'Thief'];
$errors = [];
$registration = settings('registration');
if (array_key_exists('submit', $_POST) && $registration === 'open') {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (defined('CAPTCHA_REGISTRATION') && CAPTCHA_REGISTRATION == true) {
        $_POST['captcha_code'] = array_key_exists('captcha_code', $_POST) && ctype_alnum($_POST['captcha_code']) ? $_POST['captcha_code'] : null;
        if (empty($_POST['captcha_code'])) {
            $errors[] = 'You didn\'t enter a valid captcha code';
        }
        require_once __DIR__.'/inc/securimage/securimage.php';
        $securimage = new Securimage();
        if (!$securimage->check($_POST['captcha_code'])) {
            $errors[] = 'Invalid captcha code';
        }
    }
    $_POST['username'] = array_key_exists('username', $_POST) && is_string($_POST['username']) ? strip_tags(trim($_POST['username'])) : null;
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $len = strlen($_POST['username']);
    if ($len < 4 || $len > 20) {
        $errors[] = 'Usernames must be between 4 and 20 characters';
    }
    $db->query('SELECT COUNT(id) FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if ($db->result()) {
        $errors[] = 'That username has already been taken';
    }
    $signuptime = time();
    $_POST['pass'] = array_key_exists('pass', $_POST) && is_string($_POST['pass']) ? $_POST['pass'] : null;
    if (empty($_POST['pass'])) {
        $errors[] = 'You didn\'t enter a valid password';
    }
    $_POST['conf_pass'] = array_key_exists('conf_pass', $_POST) && is_string($_POST['conf_pass']) ? $_POST['conf_pass'] : null;
    if (empty($_POST['conf_pass'])) {
        $errors[] = 'You didn\'t enter a valid confirmation password';
    }
    if ($_POST['pass'] !== $_POST['conf_pass']) {
        $errors[] = 'The passwords you entered didn\'t match. Passwords are case-sensitive';
    }
    $_POST['email'] = array_key_exists('email', $_POST) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ? $_POST['email'] : null;
    if (empty($_POST['email'])) {
        $errors[] = 'You didn\'t enter a valid email address';
    }
    $db->query('SELECT COUNT(id) FROM users WHERE email = ?');
    $db->execute([$_POST['email']]);
    if ($db->result()) {
        $errors[] = 'That email is already in use';
    }
    $_POST['class'] = array_key_exists('class', $_POST) && in_array($_POST['class'], $classes) ? $_POST['class'] : null;
    if (empty($_POST['class'])) {
        $errors[] = 'You didn\'t select a valid class';
    }
    if (!count($errors)) {
        $validationCode = substr(md5(microtime(true)), 0, 15);
        $pass = password_hash($_POST['pass'], PASSWORD_BCRYPT);
        $db->trans('start');
        $db->query('INSERT INTO pending_validations (ip, username, password, email, class, validation_code) VALUES (?, ?, ?, ?, ?, ?)');
        $db->execute([$_SERVER['REMOTE_ADDR'], $_POST['username'], $pass, $_POST['email'], $_POST['class'], $validationCode]);
        $db->trans('end');
        $message = 'You\'ve received this email because your email address was used to sign up to '.GAME_NAME."\n".
        'If you didn\'t do that, then just ignore this message'."\n".
        'If you did, then awesome! Simply visit the URL below to validate your account'."\n\n".
        BASE_URL.'validate.php?email='.base64_encode($_POST['email']).'&token='.$validationCode;
        if (mail($_POST['email'], GAME_NAME.' Validation', $message, 'From: '.DEFAULT_EMAIL_ADDRESS)) {
            $output = 'A validation message has been sent to '.format($_POST['email']).'. It\'ll remain valid for 24 hours';
        } else {
            $db->query('INSERT INTO tickets (subject, body) VALUES (\'Failed to send validation email\', ?)');
            $db->execute(['Email: '.$_POST['email']."\n".'Validation Code: '.$validationCode]);
            $output = 'A validation email couldn\'t be sent. A support ticket has been generated for you';
        }
        echo Message($output, null, true);
    }
}
if (count($errors)) {
    display_errors($errors);
}
$_GET['referer'] = array_key_exists('referer', $_GET) && ctype_digit($_GET['referer']) ? $_GET['referer'] : null;
?><tr>
    <th class="content-head">.: Register</th>
</tr>
<tr>
    <td class="content"><?php
    if ($registration === 'open') {
        ?>
        <form action="register.php" method="post" class="pure-form pure-form-aligned"><?php
echo csrf_create();
        if (!empty($_GET['referer'])) {
            ?><input type="hidden" name="referer" value="<?php echo $_GET['referer']; ?>" /><?php
        } ?>
<legend>Account Setup</legend>
<fieldset>
    <div class="pure-control-group">
        <label for="username">Username</label>
        <input type="text" name="username" id="username" />
    </div>
    <div class="pure-control-group">
        <label for="pass">Password</label>
        <input type="password" name="pass" id="pass" autocomplete="off" />
    </div>
    <div class="pure-control-group">
        <label for="conf_pass">Confirm Password</label>
        <input type="password" name="conf_pass" id="conf_pass" autocomplete="off" />
    </div>
    <div class="pure-control-group">
        <label for="email">Email address</label>
        <input type="text" name="email" id="email" />
    </div>
    <div class="pure-control-group">
        <label for="class">Class</label>
        <select name="class" id="class"><?php
foreach ($classes as $opt) {
            printf('<option value="%1$s">%1$s</option>', $opt);
        } ?></select>
                </div>
            </fieldset><?php
if (defined('CAPTCHA_REGISTRATION') && CAPTCHA_REGISTRATION == true) {
            ?><legend>Captcha</legend>
                <fieldset>
                    <div class="pure-control-group">
                        <img id="captcha" src="/inc/securimage/securimage_show.php" alt="CAPTCHA Image" /><br />
                        <input type="text" name="captcha_code" id="captcha_code" size="10" maxlength="6" />
                        [<a href="#" onclick="document.getElementById('captcha').src = '/inc/securimage/securimage_show.php?' + Math.random(); return false">Different Image</a>]
                    </div>
                </fieldset><?php
        } ?><div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Register</button>
            </div>
        </form><br /><?php
    } else {
        ?>
        Registration is currently closed<br /><?php
    } ?>
        <span class="center">&copy; GenericRPG 2007-<?php echo date('Y'); ?> GRPG Dev Team</span>
    </td>
</tr><?php
require_once __DIR__.'/inc/nlifooter.php';
