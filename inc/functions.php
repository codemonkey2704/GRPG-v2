<?php
declare(strict_types=1);
if (!defined('GRPG_INC')) {
    exit;
}
if ($db->tableExists('users')) {
    $db->query('SELECT id FROM users WHERE admin = 1 ORDER BY id LIMIT 1');
    $db->execute();
    $ownerID = $db->result();
    $owner = $ownerID ? new User($ownerID) : (object)[
        'id' => 0,
        'username' => 'Unknown',
        'formattedname' => '<em>Unknown</em>',
    ];
} else {
    $owner = (object)[
        'id' => 0,
        'username' => 'Unknown',
        'formattedname' => '<em>Unknown</em>',
    ];
    if (basename($_SERVER['PHP_SELF']) !== 'install') {
        header('Location: install/index.php');
        exit;
    }
}
/**
 *    For use with MySQL's TIMESTAMP YYYY-MM-DD HH-MM-SS format.
 *
 * @param int $time [numerical timestamp, or current time if null]
 *
 * @return string
 * @throws Exception
 * @throws Exception
 */
function db_timestamp($time = null)
{
    if ($time === null) {
        $time = time();
    }
    $str = ctype_digit($time) ? $time : '@' . $time;
    $date = new DateTime($str);

    return $date->format('Y-m-d H:i:s');
}

/**
 *    Returns optionally formatted number with suffix.
 *
 * @param int  $num [Numeric input]
 * @param bool $format
 *
 * @return string
 */
function ordinal($num, $format = false)
{
    $ret = $format === true ? format($num) : $num;
    if (!in_array((str_replace(',', '', $num) % 100), [11, 12, 13])) {
        switch (str_replace(',', '', $num) % 10) {
            case 1:
                return $ret . 'st';
                break;
            case 2:
                return $ret . 'nd';
                break;
            case 3:
                return $ret . 'rd';
                break;
        }
    }

    return $ret . 'th';
}

/*
function formatCurrency($amount, $currency = RMSTORE_CURRENCY, $locale = RMSTORE_LOCALE)
{
    $currency = strtoupper($currency);
    $formatter = new NumberFormatter($locale, NumberFormatter::CURRENCY);
    $ret = $formatter->parseCurrency($amount, $currency);

    return $ret;
}
*/
/**
 *    Format a currency (for the RM Store).
 *
 * @param float  $amount [price]
 * @param string $sym    [optional currency symbol]
 *
 * @return string
 */
function formatCurrency($amount, $sym = '$')
{
    return $sym . format($amount, 2);
}

/**
 *    Internal error handler override on call.
 *
 * @param string $message [error message]
 * @param int    $level   [uses PHP error constants - https://php.net/manual/en/errorfunc.constants.php]
 *
 * @return void
 */
function code_error($message, $level = E_USER_WARNING)
{
    $back = debug_backtrace();
    $caller = next($back);
    if ($_SESSION['id'] !== null && $_SESSION['id'] == 1) {
        throw new RuntimeException($message . ' in ' . $caller['function'] . ' called from ' . $caller['file'] . ' on line ' . $caller['line'],
            $level);
    } else {
        exit('An error has been detected in the code' . (generate_ticket('Code error',
                $_SERVER['PHP_SELF']) ? '. A ticket has been generated for you' : ''));
    }
}

/**
 *    Generate a bug ticket.
 *
 * @param string $subject [...]
 * @param string $body    [the body of the ticket ..]
 * @param int    $id      [player ID - defaults to active player]
 *
 * @return bool
 */
function generate_ticket($subject = '', $body = '', $id = 0)
{
    global $db, $user_class;
    if (!$id || !ctype_digit($id)) {
        $id = isset($user_class) ? $user_class->id : 0;
    }
    $db->query('SELECT COUNT(id) FROM tickets WHERE subject = ? AND body = ? AND status IN(\'open\', \'pending\')');
    $db->execute([$subject, $body]);
    $cnt = $db->result();
    if (!$cnt) {
        $db->query('INSERT INTO tickets (subject, body, userid) VALUES (?, ?, ?)');
        $db->execute([$subject, $body, $id]);

        return true;
    }
}

/**
 *    Format a given timestamp (usually unix timestamp).
 *
 * @param int    $seconds [amount of seconds]
 * @param string $mode    [[short|long:default] - shorthand or longhand format]
 * @param int    $display [how many "steps" to display]
 *
 * @return string
 */
