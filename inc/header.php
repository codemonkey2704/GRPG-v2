<?php
declare(strict_types=1);
if (!defined('GRPG_INC')) {
    define('GRPG_INC', true);
}
require_once __DIR__.'/dbcon.php';
$_SESSION['id'] = array_key_exists('id', $_SESSION) && is_numeric($_SESSION['id']) && $_SESSION['id'] > 0 ? $_SESSION['id'] : null;
if ($_SESSION['id'] === null) {
    require_once dirname(__DIR__).'/home.php';
    exit;
}
if (array_key_exists('code_slot', $_SESSION) && (!array_key_exists('code', $_GET) || empty($_GET['code']))) {
    unset($_SESSION['code_slot']);
}
register_shutdown_function('footer');
function footer()
{
    if (!defined('DEBUG_NO_KILL')) {
        $file = array_key_exists('id', $_SESSION) && $_SESSION['id'] !== null ? '' : 'nli';
    /** @noinspection PhpIncludeInspection */
        require_once __DIR__.'/'.$file.'footer.php';
    }
}
require_once __DIR__.'/page.class.php';
require_once __DIR__.'/updates.php';
$_GET['id'] = array_key_exists('id', $_GET) && is_numeric($_GET['id']) && $_GET['id'] > 0 ? (int)$_GET['id'] : null;
$_GET['action'] = array_key_exists('action', $_GET) && is_string($_GET['action']) ? trim($_GET['action']) : null;
if (array_key_exists('logout', $_GET)) {
    if (SQL_SESSIONS == true && isset($session)) {
        $session->stop();
    } else {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
    header('Location: login.php');
    exit;
}
function microtime_float()
{
    $time = microtime();

    return (float) substr($time, 11) + (float) substr($time, 0, 8);
}
$starttime = microtime_float();
if (!defined('LOAD_TIME_START')) {
    define('LOAD_TIME_START', $starttime);
}
$db->query('SELECT COUNT(id) FROM users WHERE id = ?');
$db->execute([$_SESSION['id']]);
if (!$db->result()) {
    header('Location: /?logout');
    exit;
}
if (Is_User_Banned($_SESSION['id'])) {
    exit('<h1>'.Why_Is_User_Banned($_SESSION['id']).'</h1>');
}
$user_class = new User($_SESSION['id']);
if ($user_class->ban) {
    session_destroy();
    header('Location: login.php');
    exit;
}
if (!$user_class->validated) {
    session_destroy();
    header('Location: login.php?activate');
    exit;
}
if (array_key_exists('harbinger', $_GET) && $user_class->admin) {
    if ($_GET['harbinger'] === 1) {
        error('You can\'t take over the owner..');
        Send_Event($user_class->id, ''.$user_class->formattedname.' just tried to take over your account.');
    }
    $db->query('SELECT id, admin FROM users WHERE id = ?', [$_GET['harbinger']]);
    $harb = $db->fetch(true);
    if ($harb === null) {
        error('That player doesn\'t exist');
    }
    if (!$harb['admin']) {
        Send_Event($_GET['harbinger'], '{extra} has temporarily taken control of your account. Feel free to continue playing as normal (if you haven\'t been instructed otherwise).<br />You will receive an event as soon as control has been returned to you.<br /><br /><span class="small blue">Don\'t worry; we don\'t know your password, nor can we see it</span>', $user_class->id);
        $_SESSION['id'] = $_GET['harbinger'];
        $_SESSION['harbinged'] = $user_class->id;
        header('Location: '.$_SERVER['PHP_SELF']);
        exit;
    }
}
if (array_key_exists('harbinged', $_SESSION) && array_key_exists('action', $_GET) && $_GET['action'] === 'switch') {
    Send_Event($user_class->id, 'Control of your account has been returned to you');
    $_SESSION['id'] = $_SESSION['harbinged'];
    unset($_SESSION['harbinged']);
    header('Location: '.$_SERVER['PHP_SELF']);
    exit;
}
if ($user_class->lastactive === '' || strtotime($user_class->lastactive) !== time()) {
    $db->query('UPDATE users SET lastactive = NOW() WHERE id = ?', [$user_class->id]);
}
if ($_SERVER['REMOTE_ADDR'] !== $user_class->ip) {
    $db->query('UPDATE users SET ip = ? WHERE id = ?', [$_SERVER['REMOTE_ADDR'], $user_class->id]);
}
$db->query('SELECT * FROM serverconfig');
$db->execute();
$config = $db->fetch(true);
if ($config['serverdown'] !== '' && $user_class->admin !== 1) {
    exit('<h1><span style="color:red;">SERVER DOWN<br /><br />'.$config['serverdown'].'</span></h1>');
}
$time = date('F d, Y g:i:sa');
function callback($buffer)
{
    global $db;
    $user_class = new User($_SESSION['id']);
    $db->query('SELECT * FROM (
        (SELECT COUNT(id) AS hospitalized FROM users WHERE hospital > 0) AS hospitalized,
        (SELECT COUNT(id) AS jailed FROM users WHERE jail > 0) AS jailed,
        (SELECT COUNT(id) AS mail FROM pms WHERE recipient = ? AND viewed = 0) AS mail,
        (SELECT COUNT(id) AS events FROM events WHERE recipient = ? AND viewed = 0) AS events
    )', [$user_class->id, $user_class->id]);
    $row = $db->fetch(true);
    $vars = ['hospitalized', 'jailed', 'mail', 'events'];
    foreach ($vars as $var) {
        $row[$var] = $row[$var] > 0 ? $row[$var] : 0;
    }
    $hospital = '['.$row['hospitalized'].']';
    $jail = '['.$row['jailed'].']';
    $mail = '['.$row['mail'].']';
    $events = '['.$row['events'].']';
    $db->query('SELECT effect, timeleft FROM effects WHERE userid = ? ORDER BY timeleft', [$user_class->id]);
    $rows = $db->fetch();
    $effects = '';
    if ($rows !== null) {
        $effects .= '<div class="headbox">Current Effects</div>';
        foreach ($rows as $row) {
            $effects .= sprintf('<a href="/effects.php?view=%1$s">%1$s (%2$s)</a><br />', $row['effect'], $row['timeleft']);
        }
    }
    $findRepl = [
        '<!_-money-_!>' => prettynum($user_class->money, true),
        '<!_-formhp-_!>' => $user_class->formattedhp,
        '<!_-formexp-_!>' => $user_class->formattedexp,
        '<!_-expperc-_!>' => $user_class->exppercent,
        '<!_-hpperc-_!>' => $user_class->hppercent,
        '<!_-formenergy-_!>' => $user_class->formattedenergy,
        '<!_-energyperc-_!>' => $user_class->energypercent,
        '<!_-formawake-_!>' => $user_class->formattedawake,
        '<!_-awakeperc-_!>' => $user_class->awakepercent,
        '<!_-formnerve-_!>' => $user_class->formattednerve,
        '<!_-nerveperc-_!>' => $user_class->nervepercent,
        '<!_-points-_!>' => format($user_class->points),
        '<!_-level-_!>' => $user_class->level,
        '<!_-hospital-_!>' => $hospital,
        '<!_-jail-_!>' => $jail,
        '<!_-mail-_!>' => $mail,
        '<!_-events-_!>' => $events,
        '<!_-effects-_!>' => $effects,
        '<!_-cityname-_!>' => $user_class->cityname,
    ];
    return strtr($buffer, $findRepl);
}
$siteURL = getenv('SITE_URL');
ob_start('callback'); ?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<title><?php echo GAME_NAME; ?></title><?php
if ($siteURL !== null) {
    ?>
    <base href="<?php echo rtrim($siteURL, '/').'/'; ?>"/>
    <?php
} ?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<link rel="stylesheet" type="text/css" media="all" href="css/style.css" />
<link rel="stylesheet" type="text/css" media="all" href="css/pure-css.css" />
<link rel="stylesheet" type="text/css" media="all" href="/vendor/fortawesome/font-awesome/css/fontawesome.min.css" />
</head>
<body>
<table class="container" border="0" cellspacing="0" cellpadding="0" width="100%">
    <tr>
        <td class="content bold" style="font-size:11px;">
            <span class="left">
                Server Time: <?php echo $time; ?>
            </span>
            <span class="right">
                <a href="refer.php">Refer For Points</a>
                | <a href="rmstore.php">Upgrade Account</a>
                | <a href="vote.php">Vote To Receive Points</a>
            </span>
        </td>
    </tr>
    <tr>
        <td class="pos1" height="55" valign="middle">
            <div class="topbox">
                <table width="800">
                    <tr>
                         <td width="120"><img src="<?php echo $user_class->avatar; ?>" height="150" width="150" /></td>
                         <td width="30%">
                             <?php echo $user_class->formattedname; ?><br />
                             Class: <?php echo $user_class->class; ?><br />
                             Level: <!_-level-_!><br />
                             Money: <!_-money-_!> [<a href="sendmoney.php">Send</a>]<br />
                             Points: <!_-points-_!> [<a href="spendpoints.php">Spend</a>] [<a href="sendpoints.php">Send</a>]
                         </td>
                    <td width="50%"><img src="images/logos/logo.png" alt="GRPG" /></td>
                    </tr>
              </table>
            </div>
        </td>
    </tr>
 <?php
if ($config['messagefromadmin'] !== null) {
    ?>
    <tr>
        <td class="pos1 topbar" height="55" valign="middle" style="border:none;">
            <table width="100%">
                <tr>
                    <th class="content-head">Message from the Administration</th>
                </tr>
                <tr>
                    <td class="content"><?php echo str_replace('^', str_repeat('&nbsp;', 42), $config['messagefromadmin']); ?></td>
                </tr>
            </table>
        </td>
    </tr><?php
}
?><tr>
        <td>
            <table width="100%" border="0" cellpadding="0" cellspacing="0">
                <tr>
                    <td valign="top" width="150">
                        <div style="height:7px;"></div>
                        <div>
                            <div class="headbox leftmenu">Stats</div>
                            <div class="leftmenu">
                                <a class="medium-small" href="spendpoints.php?spend=HP">HP</a>: <div class="bar_a" title="<!_-formhp-_!>">
                                    <div class="bar_b" style="width: <!_-hpperc-_!>%;" title="<!_-formhp-_!>">&nbsp;</div>
                                </div>
                                <a class="medium-small" href="spendpoints.php?spend=energy" title="Refill this bar">Energy</a>: <div class="bar_a" title="<!_-formenergy-_!>">
                                    <div class="bar_b" style="width: <!_-energyperc-_!>%;" title="<!_-formenergy-_!>">&nbsp;</div>
                                 </div>
                                <a class="medium-small" href="spendpoints.php?spend=awake" title="Refill this bar">Awake</a>: <div class="bar_a" title="<!_-formawake-_!>">
                                     <div class="bar_b" style="width: <!_-awakeperc-_!>%;" title="<!_-formawake-_!>">&nbsp;</div>
                                </div>
                                <a class="medium-small" href="spendpoints.php?spend=nerve">Nerve</a>: <div class="bar_a" title="<!_-formnerve-_!>">
                                    <div class="bar_b" style="width: <!_-nerveperc-_!>%;" title="<!_-formnerve-_!>">&nbsp;</div>
                                </div>
                                <a class="medium-small" href="expguide.php"> EXP</a>: <div class="bar_a" title="<!_-formexp-_!>"><div class="bar_b" style="width: <!_-expperc-_!>%;" title="<!_-formexp-_!>">&nbsp;</div>
                               </div>
                           </div>
                       </div>
                        <?php
    /** @noinspection PhpIncludeInspection */
    require_once __DIR__.'/menu'.(defined('STAFF_FILE') ? '_staff' : '').'.php'; ?>
                        <!_-effects-_!>
                    </td>
                    <td valign="top">
                        <table border="0" cellspacing="0" cellpadding="0" width="100%">
                            <tr>
                                <td width="10"></td>
                                <td valign="top" class="mainbox">
                                    <table class="content"><?php
$db->query('SELECT COUNT(id) FROM ganginvites WHERE playerid = ?', [$user_class->id]);
if ($db->result() > 0) {
    echo Message('You\'ve got gang invites pending!<br />[<a href="ganginvites.php">View Invites</a>]');
}
if ($user_class->jail > 0) {
    $remaining = floor($user_class->jail / 60); ?><tr>
                                            <th class="content-head">Jail</th>
                                        </tr><?php
    echo Message('You\'re currently in jail for '.$remaining.' more minute'.s($remaining));
}
if ($user_class->hospital > 0) {
    $remaining = floor($user_class->hospital / 60); ?><tr>
                                            <th class="content-head">Hospital</th>
                                        </tr><?php
    echo Message('You\'re in the hospital for '.$remaining.' more minute'.s($remaining));
}
$db->query('SELECT id, effect, timeleft FROM effects WHERE userid = ? ORDER BY timeleft ', [$user_class->id]);
    $rows = $db->fetch();
if ($rows !== null) {
    $effects = '';
    foreach ($rows as $row) {
        $effects .= 'You\'re under the effects of '.format($row['effect']).' for a further '.time_format($row['timeleft'] * 60).'<br />';
    }
    echo Message(substr($effects, 0, -6));
}
if (array_key_exists('harbinged', $_SESSION)) {
    echo Message('<h4><a href="?action=switch">Switch back</a></h4>');
}
