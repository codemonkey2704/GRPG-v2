<?php
declare(strict_types=1);
define('GRPG_INC', true);
define('NO_SESSION', true);
define('NO_CLASSES', true);
define('NO_FUNCTIONS', true);
define('NO_CSRF', true);
define('INSTALLER', true);
require_once __DIR__ . '/header.php';
function error($msg)
{
    ?>
    <div class="notification notification-error">
        <span class="fa fa-times-circle"></span>
        <p><?php echo $msg; ?></p>
    </div>
    <?php
    exit;
}

function success($msg)
{
    ?>
    <div class="notification notification-success">
        <span class="fa fa-check-circle"></span>
        <p><?php echo $msg; ?></p>
    </div>
    <?php
}

function info($msg)
{
    ?>
    <div class="notification notification-info">
        <span class="fas fa-info-circle"></span>
        <p><?php echo $msg; ?></p>
    </div>
    <?php
}

function warning($msg)
{
    ?>
    <div class="notification notification-secondary">
        <span class="fa fa-secondary-circle"></span>
        <p><?php echo $msg; ?></p>
    </div>
    <?php
}

function checkInstallation()
{
    $installed = getenv('INSTALLER_RAN');
    if ($installed !== null && $installed === 'true') {
        info('It looks like your game may have already been installed.. Please check that <code>/.env</code> contains the correct information');
        exit;
    }
}

function isFunctionAvailable($func)
{
    /** @noinspection DeprecatedIniOptionsInspection */
    if (ini_get('safe_mode')) {
        return false;
    }
    $disabledFunctions = ini_get('disable_functions');
    if (is_array($disabledFunctions) && count($disabledFunctions) > 0) {
        $disabledFunctions = array_map('trim', explode(',', $disabledFunctions));
        return !in_array($func, $disabledFunctions, true);
    }
    return true;
}
function commandExists($command) {
    return shell_exec($command) !== false;
}

if (!defined('PHP_VERSION_ID')) {
    $version = (array)explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}
function formatTimeZones(array $zones)
{
    if (!count($zones)) {
        return 'Something screwed up..';
    }
    $locations = [];
    $continents = [
        'Africa',
        'America',
        'Antarctica',
        'Arctic',
        'Asia',
        'Atlantic',
        'Australia',
        'Europe',
        'Indian',
        'Pacific',
    ];
    foreach ($zones as $zone) {
        $zone = (array)explode('/', $zone);
        // Only use "friendly" continent names
        if (isset($zone[1]) && $zone[1] !== '' && in_array($zone[0], $continents, true)) {
            $locations[$zone[0]][$zone[0] . '/' . $zone[1]] = str_replace('_', ' ', $zone[1]);
        }
    }

    return $locations;
}

function listTimeZones(array $list, $name = 'timezone')
{
    if (!count($list)) {
        return 'Something screwed up...';
    }
    $ret = '<select name="' . $name . '">';
    $cnt = 0;
    foreach ($list as $key => $val) {
        ++$cnt;
        $ret .= "\n" . '<option value="0" disabled ' . ($cnt === 1 ? 'selected' : '') . '>------ ' . $key . ' -------</option>';
        foreach ($val as $zone => $show) {
            $ret .= "\n" . '<option value="' . $zone . '">' . $show . '</option>';
        }
    }
    $ret .= '</select>';

    return $ret;
}

$mainPath = dirname(__DIR__);
$paths = (array)explode(DIRECTORY_SEPARATOR, $mainPath);
$path = end($paths);

$sqlPathMain = __DIR__ . DIRECTORY_SEPARATOR . 'sqls' . DIRECTORY_SEPARATOR . 'grpg-pdo.sql';
$configFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
$steps = [1, 2, 3, 4, 5, 6, 7, 8];
$_GET['step'] = isset($_GET['step']) && is_numeric($_GET['step']) && in_array($_GET['step'], $steps,
    false) ? $_GET['step'] : 1; ?>
<div class="header">
    <h1>gRPG: PDO</h1>
    <h2>Installation</h2>
    <h3>Progress: <span class="pure-u-<?php echo $_GET['step']; ?>-<?php echo count($steps); ?>"></span></h3>
    <p>Step <?php echo $_GET['step']; ?> of <?php echo count($steps); ?></p>