function time_format($seconds, $mode = 'long', $display = 3)
{
    if (!$seconds) {
        return 'Never';
    }
    $names = [
        'long' => [
            'millenia',
            'year',
            'month',
            'day',
            'hour',
            'minute',
            'second',
        ],
        'short' => [
            'mil',
            'yr',
            'mnth',
            'day',
            'hr',
            'min',
            'sec',
        ],
    ];
    $seconds = (int)floor($seconds);
    $minutes = (int)($seconds / 60);
    $seconds -= $minutes * 60;
    $hours = (int)($minutes / 60);
    $minutes -= $hours * 60;
    $days = (int)($hours / 24);
    $hours -= $days * 24;
    $months = (int)($days / 31);
    $days -= $months * 31;
    $years = (int)($months / 12);
    $months -= $years * 12;
    $millenia = (int)($years / 1000);
    $years -= $millenia * 1000;
    $disp = 0;
    $result = [];
    if ($millenia && $disp < $display) {
        $result[] = sprintf('%s %s', number_format($millenia), $names[$mode][0]);
        ++$disp;
    }
    if ($years && $disp < $display) {
        $result[] = sprintf('%s %s%s', number_format($years), $names[$mode][1], s($years));
        ++$disp;
    }
    if ($months && $disp < $display) {
        $result[] = sprintf('%s %s%s', number_format($months), $names[$mode][2], s($months));
        ++$disp;
    }
    if ($days && $disp < $display) {
        $result[] = sprintf('%s %s%s', number_format($days), $names[$mode][3], s($days));
        ++$disp;
    }
    if ($hours && $disp < $display) {
        $result[] = sprintf('%s %s%s', number_format($hours), $names[$mode][4], s($hours));
        ++$disp;
    }
    if ($minutes && $disp < $display) {
        $result[] = sprintf('%s %s%s', number_format($minutes), $names[$mode][5], s($minutes));
        ++$disp;
    }
    if (($seconds && $disp < $display) || !count($result)) {
        $result[] = sprintf('%s %s%s', number_format($seconds), $names[$mode][6], s($seconds));
        ++$disp;
    }

    return implode(', ', $result);
}

/**
 *    Check if user exists based on ID or username.
 *
 * @param int $id [the user's ID or username]
 *
 * @return bool
 */
function userExists($id = 0)
{
    global $db;
    if (!$id) {
        return false;
    }
    if (!ctype_digit($id)) {
        $id = Get_ID($id);
        if (!$id) {
            return false;
        }
    }
    $db->query('SELECT COUNT(id) FROM users WHERE id = ?');
    $db->execute([$id]);

    return $db->result() ? true : false;
}

/**
 *    Check if item exists based on ID or name.
 *
 * @param int $id [the item's ID or name]
 *
 * @return bool
 */
function itemExists($id = 0)
{
    global $db;
    if (!$id) {
        return false;
    }
    if (!ctype_digit($id)) {
        $db->query('SELECT id FROM items WHERE name = ?', [$id]);
        $tmp = $db->result();
        if (!$tmp) {
            return false;
        }
        $id = $tmp;
    }
    $db->query('SELECT COUNT(id) FROM items WHERE id = ?', [$id]);

    return $db->result() > 0;
}

/**
 *    Basic formatted image display.
 *
 * @param string $url    [URL - validity check given]
 * @param int    $width  [do the dimensions really need explaining?]
 * @param int    $height [seriously though, do they?]
 * @param string $style  [extra styling, inline-CSS]
 *
 * @return string
 */
function formatImage($url = null, $width = 100, $height = 100, $style = 'border: 1px solid #333333;')
{
    if (!$url) {
        $url = 'images/noimage.png';
    }
    if (!isImage($url)) {
        return '[Invalid image: ' . $url . ']';
    }
    $image = '<img src="' . $url . '" width="' . $width . '" height="' . $height . '"';
    if ($style) {
        $image .= ' style="' . $style . '"';
    }
    $image .= ' />';

    return $image;
}

/**
 *    Validate URL as image.
 *
 * @param string $url   [full valid url to image]
 * @param bool   $local [defines whether $url is local or not]
 *
 * @return bool
 */
function isImage($url, $local = false)
{
    if (!urlExists($url)) {
        return false;
    }
    if ($local === true && function_exists('exif_imagetype')) { // Preferred method
        if (!exif_imagetype($url)) {
            return false;
        }
    } else { // Eww.. Legacy
        try {
            $dims = (array)getimagesize($url);
        } catch (Exception $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e->getMessage());
            return false;
        }
        if (!is_array($dims) || !isset($dims[0], $dims[1]) || !$dims[0] || !$dims[1]) {
            return false;
        }
    }

    return true;
}

/**
 *    Validate and verify existence of URL.
 *    Thank you to MoonLite over at StackOverflow for this answer - https://stackoverflow.com/questions/2280394/how-can-i-check-if-a-url-exists-via-php.
 *    Minor edits.
 *
 * @param string $url [the URL]
 *
 * @param bool   $applyGameURL
 *
 * @return bool
 */
function urlExists($url, $applyGameURL = true)
{
    $url = str_replace(' ', '%20', $url);
    if ($applyGameURL == true) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            $url = BASE_URL . ltrim($url, '/');
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                return false;
            }
        }
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    // the next bit could be slow:
    return !(getHttpResponseCode_using_curl($url, true, true) !== true);
}

function getHttpResponseCode_using_curl($url, $followredirects = true, $bool = false)
{
    global $user_class, $owner;
    // returns int responsecode, or false (if url does not exist or connection timeout occurs)
    // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
    // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
    // if $followredirects == true : return the LAST  known httpcode (when redirected)
    if (!$url || !is_string($url)) {
        return false;
    }
    $curlOpts = [
        CURLOPT_HEADER => true,
        CURLOPT_NOBODY => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_TIMEOUT => 6,
    ];
    if ($followredirects === true) {
        $curlOpts[CURLOPT_FOLLOWLOCATION] = true;
        $curlOpts[CURLOPT_MAXREDIRS] = 5;
    } else {
        $curlOpts[CURLOPT_FOLLOWLOCATION] = false;
    }
    $handle = curl_init($url);
    if ($handle === false) {
        return false;
    }
    curl_setopt_array($handle, $curlOpts);
    try {
        curl_exec($handle);
        if (curl_errno($handle)) { // should be 0
            curl_close($handle);

            return false;
        }
    } catch (Exception $e) {
        echo Message('cURL failure' . (isset($user_class->id, $owner) && $user_class->id == $owner ? '<br /><br />Error:<br /><br />' . $e->getMessage() : ''),
            'Critical Error', true);
    }
    $code = (int)curl_getinfo($handle,
        CURLINFO_HTTP_CODE); // note: php.net documentation shows this returns a string, but really it returns an int
    curl_close($handle);
    if ($bool) {
        return $code === 200;
    }

    return $code;
}

function getHttpResponseCode_using_getheaders($url, $followredirects = true, $bool = false)
{
    // returns string responsecode, or false if no responsecode found in headers (or url does not exist)
    // NOTE: could potentially take up to 0-30 seconds , blocking further code execution (more or less depending on connection, target site, and local timeout settings))
    // if $followredirects == false: return the FIRST known httpcode (ignore redirects)
    // if $followredirects == true : return the LAST  known httpcode (when redirected)
    if (!$url || !is_string($url)) {
        return false;
    }
    $headers = @get_headers($url);
    if ($headers && is_array($headers)) {
        if ($followredirects) {
            // we want the the last errorcode, reverse array so we start at the end:
            $headers = array_reverse($headers);
        }
        foreach ($headers as $hline) {
            // search for things like "HTTP/1.1 200 OK" , "HTTP/1.0 200 OK" , "HTTP/1.1 301 PERMANENTLY MOVED" , "HTTP/1.1 400 Not Found" , etc.
            // note that the exact syntax/version/output differs, so there is some string magic involved here
            if (preg_match('/^HTTP\/\S+\s+([1-9]\d{2})\s+.*/', $hline, $matches)) {
                // "HTTP/*** ### ***"
                $code = $matches[0];
                if ($bool) {
                    return $code == 200;
                }

                return $code;
            }
        }
        // no HTTP/xxx found in headers:
        return false;
    }
    // no headers :
    return false;
}

/**
 *    Cleanly kills page if user is in hospital and/or jail.
 *
 * @param string $which [options: all, jail, hospital, hosp]
 * @param int    $user  [defaults to active user]
 *
 * @return string
 */
function checkUserStatus($which = 'all', $user = 0)
{
    global $db, $user_class;
    if (!$user || !ctype_digit($user)) {
        $user = $user_class->id;
    }
    $status_class = $user != $user_class->id ? new User($user) : $user_class;
    switch ($which) {
        case 'all':
            if ($status_class->jail || $status_class->hospital) {
                echo Message('You can\'t do that whilst in ' . ($status_class->jail ? 'jail' : 'hospital'), 'Error',
                    true);
            }
            break;
        case 'jail':
            if ($status_class->jail) {
                echo Message('You can\'t do that whilst in jail', 'Error', true);
            }
            break;
        case 'hospital':
        case 'hosp':
            if ($status_class->hospital) {
                echo Message('You can\'t do that whilst in hospital', 'Error', true);
            }
            break;
        default:
            break;
    }
}

/**
 *    Displays a formatted list of error messages, or null if empty.
 *
 * @param array $errors [list of error messages]
 * @param bool  $kill   [optional kill page from function]
 *
 * @return string/null
 */
function display_errors($errors = [], $kill = false)
{
    $cnt = count($errors);
    if (!$cnt) {
        return null;
    }
    echo Message('<ul><li>' . implode('</li><li>', $errors) . '</li></ul>', 'Error' . s($cnt));
    if ($kill == true) {
        exit;
    }
}

/**
 *    Sentence formatting a or an from first letter of $word.
 *
 * @param string $word   [a word]
 * @param bool   $disp   [if true, then $word will be returned too]
 * @param bool   $format [if true, wraps $ret in format()]
 *
 * @return string
 */