</div>
<div class="content"><?php
    switch ($_GET['step']) {
        default:
        case 1:
            checkInstallation();
            ?><h2 class="content-subhead">Let's do some checks first...</h2>
            <form action="install.php?step=2" method="post" class="pure-form pure-form-aligned">
                <div class="pure-control-group">
                    <label for="version">PHP Version</label>
                    <span id="version"
                          class="<?php echo PHP_VERSION_ID >= 70400 ? 'green' : 'red'; ?>"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="pure-control-group">
                    <label for="sql-file">SQL File</label>
                    <span id="sql-file"><?php echo is_file($sqlPathMain) ? '<span class="green">Exists</span>' : '<span class="red">Doesn\'t exist!</span>'; ?></span>
                </div>
                <div class="pure-control-group">
                    <label for="game-dir">Game Directory</label>
                    <input type="text" name="gamedir" id="game-dir" value="<?php echo DIRECTORY_SEPARATOR; ?>"/>
                </div>
                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary">Check</button>
                </div>
            </form>
            <p>
                *<strong>Game Directory:</strong> This is simply where you've uploaded the game - this is usually
                <code>/</code>.<br/>
                Make sure that <code>/.env</code> is writable.<br/>
                If you're not sure, just leave it blank.
            </p>
            <?php
            break;
        case 2:
            checkInstallation();
            $_POST['gamedir'] = isset($_POST['gamedir']) && is_string($_POST['gamedir']) ? $_POST['gamedir'] : null;
            $path = ltrim(DIRECTORY_SEPARATOR . (!empty($_POST['gamedir']) ? $_POST['gamedir'] : DIRECTORY_SEPARATOR),
                DIRECTORY_SEPARATOR);
            $path = str_replace([DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, '\\' . DIRECTORY_SEPARATOR, '/'],
                DIRECTORY_SEPARATOR, $mainPath . $path);
            if (!is_dir($path)) {
                error('That\'s not a valid directory path: ' . $path);
            }
            if (!is_dir($path)) {
                error('I couldn\'t find that directory. Are you sure you\'ve entered the correct game path?');
            }
            $timezones = formatTimeZones(timezone_identifiers_list());
            ?><h2 class="content-subhead">That checks out fine!</h2>
            <p>
            <form action="install.php?step=3" method="post" class="pure-form pure-form-aligned">
                <input type="hidden" name="gamedir" value="<?php echo $path; ?>"/>
                <legend>Database Configuration</legend>
                <div class="pure-control-group">
                    <label for="host">Host</label>
                    <input type="text" name="host" id="host" value="localhost"/>
                </div>
                <div class="pure-control-group">
                    <label for="user">User</label>
                    <input type="text" name="user" id="user" placeholder="root"/>
                </div>
                <div class="pure-control-group">
                    <label for="pass">Password</label>
                    <input type="password" name="pass" id="pass"/>
                </div>
                <div class="pure-control-group">
                    <label for="name">Database</label>
                    <input type="text" name="name" id="name"/>
                </div>
                <div class="pure-control-group">
                    <label for="offset">Time Offset</label>
                    <?php echo listTimeZones($timezones); ?>
                </div>
                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary">Connect</button>
                </div>
            </form>
            <p>
                *<strong>Host:</strong> This speaks for itself. You need to enter the URL to your MySQL database.<br/>
                &nbsp;&nbsp;&nbsp;&nbsp;- For most people, it's normally
                <code>localhost</code>, which is filled in by default.<br/>
                *<strong>User:</strong> The name of the user you created when creating the database.<br/>
                *<strong>Pass:</strong> This is the password you entered when creating the user.<br/>
                *<strong>Database:</strong> And finally, the name of the database itself!
            </p>
            <?php
            break;
        case 3:
            checkInstallation();
            $_POST['host'] = $_POST['host'] ?? null;
            if (empty($_POST['host'])) {
                error('You didn\'t enter a valid hostname');
            }
            if (!in_array($_POST['host'], ['localhost', '127.0.0.1'], true) && !@checkdnsrr($_POST['host'])) {
                warning('I couldn\'t verify that host. I\'ll continue attempting to install this for you anyway');
            }
            $_POST['user'] = array_key_exists('user', $_POST) && !empty($_POST['user']) ? $_POST['user'] : 'root';
            $_POST['timezone'] = array_key_exists('timezone',
                $_POST) && is_string($_POST['timezone']) ? $_POST['timezone'] : null;
            if (empty($_POST['timezone'])) {
                error('You didn\'t select a valid timezone');
            }
            $_POST['gamedir'] = isset($_POST['gamedir']) && is_string($_POST['gamedir']) ? $_POST['gamedir'] : null;
            $path = !empty($_POST['gamedir']) ? $_POST['gamedir'] : '';
            if (!is_dir($path)) {
                error('That\'s not a valid directory path');
            }
            $includeDir = rtrim($path, '/') . '/inc';
            $configFile = $mainPath . DIRECTORY_SEPARATOR . '.env';
            if (!is_dir($path)) {
                error('I couldn\'t find that directory. Are you sure you\'ve entered the correct game path?');
            }
            $siteUrl = 'http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'].'/';
            $configuration = 'MYSQL_HOST="' . $_POST['host'] . '"