function aAn($word, $disp = true, $format = true)
{
    $first = $word[0];
    $ret = in_array($first, ['a', 'e', 'i', 'o', 'u']) ? 'an' : 'a';
    if ($disp == true) {
        $ret .= ' ' . $word;
    }
    if ($format == true) {
        $ret = format($ret);
    }

    return $ret;
}

/**
 *    Returns formatted string, wrapping number_format() if numeric, stripslashes() and htmlspecialchars().
 *
 * @param string $str [the input]
 * @param int    $dec [optional decimal places if numeric]
 *
 * @return string
 */
function format($str, $dec = 0)
{
    if(is_numeric($str)) {
        $num = (float) $str;
        return number_format($num, $dec);
    }
    if(is_string($str)) {
        $str = stripslashes(htmlspecialchars($str));
        if($dec === true) {
            $str = nl2br($str);
        }
        return $str;
    }
    return null;
}

/**
 *    Basic pluralization (not perfect).
 *
 * @param int $num [input number]
 *
 * @return string
 */
function s($num = 1)
{
    return $num == 1 ? '' : 's';
}

/**
 *    Return user ID from username if exists, 0 if not.
 *
 * @param string $username [user's name]
 *
 * @return int
 */
function Get_ID($username = '')
{
    global $db;
    $username = strip_tags(trim($username));
    if (!$username) {
        return 0;
    }
    $db->query('SELECT id FROM users WHERE username = ?');
    $db->execute([$username]);
    $id = $db->result();
    if (!$id) {
        return 0;
    }

    return $id;
}

/**
 *    Lazy meta-refresh.
 *
 * @param string $url  [in-game page url]
 * @param int    $time [refresh wait time]
 *
 * @return string
 */
function mrefresh($url = '', $time = 1)
{
    echo '<meta http-equiv="refresh" content="' . $time . ';url=' . $url . '" />';
}

/**
 *    Lazy javascript info popup for cars.
 *
 * @param string $text [car name]
 * @param int    $id   [car ID]
 *
 * @return string
 */
function car_popup($text = '', $id = 0)
{
    return '<span class="underline clicky" onclick="javascript:window.open(\'cardesc.php?id=' . $id . '\', \'60\', \'left=20,top=20,width=400,height=400,toolbar=0,resizable=0,scrollbars=1\');">' . $text . '</span>';
}

/**
 *    Lazy javascript info popup for items.
 *
 * @param int    $id [item ID]
 *
 * @param string $name
 *
 * @return string
 */
function item_popup($id = 0, $name = '')
{
    global $db;
    $db->query('SELECT name FROM items WHERE id = ?');
    $db->execute([$id]);
    $tmp = $db->result();
    if (!$tmp) {
        return '<em>Non-existent item</em>';
    }
    if (!$name) {
        $name = $tmp;
    }

    return '<span class="underline clicky" onclick="javascript:window.open(\'description.php?id=' . $id . '\', \'60\', \'left=20,top=20,width=400,height=400,toolbar=0,resizable=0,scrollbars=1\');">' . format($name) . '</span>';
}

/**
 *    Basic number formatter with optional currency symbol display and selector.
 *
 * @param int    $num    [numeric input]
 * @param bool   $dollar [if true, show currency symbol]
 * @param string $sym    [currency symbol]
 *
 * @return string
 */
function prettynum($num = 0, $dollar = false, $sym = '$')
{
    if (!is_numeric($num)) {
        return ($dollar ? $sym : '') . '0';
    }
    $out = number_format((float)$num);
    if ($dollar && is_numeric($num)) {
        $out = $sym . $out;
    }

    return $out;
}

/**
 *    Verify user's quantity of selected item.
 *
 * @param int $itemid [item ID]
 * @param int $userid [user ID]
 *
 * @return int
 */
function Check_Item(int $itemid = 0, int $userid = 0)
{
    global $db, $user_class;
    if(!$userid) {
        $userid = $user_class->id;
    }
    if (!$itemid) {
        return 0;
    }
    $db->query('SELECT quantity FROM inventory WHERE userid = ? AND itemid = ?');
    $db->execute([$userid, $itemid]);
    $qty = $db->result();

    return (int)$qty;
}

/**
 *    Verify user's quantity of selected land.
 *
 * @param int $city   [location ID]
 * @param int $userid [user ID]
 *
 * @return int
 */
function Check_Land($city = 0, $userid = 0)
{
    global $db;
    if (!ctype_digit($city) || !ctype_digit($userid)) {
        return 0;
    }
    $db->query('SELECT amount FROM land WHERE userid = ? AND city = ?', [$userid, $city]);
    $qty = $db->result();

    return $qty > 0 ? $qty : 0;
}

/**
 *    Credit stock share to user.
 *
 * @param int $stock    [stock ID]
 * @param int $userid   [user ID]
 * @param int $quantity [amount to credit]
 *
 * @return bool
 * @return bool
 */
function Give_Share($stock = 0, $userid = 0, $quantity = 1)
{
    global $db;
    if (!ctype_digit($userid) || !ctype_digit($stock)) {
        code_error('Invalid arguments passed to give_share');

        return false;
    }
    $db->query('SELECT amount FROM shares WHERE userid = ? AND companyid = ?');
    $db->execute([$userid, $stock]);
    if ($db->count()) {
        $db->query('UPDATE shares SET amount = amount + ? WHERE userid = ? AND companyid = ?');
    } else {
        $db->query('INSERT INTO shares (amount, userid, companyid) VALUES (?, ?, ?)');
    }
    $db->execute([$quantity, $userid, $stock]);

    return true;
}

/**
 *    Decrement stock share from user.
 *
 * @param int $stock    [stock ID]
 * @param int $userid   [user ID]
 * @param int $quantity [amount to decrement]
 *
 * @return bool
 * @return bool
 */
function Take_Share($stock = 0, $userid = 0, $quantity = 1)
{
    global $db;
    if (!ctype_digit($userid) || !ctype_digit($stock)) {
        code_error('Invalid arguments passed to take_share');

        return false;
    }
    $db->query('SELECT amount FROM shares WHERE userid = ? AND companyid = ?');
    $db->execute([$userid, $stock]);
    if ($db->count()) {
        if ($db->result() - $quantity <= 0) {
            $db->query('DELETE FROM shares WHERE userid = ? AND companyid = ?');
            $db->execute([$userid, $stock]);
        } else {
            $db->query('UPDATE shares SET amount = GREATEST(amount - ?, 0) WHERE userid = ? AND companyid = ?');
            $db->execute([$quantity, $userid, $stock]);
        }
    }

    return true;
}

/**
 *    Verify user's quantity of selected stock shares.
 *
 * @param int $stock  [stock ID]
 * @param int $userid [user ID]
 *
 * @return bool|int|mixed|null
 * @return bool|int|mixed|null
 */
function Check_Share($stock = 0, $userid = 0)
{
    global $db;
    if (!ctype_digit($userid) || !ctype_digit($stock)) {
        code_error('Invalid arguments passed to check_share');

        return false;
    }
    $db->query('SELECT amount FROM shares WHERE userid = ? AND companyid = ?');
    $db->execute([$userid, $stock]);
    $qty = $db->result();

    return $qty > 0 ? $qty : 0;
}

/**
 *    Credit land to user.
 *
 * @param int $city     [location ID]
 * @param int $userid   [user ID]
 * @param int $quantity [amount to credit]
 *
 * @return bool
 * @return bool
 */
function Give_Land($city = 0, $userid = 0, $quantity = 1)
{
    global $db;
    if (!ctype_digit($userid) || !ctype_digit($city)) {
        code_error('Invalid arguments passed to give_land');

        return false;
    }
    $db->query('SELECT COUNT(id) FROM land WHERE userid = ? AND city = ?');
    $db->execute([$userid, $city]);
    if ($db->result()) {
        $db->query('UPDATE land SET amount = amount + ? WHERE userid = ? AND city = ?');
    } else {
        $db->query('INSERT INTO land (amount, userid, city) VALUES (?, ?, ?)');
    }
    $db->execute([$city, $userid, $quantity]);

    return true;
}

/**
 *    Decrement land from user.
 *
 * @param int $city     [location ID]
 * @param int $userid   [user ID]
 * @param int $quantity [amount to decrement]
 *
 * @return bool
 * @return bool
 */
function Take_Land($city = 0, $userid = 0, $quantity = 1)
{
    global $db;
    if (!ctype_digit($userid) || !ctype_digit($city)) {
        code_error('Invalid arguments passed to take_land');

        return false;
    }
    $db->query('SELECT amount FROM land WHERE userid = ? AND city = ?');
    $db->execute([$userid, $city]);
    if ($db->result() - $quantity > 0) {
        $db->query('UPDATE land SET amount = GREATEST(amount - ?, 0) WHERE userid = ? AND city = ?');
        $db->execute([$quantity, $userid, $city]);
    } else {
        $db->query('DELETE FROM land WHERE city = ? AND userid = ?');
        $db->execute([$city, $userid]);
    }

    return true;
}

/**
 *    Credit item to user.
 *
 * @param int $itemid   [item ID]
 * @param int $userid   [user ID]
 * @param int $quantity [amount to credit]
 *
 * @return bool
 * @return bool
 * @return bool
 */