MYSQL_USER="' . $_POST['user'] . '"
MYSQL_PASS="' . $_POST['pass'] . '"
MYSQL_BASE="' . $_POST['name'] . '"
DEFAULT_TIMEZONE="' . $_POST['timezone'] . '"
GAME_PATH="' . addslashes($_POST['gamedir']) . '"
SITE_URL="'.$siteUrl.ltrim(str_replace([dirname(__DIR__, 2), DIRECTORY_SEPARATOR], ['', '/'], $_POST['gamedir']), '/').'"
';
            if (!file_exists($configFile)) {
                info('The configuration file (<code>' . $configFile . '</code>) couldn\'t be found. Trying to create it now...');
                $creation = @fopen($configFile, 'wb');
                if (!$creation) {
                    error('I couldn\'t open .env to edit! Please manually create it in the <code>/inc</code> directory');
                }
                fwrite($creation, $configuration);
                fclose($creation);
                if (!$creation || !file_exists($configFile)) {
                    error('The configuration file couldn\'t be created');
                } else {
                    success('The configuration file has been created');
                }
            } elseif (file_exists($configFile) && !is_writable($configFile)) {
                ?>Code required:<br/><textarea class="pure-input-1-2" rows="10"
                                               cols="70"><?php echo $configuration; ?></textarea><br/><?php
                error('Unfortunately, .env exists, but couldn\'t be modified. Please make sure your <code>/.env</code> is writable - or edit the file manually');
            } else {
                $creation = fopen($configFile, 'wb');
                if (!$creation) {
                    error('I couldn\'t edit .env');
                }
                fwrite($creation, $configuration);
                fclose($creation);
                if (!$creation || !file_exists($configFile)) {
                    error('The configuration file couldn\'t be created');
                } else {
                    success('The configuration file has been created');
                }
            }
            info('Attempting connection to the database..');
            require_once $mainPath . '/inc/dbcon.php';
            success('We\'ve connected! Moving on...<meta http-equiv="refresh" content="2; url=install.php?step=4" />');
            break;
        case 4:
            require_once $mainPath . '/inc/dbcon.php';
            ?><h2 class="content-subhead">We're connected! Let's install the database</h2><?php
            $templineMain = '';
            if (isFunctionAvailable('system')) {
                system('mysql --user='.getenv('MYSQL_USER').' --password='.getenv('MYSQL_PASS').' '.getenv('MYSQL_BASE').' < '.__DIR__.DIRECTORY_SEPARATOR.'sqls'.DIRECTORY_SEPARATOR.'grpg-pdo.sql');
        } else {
                $lines = file($sqlPathMain);
                foreach ($lines as $line) {
                    if (strncmp($line, '--', 2) === 0 || !$line) {
                        continue;
                    }
                    $templineMain .= $line;
                    if (substr(trim($line), -1, 1) === ';') {
                        $db->query($templineMain);
                        $db->execute();
                        $templineMain = '';
                    }
                }
            }
            if ($db->tableExists('users')) {
                success('Database installed, let\'s move on.<meta http-equiv="refresh" content="2; url=install.php?step=5" />');
            } else {
                error('The database didn\'t install.. Try importing it manually');
            }
            break;
        case 5:
            ?><h2 class="content-subhead">Database installed, let's configure the game</h2>
            <form action="install.php?step=6" method="post" class="pure-form pure-form-aligned">
                <legend>Your Account</legend>
                <div class="pure-control-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" class="pure-u-1-3" required/>
                </div>
                <div class="pure-control-group">
                    <label for="pass">Password</label>
                    <input type="password" name="pass" class="pure-u-1-3" required/>
                </div>
                <div class="pure-control-group">
                    <label for="cpass">Confirm Password</label>
                    <input type="password" name="cpass" class="pure-u-1-3" required/>
                </div>
                <div class="pure-control-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="pure-u-1-3" required/>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="submit" class="pure-button pure-button-primary">Create Account</button>
                    <button type="reset" class="pure-button pure-button-secondary"><i class="fa fa-recycle"></i> Reset
                    </button>
                </div>
            </form><?php
            break;
        case 6:
            if (!array_key_exists('submit', $_POST)) {
                error('You didn\'t come from step 5..');
            }
            require_once $mainPath . '/inc/dbcon.php';
            if (empty($_POST['username'])) {
                error('You didn\'t enter a valid username');
            }
            if (empty($_POST['pass'])) {
                error('You didn\'t enter a valid password');
            }
            if (empty($_POST['cpass'])) {
                error('You didn\'t enter a valid password confirmation');
            }
            if ($_POST['pass'] != $_POST['cpass']) {
                error('Your passwords didn\'t match');
            }
            $pass = password_hash($_POST['pass'], PASSWORD_BCRYPT);
            $success = $db->exists('users', 'id', 1) ? 'updated' : 'created';
            $db->query(
                'INSERT INTO users (id, ip, username, loginame, password, email, admin, activate) VALUES (1, ?, ?, ?, ?, ?, 1, 1) 
                ON DUPLICATE KEY UPDATE username = ?, loginame = ?, password = ?, email = ?, admin = 1, activate = 1',
                [
                    $_SERVER['REMOTE_ADDR'], $_POST['username'], $_POST['username'], $pass, $_POST['email'],
                    $_POST['username'], $_POST['username'], $pass, $_POST['email'],
                ]
            );
            success('Your account has been '.$success.'!'); ?>
            <a href="install.php?step=7">Run Composer (if available)</a>
            <?php
            break;
        case 7:
            echo 'Checking if we can run <code>shell_exec</code> commands';
            if(isFunctionAvailable('shell_exec')) {
                ?>
                ... <span style="color:green;">we can!</span><br>
                Checking if Composer is installed<?php
                $whichWhere = strncmp(PHP_OS_FAMILY, 'WIN', 3) === 0 ? 'where' : 'command -v';
                if(commandExists($whichWhere.' composer')) {
                    ?>
                    ... <span style="color:green;">it is!</span><br>
                    Running <code>composer update</code>
                    <?php
                    if(shell_exec('composer update') !== false) {
                        success('Composer has been updated');
                    } else {
                        error('Couldn\'t run <code>composer update</code>. Check the log for more information');
                    }
                } else {
                    ?>
                    ... <span style="color:red;">it isn't</span><br>
                    Recommend you <a href="https://getcomposer.org" target="_blank">install Composer</a>
                    <?php
                }
            } else {
                ?>
                ... <span style="color:red;">we can't</span>
                If you have SSH access, you will need to run<br>
                <code>composer update</code><br>
                within the game's installation directory
                <?php
            }?>
            I recommend that you remove this installation directory (keep a local backup, just in case).<br/>
            I can try to delete it for you now if you'd like?<br/>
            <a href="install.php?step=8">Yes, try and remove this directory</a> &middot;
            <a href="<?php echo getenv('SITE_URL'); ?>">No, leave it and head to the game</a>
            <?php
            break;
        case 8:
            delete_files(__DIR__);
            if (is_file(__DIR__ . '/index.php')) {
                $extra = null;
                if (chmod(__DIR__, 0600)) {
                    $extra = '<br />I\'ve managed to set the directory permissions to 0600. That should offer a little protection, but I still highly recommend you delete this folder!';
                }
                warning('I couldn\'t delete this folder. Please manually delete it.' . $extra);
            } else {
                $_SESSION['success'] = 'I\'ve managed to delete this install folder. Have fun!<br><a href="' . getenv('GAME_PATH') . '">To the game!</a>';
                header('Location: /');
                return null;
            }
            break;
    }
    function delete_files($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object !== '.' && $object !== '..') {
                    if (filetype($dir . '/' . $object) === 'dir') {
                        delete_files($dir . '/' . $object);
                    } else {
                        unlink($dir . '/' . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    ?></div>