function Give_Item(int $itemid = 0, int $userid = 0, int $quantity = 1)
{
    global $db;
    if (!$itemid) {
        return false;
    }
    if (!$userid && $_SESSION['id'] !== null) {
        $userid = (int)$_SESSION['id'];
    }
    $db->query('SELECT quantity FROM inventory WHERE userid = ? AND itemid = ?', [$userid, $itemid]);
    if ($db->result()) {
        $db->query('UPDATE inventory SET quantity = quantity + ? WHERE userid = ? AND itemid = ?',
            [$quantity, $userid, $itemid]);
    } else {
        $db->query('INSERT INTO inventory (quantity, userid, itemid) VALUES (?, ?, ?)', [$quantity, $userid, $itemid]);
    }

    return true;
}

/**
 *    Decrement item from user.
 *
 * @param int $itemid   [item ID]
 * @param int $userid   [user ID]
 * @param int $quantity [amount to decrement]
 *
 * @return bool
 * @return bool
 * @return bool
 */
function Take_Item(int $itemid = 0, int $userid = 0, int $quantity = 1)
{
    global $db;
    if (!$itemid) {
        return false;
    }
    if (!$userid && $_SESSION['id'] !== null) {
        $userid = $_SESSION['id'];
    }
    $db->query('SELECT quantity FROM inventory WHERE userid = ? AND itemid = ?');
    $db->execute([$userid, $itemid]);
    $owned = $db->result();
    if (($owned - $quantity) > 0) {
        $db->query('UPDATE inventory SET quantity = GREATEST(quantity - ?, 0) WHERE userid = ? AND itemid = ?');
        $db->execute([$quantity, $userid, $itemid]);
    } else {
        $db->query('DELETE FROM inventory WHERE userid = ? AND itemid = ?');
        $db->execute([$userid, $itemid]);
    }

    return true;
}

/**
 *    Returns formatted message.
 *
 * @param string $text [the content to be formatted]
 * @param null   $head
 * @param bool   $kill
 *
 * @return string|null
 */
function Message($text, $head = null, $kill = false)
{
    if ($text === SECURITY_TIMEOUT_MESSAGE) {
        $head = 'Security Error';
        $kill = $kill !== null;
    } ?>
    <tr>
    <th class="content-head"><?php echo $head ?? '.: Important Message :.'; ?></th>
    </tr>
    <tr>
    <td class="content"><?php echo $text; ?></td>
    </tr><?php
    if ($kill === true) {
        exit;
    }
    return null;
}

/**
 *    Notify user of event.
 *
 * @param int    $id    [user ID]
 * @param string $text  [the event's content]
 * @param int    $extra [optional usage for {extra} modifier in events - to be used as secondary user ID]
 *
 * @return bool
 * @return bool
 */
function Send_Event($id = 0, $text = '', $extra = 0)
{
    global $db;
    if (!ctype_digit($id) || !is_string($text)) {
        code_error('Invalid arguments passed to send_event');

        return false;
    }
    $text = trim($text);
    $db->query('INSERT INTO events (recipient, content, extra) VALUES (?, ?, ?)');
    $db->execute([$id, $text, $extra]);

    return true;
}

/**
 *    Do I really need to explain this one?
 *
 * @param int $id [user ID]
 *
 * @return integer/bool
 */
function Is_User_Banned($id = 0)
{
    global $db;
    if (!$id) {
        if($_SESSION['id'] === null) {
            return false;
        }
        $id = $_SESSION['id'];
    }
    $db->query('SELECT COUNT(uni_id) FROM bans WHERE id = ?');
    $db->execute([$id]);

    return $db->result();
}

/**
 *    Or this one?
 *
 * @param int $id [user ID]
 *
 * @return string
 */
function Why_Is_User_Banned($id = 0)
{
    global $db;
    if (!ctype_digit($id)) {
        code_error('Invalid argument passed to why_is_user_banned');

        return false;
    }
    $db->query('SELECT reason FROM bans WHERE id = ?');
    $db->execute([$id]);

    return $db->result();
}

/**
 *    Lazy radio status.
 *
 * @return integer/bool
 */
function Radio_Status()
{
    global $db;
    $db->query('SELECT radio FROM serverconfig');
    $db->execute();

    return $db->result();
}

/**
 *    Format time output.
 *
 * @param int  $ts [amount of time in seconds]
 *
 * @param bool $ago
 *
 * @return string
 */
function howlongago($ts, $ago = false)
{
    if (!is_numeric($ts)) {
        $ts = (int)strtotime($ts);
    }
    $ts = time() - $ts;
    if ($ts < 1) { // <1 second
        return ' NOW';
    }

    if ($ts == 1) { // <1 second
        return $ts . ' second' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 60) { // <1 minute
        return $ts . ' seconds' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 120) { // 1 minute
        return '1 minute' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 60 * 60) { // <1 hour
        return floor($ts / 60) . ' minutes' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 60 * 60 * 2) { // <2 hour
        return '1 hour' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 60 * 60 * 24) { // <24 hours = 1 day
        return floor($ts / (60 * 60)) . ' hours' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 60 * 60 * 24 * 2) { // <2 days
        return '1 day' . ($ago === true ? ' ago' : '');
    }

    if ($ts < (60 * 60 * 24 * 7)) { // <7 days = 1 week
        return floor($ts / (60 * 60 * 24)) . ' days' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 60 * 60 * 24 * 30.5) { // <30.5 days ~  1 month
        return floor($ts / (60 * 60 * 24 * 7)) . ' weeks' . ($ago === true ? ' ago' : '');
    }

    if ($ts < 60 * 60 * 24 * 365) { // <365 days = 1 year
        return floor($ts / (60 * 60 * 24 * 30.5)) . ' months' . ($ago === true ? ' ago' : '');
    }

// more than 1 year
    return floor($ts / (60 * 60 * 24 * 365)) . ' years' . ($ago === true ? ' ago' : '');
}

/**
 *    Identical to howlongago(), just logic reversed.
 *
 * @param int $ts [amount of time in seconds]
 *
 * @return string
 */
function howlongtil($ts)
{
    if (!is_numeric($ts)) {
        $ts = (int)strtotime($ts);
    }
    $ts -= time();
    switch (true) {
        case $ts < 1:
            return 'NOW';
            break;
        case $ts === 1: // 1 second
            return $ts . ' second';
            break;
        case $ts < 60: // <1 minute
            return $ts . ' seconds';
            break;
        case $ts < 120: // 1 minute
            return '1 minute';
            break;
        case $ts < 60 * 60: // <1 hour
            return floor($ts / 60) . ' minutes';
            break;
        case $ts < 60 * 60 * 2: // <2 hour
            return '1 hour';
            break;
        case $ts < 86400: // <24 hours = 1 day
            return floor($ts / 3600) . ' hours';
            break;
        case $ts < 172800: // <2 days
            return '1 day';
            break;
        case $ts < 604800: // <7 days = 1 week
            return floor($ts / 86400) . ' days';
            break;
        case $ts < 2635200: // <30.5 days ~  1 month
            return floor($ts / 604800) . ' weeks';
            break;
        case $ts < 31536000: // <365 days = 1 year
            return floor($ts / 2635200) . ' months';
            break;
        default:
            return floor($ts / 31536000) . ' years';
            break;
    }
}

/**
 *    Returns experience.
 *
 * @param int $L [user's level]
 *
 * @return int
 */
function experience($L)
{
    $a = 0;
    for ($x = 1; $x < $L; ++$x) {
        $a += floor($x + 1500 * (4 * ($x / 7)));
        if ($L === $x) {
            return $a;
        }
    }

    return floor($a / 4);
}

/**
 *    Return a user's level based on experience.
 *
 * @param int $exp [user's experience]
 *
 * @return int
 * @return int
 */
function Get_The_Level($exp)
{
    $a = 0;
    for ($x = 1; $x < 100; ++$x) {
        $a += floor($x + 1500 * (4 ** ($x / 7)));
        if ($exp < floor($a / 4)) {
            return $x;
            break;
        }
    }
}

/**
 *    Returns a user's "exp needed" to level.
 *
 * @param [type] $exp [description]
 *
 * @return int
 * @return int
 */
function Get_Max_Exp($exp)
{
    global $user_class;
    if ($exp == 0) {
        return 457;
    }
    for ($L = 1; $L < 100; ++$L) {
        $exp = experience($L);
        if ($exp >= $user_class->exp) {
            return $exp;
            break;
        }
    }
}

/**
 *    Show an HTML select dropdown menu populated with forum boards (or message if none).
 *
 * @param string $ddname   [HTML select name]
 * @param int    $selected [optional default selected value]
 *
 * @return string
 */
function forums_boards($ddname = 'forum', $selected = -1)
{
    global $db;
    $db->query('SELECT fb_id, fb_name, fb_auth FROM forum_boards ORDER BY fb_name ');
    $db->execute();
    $rows = $db->fetch();
    if ($rows === null) {
        return 'No forum boards available';
    }
    $ret = '<select name="' . $ddname . '" id="' . $ddname . '"><option value="0" class="centre"' . (in_array($selected, [-1, null], true) ? ' selected' : '') . '>--- NONE ---</option>';
    $ret .= '<option value="0" class="centre blue">&rarr; PUBLIC &larr;</option>';
    foreach ($rows as $row) {
        if ($row['fb_auth'] === 'public') {
            $ret .= sprintf('<option value="%u"%s>%s</option>', $row['fb_id'],
                $row['fb_id'] == $selected ? ' selected' : '', format($row['fb_name']));
        }
    }
    $ret .= '<option value="0" class="centre green">&rarr; STAFF &larr;</option>';
    foreach ($rows as $row) {
        if ($row['fb_auth'] === 'staff') {
            $ret .= sprintf('<option value="%u"%s>%s</option>', $row['fb_id'],
                $row['fb_id'] == $selected ? ' selected' : '', format($row['fb_name']));
        }
    }
    $ret .= '</select>';

    return $ret;
}

/**
 *    Show an HTML select dropdown menu populated with forum boards (or message if none).
 *
 * @param string $ddname   [HTML select name]
 * @param int    $selected [optional default selected value]
 *
 * @return string
 */
function forums_topics($ddname = 'forum', $selected = -1)
{
    global $db;
    $db->query('SELECT ft_id, ft_name FROM forum_topics ORDER BY ft_name ');
    $db->execute();
    $rows = $db->fetch();
    if ($rows === null) {
        return 'No forum boards available';
    }
    $ret = '<select name="' . $ddname . '" id="' . $ddname . '"><option value="0" class="centre"' . (in_array($selected,
            [-1, null]) ? ' selected' : '') . '>--- NONE ---</option>';
    foreach ($rows as $row) {
        $ret .= sprintf('<option value="%u"%s>%s</option>', $row['ft_id'],
            $row['ft_id'] == $selected ? ' selected' : '', format($row['ft_name']));
    }
    $ret .= '</select>';

    return $ret;
}

/**
 *    Grab a setting defined within the staff panel.
 *
 * @param string $setting [the setting name - match database table entry conf_name]
 *
 * @return string
 */
function settings($setting)
{
    global $db;
    $db->query('SELECT conf_value FROM settings WHERE conf_name = ?');
    $db->execute([$setting]);

    return $db->result();
}

function listItems($ddname = 'item', $selected = -1, array $notIDs = [])
{
    global $db;
    $where = '';
    if (count($notIDs)) {
        $where = 'WHERE id NOT IN(' . implode(',', $notIDs) . ')';
    }
    $db->query('SELECT id, name FROM items ' . $where . ' ORDER BY name ');
    $db->execute();
    if (!$db->count()) {
        return 'No items found';
    }
    $rows = $db->fetch();
    $ret = '<select name="' . $ddname . '" id="' . $ddname . '"><option value="0" class="centre"' . (in_array($selected,
            [-1, null]) ? ' selected' : '') . '>--- NONE ---</option>';
    foreach ($rows as $row) {
        $ret .= sprintf('<option value="%u"%s>%s</option>', $row['id'], $row['id'] == $selected ? ' selected' : '',
            format($row['name']));
    }
    $ret .= '</select>';

    return $ret;
}

function listMobsters($ddname = 'user', $selected = -1, $notIDs = [])
{
    global $db;
    $where = '';
    if (count($notIDs)) {
        $where = ' WHERE id NOT IN (' . implode(',', $notIDs) . ')';
    }
    $db->query('SELECT id, username FROM users ' . $where . ' ORDER BY username ');
    $db->execute();
    if (!$db->count()) {
        return 'No mobsters found';
    }
    $rows = $db->fetch();
    $ret = '<select name="' . $ddname . '" id="' . $ddname . '"><option value="0" class="centre"' . (in_array($selected,
            [-1, null]) ? ' selected' : '') . '>--- NONE ---</option>';
    foreach ($rows as $row) {
        $ret .= sprintf('<option value="%u"%s>%s</option>', $row['id'], $row['id'] == $selected ? ' selected' : '',
            format($row['username']));
    }
    $ret .= '</select>';

    return $ret;
}

function points($amnt)
{
    return format($amnt) . ' point' . s($amnt);
}

function forums_rank($tp = 0)
{
    $rank = '#0 Inactive';
    if (!$tp || !ctype_digit($tp)) {
        return $rank;
    }
    $ranks = [
        3 => '#1 Absolute Newbie',
        7 => '#2 Newbie',
        12 => '#3 Beginner',
        18 => '#4 Not Experienced',
        25 => '#5 Rookie',
        50 => '#6 Average',
        100 => '#7 Good',
        200 => '#8 Very Good',
        350 => '#9 Greater Than Average',
        500 => '#10 Experienced',
        750 => '#11 Highly Experienced',
        1200 => '#12 Honoured',
        1800 => '#13 Highly Honoured',
        2500 => '#14 Respect King',
        5000 => '#15 True Champion',
    ];
    foreach ($ranks as $key => $value) {
        if ($tp >= $key) {
            $rank = $value;
            break;
        }
    }

    return $rank;
}

function csrf_create($name = 'csrf', $html = true)
{
    $token = nocsrf::generate($name);

    return $html ? '<input type="hidden" name="' . $name . '" value="' . $token . '" />' : $token;
}

function csrf_check($name, $which, $exception = false, $time = 600, $multiple = false)
{
    if (!in_array($which, [$_POST, $_GET, $_REQUEST, $_SESSION, $_COOKIE], true)) {
        return false;
    }

    try {
        return nocsrf::check($name, $which, $exception, $time, $multiple);
    } catch (Exception $e) {
        /** @noinspection ForgottenDebugOutputInspection */
        error_log($e->getMessage());
        return false;
    }
}
