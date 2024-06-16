<?php
declare(strict_types=1);
define('STAFF_FILE', true);
require_once __DIR__.'/inc/header.php';
$auths = ['public', 'staff'];
if ($user_class->admin != 1) {
    echo Message('You don\'t have access', 'Access Denied', true);
}
if (defined('PRUNE_INACTIVE_ACCOUNTS') && PRUNE_INACTIVE_ACCOUNTS == true) {
    $ids = [];
    $db->query('SELECT id FROM users WHERE DATE_SUB(NOW(), INTERVAL 1 MONTH) > lastactive ORDER BY id ');
    $db->execute();
    $rows = $db->fetch();
    foreach ($rows as $row) {
        $ids[] = $row['id'];
    }
    if (count($ids)) {
        $db->query('DELETE FROM users WHERE id IN('.implode(',', $ids).')');
        $db->execute();
    }
}
//referrals section
$nums = array_unique(['givecredit', 'denycredit', 'deletejob', 'deletecrime', 'deletecity', 'takealluser', 'takeallitem']);
foreach ($nums as $what) {
    $_GET[$what] = array_key_exists($what, $_GET) && ctype_digit($_GET[$what]) ? $_GET[$what] : null;
}
$nums2 = array_unique(['money', 'strength', 'defense', 'speed', 'level', 'landleft', 'landprice', 'levelreq', 'nerve', 'cost', 'offense', 'heal', 'reduce', 'itemnumber', 'itemquantity', 'rmdays', 'points', 'hookers', 'crimeid', 'cityid', 'jobid', 'id', 'board', 'forum', 'forum2', 'recycle', 'delete', 'awake', 'buyable']);
foreach ($nums2 as $what) {
    $_POST[$what] = array_key_exists($what, $_POST) && ctype_digit(str_replace(',', '', $_POST[$what])) ? str_replace(',', '', $_POST[$what]) : 0;
}
$strs = array_unique(['name', 'description', 'image', 'username', 'message', 'items']);
foreach ($strs as $what) {
    $_POST[$what] = array_key_exists($what, $_POST) && is_string($_POST[$what]) ? strip_tags(trim($_POST[$what])) : '';
    if ($what === 'image' && !isImage($_POST[$what])) {
        $_POST[$what] = '';
    }
}
$errors = [];
if (array_key_exists('addrmpack', $_POST)) {
    if (!csrf_check('rmstore_add', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $_POST['pack_cost'] = array_key_exists('pack_cost', $_POST) && is_numeric(str_replace(',', '', $_POST['pack_cost'])) ? str_replace(',', '', $_POST['pack_cost']) : null;
    if (empty($_POST['pack_cost'])) {
        $errors[] = 'As nice as that is, you can\'t have free packs';
    }
    $db->query('SELECT COUNT(id) FROM rmstore_packs WHERE name = ?');
    $db->execute([$_POST['name']]);
    if ($db->result()) {
        $errors[] = 'Another pack with that name already exists';
    }
    if (!empty($_POST['items'])) {
        $cnt = 0;
        $_POST['items'] = str_replace(["\r\n","\r","\n",',,'], ',', $_POST['items']);
        $items = explode(',', $_POST['items']);
        foreach ($items as $what) {
            ++$cnt;
            [$item, $qty] = explode(':', $what);
            if (!itemExists($item)) {
                $errors[] = 'The '.ordinal($cnt).' item you entered doesn\'t exist';
            }
        }
        if ($cnt > 10) {
            $errors[] = 'For processing reasons, you can\'t add more than 10 items per pack';
        }
    }
    $_POST['enabled'] = $_POST['enabled'] ?? false;
    if (!count($errors)) {
        $db->query('INSERT INTO rmstore_packs (name, cost, money, points, days, items, enabled) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['pack_cost'], $_POST['money'], $_POST['points'], $_POST['rmdays'], $_POST['items'], $_POST['enabled']]);
        echo Message('RMStore Pack '.format($_POST['name']).' has been added');
    }
} elseif (array_key_exists('editrmpack', $_POST)) {
    if (!csrf_check('rmstore_edit', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'You didn\'t select a valid pack';
    }
    $db->query('SELECT COUNT(id) FROM rmstore_packs WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->result()) {
        $errors[] = 'The pack you selected doesn\'t exist';
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $_POST['pack_cost'] = array_key_exists('pack_cost', $_POST) && is_numeric(str_replace(',', '', $_POST['pack_cost'])) ? str_replace(',', '', $_POST['pack_cost']) : null;
    if (empty($_POST['pack_cost'])) {
        $errors[] = 'As nice as that is, you can\'t have free packs';
    }
    $db->query('SELECT COUNT(id) FROM rmstore_packs WHERE name = ? AND id <> ?');
    $db->execute([$_POST['name'], $_POST['id']]);
    if ($db->result()) {
        $errors[] = 'Another pack with that name already exists';
    }
    if (!empty($_POST['items'])) {
        $cnt = 0;
        $_POST['items'] = str_replace(["\r\n","\r","\n",',,'], ',', $_POST['items']);
        $items = explode(',', $_POST['items']);
        foreach ($items as $what) {
            ++$cnt;
            [$item, $qty] = explode(':', $what);
            if (!itemExists($item)) {
                $errors[] = 'The '.ordinal($cnt).' item you entered doesn\'t exist';
            }
        }
        if ($cnt > 10) {
            $errors[] = 'For processing reasons, you can\'t add more than 10 items per pack';
        }
    }
    $_POST['enabled'] = $_POST['enabled'] ?? false;
    if (!count($errors)) {
        $db->query('UPDATE rmstore_packs SET name = ?, cost = ?, money = ?, points = ?, days = ?, items = ?, enabled = ? WHERE id = ?');
        $db->execute([$_POST['name'], $_POST['pack_cost'], $_POST['money'], $_POST['points'], $_POST['rmdays'], $_POST['items'], $_POST['enabled'], $_POST['id']]);
        echo Message('RMStore Pack '.format($_POST['name']).' has been edited');
    }
} elseif (array_key_exists('disenablermpack', $_POST)) {
    if (!csrf_check('rmstore_disenable', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'You didn\'t select a valid pack';
    }
    $db->query('SELECT id, name, enabled FROM rmstore_packs WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->count()) {
        $errors[] = 'The pack you selected doesn\'t exist';
    }
    $pack = $db->fetch(true);
    $oppo = $pack['enabled'] ? 'disabled' : 'enabled';
    if (!count($errors)) {
        $db->query('UPDATE rmstore_packs SET enabled = IF(enabled = 1, 0, 1) WHERE id = ?');
        $db->execute([$_POST['id']]);
        echo Message('RMStore Pack '.format($pack['name']).' has been '.$oppo);
    }
} elseif (array_key_exists('deletermpack', $_POST)) {
    if (!csrf_check('rmstore_delete', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'You didn\'t select a valid pack';
    }
    $db->query('SELECT id, name FROM rmstore_packs WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->count()) {
        $errors[] = 'The pack you selected doesn\'t exist';
    }
    $pack = $db->fetch(true);
    if (!count($errors)) {
        $db->query('DELETE FROM rmstore_packs WHERE id = ?');
        $db->execute([$_POST['id']]);
        echo Message('RMStore Pack '.format($pack['name']).' has been deleted');
    }
} elseif (array_key_exists('addforumdb', $_POST)) {
    if (!csrf_check('board_add', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['auth'] = array_key_exists('auth', $_POST) && in_array($_POST['auth'], $auths) ? $_POST['auth'] : 'public';
    $_POST['bin'] = array_key_exists('bin', $_POST) && isset($_POST['bin']) ? 1 : 0;
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    if (strlen($_POST['name']) < 3) {
        $errors[] = 'Board names must have at least 3 characters';
    }
    if (strlen($_POST['name']) > 40) {
        $errors[] = 'Board names must be no longer than 40 characters';
    }
    $db->query('SELECT fb_id FROM forum_boards WHERE fb_auth = ? AND fb_name = ?');
    $db->execute([$_POST['auth'], $_POST['name']]);
    if ($db->count()) {
        $errors[] = 'That board already exists';
    }
    if ($_POST['bin']) {
        $db->query('SELECT fb_id FROM forum_boards WHERE fb_bin = 1');
        $db->execute();
        if ($db->count()) {
            $errors[] = 'You already have a Recycle Bin assigned. You can\'t set another';
        }
    }
    if (!count($errors)) {
        $db->query('INSERT INTO forum_boards (fb_name, fb_desc, fb_auth, fb_bin) VALUES (?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['desc'], $_POST['auth'], $_POST['bin']]);
        echo Message('You\'ve added the '.$_POST['auth'].' forum board: '.format($_POST['name']));
    }
} elseif (array_key_exists('editforumdb', $_POST)) {
    if (!csrf_check('board_edit', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'You didn\'t select a valid board';
    }
    $db->query('SELECT fb_id FROM forum_boards WHERE fb_id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->count()) {
        $errors[] = 'The board you selected doesn\'t exist';
    }
    $_POST['name'] = array_key_exists('name', $_POST) && is_string($_POST['name']) ? trim(strip_tags($_POST['name'])) : null;
    $_POST['desc'] = array_key_exists('desc', $_POST) && is_string($_POST['desc']) ? trim(strip_tags($_POST['desc'])) : null;
    $_POST['auth'] = array_key_exists('auth', $_POST) && in_array($_POST['auth'], $auths) ? $_POST['auth'] : 'public';
    $_POST['bin'] = array_key_exists('bin', $_POST) && isset($_POST['bin']) ? 1 : 0;
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    if (strlen($_POST['name']) < 3) {
        $errors[] = 'Board names must have at least 3 characters';
    }
    if (strlen($_POST['name']) > 40) {
        $errors[] = 'Board names must be no longer than 40 characters';
    }
    $db->query('SELECT fb_id FROM forum_boards WHERE fb_auth = ? AND fb_name = ? AND fb_id <> ?');
    $db->execute([$_POST['auth'], $_POST['name'], $_POST['id']]);
    if ($db->count()) {
        $errors[] = 'Another '.$_POST['auth'].' board with that name already exists';
    }
    if ($_POST['bin']) {
        $db->query('SELECT fb_id FROM forum_boards WHERE fb_bin = 1 AND fb_id <> ?');
        $db->execute([$_POST['id']]);
        if ($db->count()) {
            $errors[] = 'You already have a board set as your Recycle Bin';
        }
    }
    if (!count($errors)) {
        $db->query('UPDATE forum_boards SET fb_name = ?, fb_desc = ?, fb_auth = ?, fb_bin = ? WHERE fb_id = ?');
        $db->execute([$_POST['name'], $_POST['desc'], $_POST['auth'], $_POST['bin'], $_POST['id']]);
        echo Message('You\'ve edited the '.$_POST['auth'].' forum board: '.format($_POST['name']));
    }
} elseif (array_key_exists('deleteforumdb', $_GET)) {
    if (array_key_exists('submit', $_POST)) {
        if (!csrf_check('delete_forum', $_POST)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        $db->query('SELECT fb_id, fb_name FROM forum_boards WHERE fb_id = ?');
        $db->execute([$_POST['forum']]);
        if (!$db->count()) {
            $errors[] = 'The board you selected for deletion doesn\'t exist';
        }
        $row = $db->fetch(true);
        $name = format($row['fb_name']);
        if (!empty($_POST['recycle'])) {
            $db->query('SELECT fb_id FROM forum_boards WHERE fb_bin = 1');
            $db->execute();
            if (!$db->count()) {
                $errors[] = 'You don\'t have a board set as a Recycle Bin';
            }
            if (!count($errors)) {
                $bin = $db->result();
                $db->trans('start');
                $db->query('UPDATE forum_topics SET ft_board = ? WHERE ft_board = ?');
                $db->execute([$bin, $_POST['forum']]);
                $db->query('UPDATE forum_posts SET fp_board = ? WHERE fp_board = ?');
                $db->execute([$bin, $_POST['forum']]);
                $db->query('DELETE FROM forum_boards WHERE fb_id = ?');
                $db->execute([$_POST['forum']]);
                $db->trans('end');
                echo Message('You\'ve deleted the forum board '.$name.' and recycled its topics and posts');
            }
        } elseif (!empty($_POST['delete'])) {
            $db->trans('start');
            $db->query('DELETE FROM forum_posts WHERE fp_board = ?');
            $db->execute([$_POST['forum']]);
            $db->query('DELETE FROM forum_topics WHERE ft_board = ?');
            $db->execute([$_POST['forum']]);
            $db->query('DELETE FROM forum_boards WHERE fb_id = ?');
            $db->execute([$_POST['forum']]);
            $db->trans('end');
            echo Message('You\'ve deleted the forum board '.$name.', along with its posts and topics');
        } elseif (!empty($_POST['forum2'])) {
            $db->query('SELECT fb_id, fb_name FROM forum_boards WHERE fb_id = ?');
            $db->execute([$_POST['forum2']]);
            if (!$db->count()) {
                $errors[] = 'The post-move destination board you selected doesn\'t exist';
            }
            if (!count($errors)) {
                $dest = $db->fetch(true);
                $name2 = format($dest['fb_name']);
                $db->trans('start');
                $db->query('UPDATE forum_topics SET ft_board = ? WHERE ft_board = ?');
                $db->execute([$_POST['forum2'], $_POST['forum']]);
                $db->query('UPDATE forum_posts SET fp_board = ? WHERE fp_board = ?');
                $db->execute([$_POST['forum2'], $_POST['forum']]);
                $db->query('DELETE FROM forum_boards WHERE fb_id = ?');
                $db->execute([$_POST['forum']]);
                recache_forum($_POST['forum2']);
                $db->trans('end');
                echo Message('You\'ve deleted the forum board '.$name.' and moved its posts and topics to '.$name2);
            }
        }
    } else {
        $db->query('SELECT COUNT(fb_id) FROM forum_boards WHERE fb_id = ?');
        $db->execute([$_GET['deleteforumdb']]);
        if (!$db->count()) {
            echo Message('The board you selected doesn\'t exist', 'Error', true);
        } ?><tr>
            <th class="content-head">Setup Board Deletion</th>
        </tr>
        <tr>
            <td class="content">
                <script type="text/javascript">
                    function checkme() {
                        if (document.theform.forum.value == document.theform.forum2.value) {
                            alert('You cannot select the same forum to move the posts.');
                            return false;
                        }
                        return true;
                    }
                </script>
                <form action="control.php?page=forum&amp;deleteforumdb=<?php echo $_GET['deleteforumdb']; ?>" method="post" name="theform" onsubmit="return checkme();" class="pure-form pure-form-aligned">
                    <?php echo csrf_create('delete_forum'); ?>
                    <div class="pure-control-group">
                        <label for="forum">Board</label>
                        <?php echo forums_boards(); ?>
                    </div>
                    <div class="pure-control-group">
                        <label for="forum2">Destination<br /><span class="small">(only needs to be selected if you're not deleting/recycling the posts and topics)</span></label>
                        <?php echo forums_boards('forum2'); ?>
                    </div>
                    <div class="pure-control-group">
                        <label for="recycle" class="pure-radio">Recycle Posts and Topics<br /><span class="small">(This option will override deletion)</span></label>
                        <input type="checkbox" name="recycle" value="1" />
                    </div>
                    <div class="pure-control-group">
                        <label for="delete" class="pure-radio">Delete Posts and Topics</label>
                        <input type="checkbox" name="delete" value="1" />
                    </div>
                    <div class="pure-controls">
                        <button type="submit" name="submit" class="pure-button pure-button-primary">Delete Board</button>
                    </div>
                </form>
            </td>
        </tr><?php
    }
} elseif (!empty($_GET['givecredit'])) {
    if (!csrf_check('referral_credit_'.$_GET['givecredit'], $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, referrer, referred FROM referrals WHERE id = ?');
    $db->execute([$_GET['givecredit']]);
    if (!$db->count()) {
        echo Message('Invalid referral', 'Error', true);
    }
    $row = $db->fetch(true);
    $db->trans('start');
    $db->query('UPDATE referrals SET credited = 1 WHERE id = ?');
    $db->execute([$_GET['givecredit']]);
    $db->query('UPDATE users SET points = points + 10 WHERE id = ?');
    $db->execute([$row['referrer']]);
    Send_Event($row['referrer'], 'You\'ve been credited 10 points for referring {extra}. Keep up the good work!', $row['referred']);
    $db->trans('end');
    echo Message('You\'ve accepted the referral.');
} elseif (!empty($_GET['denycredit'])) {
    if (!csrf_check('referral_deny_'.$_GET['denycredit'], $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT id, referrer, referred FROM referrals WHERE id = ?');
    $db->execute([$_GET['denycredit']]);
    if (!$db->count()) {
        echo Message('Invalid referral', 'Error', true);
    }
    $row = $db->fetch(true);
    $db->trans('start');
    $db->query('DELETE FROM referrals WHERE id = ?');
    $db->execute([$_GET['denycredit']]);
    Send_Event($row['referrer'], 'Unfortunately, you\'ve received no points for referring {extra}. This could be the result of many different reasons; such as abuse of the referral system, or them signing up but not playing', $row['referred']);
    $db->trans('end');
    echo Message('You\'ve denied the referral.');
} elseif (!empty($_GET['deletejob'])) {
    if (!csrf_check('delete_job_'.$_GET['deletejob'], $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT name FROM jobs WHERE id = ?');
    $db->execute([$_GET['deletejob']]);
    if (!$db->count()) {
        echo Message('Invalid job', 'Error', true);
    }
    $job = $db->result();
    $db->query('DELETE FROM jobs WHERE id = ?');
    $db->execute([$_GET['deletejob']]);
    echo Message('You have deleted job :'.format($job));
} elseif (array_key_exists('addjobdb', $_POST)) {
    if (!csrf_check('job_add', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $nums = ['money', 'strength', 'defense', 'speed', 'level'];
    foreach ($nums as $what) {
        if (empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid amount of '.$what;
        }
    }
    $db->query('SELECT COUNT(id) FROM jobs WHERE name = ?');
    $db->execute([$_POST['name']]);
    if ($db->result()) {
        $errors[] = 'Another job with that name already exist';
    }
    if (!count($errors)) {
        $db->query('INSERT INTO jobs (name, money, strength, defense, speed, level) VALUES (?, ?, ?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['money'], $_POST['strength'], $_POST['defense'], $_POST['speed'], $_POST['level']]);
        echo Message('You\'ve added the job: '.format($_POST['name']));
    }
} elseif (array_key_exists('editjobdb', $_POST)) {
    if (!csrf_check('job_edit', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'Invalid job';
    }
    $db->query('SELECT COUNT(id) FROM jobs WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->result()) {
        $errors[] = 'Job doesn\'t exist';
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $nums = ['money', 'strength', 'defense', 'speed', 'level'];
    foreach ($nums as $what) {
        if (empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid amount of '.$what;
        }
    }
    $db->query('SELECT COUNT(id) FROM jobs WHERE name = ? AND id <> ?');
    $db->execute([$_POST['name'], $_POST['id']]);
    if ($db->result()) {
        $errors[] = 'Another job with that name already exist';
    }
    if (!count($errors)) {
        $db->query('UPDATE jobs SET name = ?, money = ?, strength = ?, defense = ?, speed = ?, level = ? WHERE id = ?');
        $db->execute([$_POST['name'], $_POST['money'], $_POST['strength'], $_POST['defense'], $_POST['speed'], $_POST['level'], $_POST['id']]);
        echo Message('You\'ve edited the job: '.format($_POST['name']));
    }
} elseif (!empty($_GET['deletecity'])) {
    if (!csrf_check('delete_city_'.$_GET['deletecity'], $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('SELECT name FROM cities WHERE id = ?');
    $db->execute([$_GET['deletecity']]);
    if (!$db->count()) {
        $errors[] = 'Invalid city';
    }
    if (!count($errors)) {
        $city = $db->result();
        $db->query('DELETE FROM cities WHERE id = ?');
        $db->execute([$_GET['deletecity']]);
        echo Message('You\'ve deleted the city: '.format($city));
    }
} elseif (array_key_exists('addcitydb', $_POST)) {
    if (!csrf_check('city_add', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $db->query('SELECT COUNT(id) FROM cities WHERE name = ?');
    $db->execute([$_POST['name']]);
    if ($db->result()) {
        $errors[] = 'Another city with that name already exists';
    }
    if (empty($_POST['description'])) {
        $errors[] = 'You didn\'t enter a valid description';
    }
    if (!count($errors)) {
        $db->query('INSERT INTO cities (name, levelreq, landleft, landprice, description) VALUES (?, ?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['levelreq'], $_POST['landleft'], $_POST['landprice'], $_POST['description']]);
        echo Message('You\'ve added the city: '.format($_POST['name']));
    }
} elseif (array_key_exists('editcitydb', $_POST)) {
    if (!csrf_check('city_edit', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'Invalid city';
    }
    $db->query('SELECT COUNT(id) FROM cities WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->result()) {
        $errors[] = 'Invalid city';
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $db->query('SELECT COUNT(id) FROM cities WHERE name = ? AND id <> ?');
    $db->execute([$_POST['name'], $_POST['id']]);
    if ($db->result()) {
        $errors[] = 'Another city with that name already exists';
    }
    if (empty($_POST['description'])) {
        $errors[] = 'You didn\'t enter a valid description';
    }
    if (!count($errors)) {
        $db->query('UPDATE cities SET name = ?, levelreq = ?, landleft = ?, landprice = ?, description = ? WHERE id = ?');
        $db->execute([$_POST['name'], $_POST['levelreq'], $_POST['landleft'], $_POST['landprice'], $_POST['description'], $_POST['id']]);
        echo Message('You\'ve edited the city: '.format($_POST['name']));
    }
} elseif (!empty($_GET['deletecrime'])) {
    if (!csrf_check('delete_crime_'.$_GET['deletecrime'], $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $$db->query('SELECT name FROM crimes WHERE id = ?');
    $db->execuute([$_GET['deletecrime']]);
    if (!$db->count()) {
        $errors[] = 'Invalid crime';
    }
    if (!count($errors)) {
        $crime = $db->result();
        $db->query('DELETE FROM crimes WHERE id = ?');
        $db->execute([$_GET['deletecrime']]);
        echo Message('You\'ve deleted the crime: '.format($crime));
    }
} elseif (array_key_exists('addcrimedb', $_POST)) {
    if (!csrf_check('crime_add', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $strs = ['stext' => 'success text', 'ftext' => 'failure text', 'ctext' => 'jail text'];
    foreach ($strs as $what => $disp) {
        $_POST[$what] = array_key_exists($what, $_POST) && is_string($_POST[$what]) ? strip_tags(trim($_POST[$what])) : null;
        if (empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid '.$disp;
        }
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $db->query('SELECT COUNT(id) FROM crimes WHERE name = ?');
    $db->execute([$_POST['name']]);
    if ($db->result()) {
        $errors[] = 'Another crime with that name already exists';
    }
    if (empty($_POST['nerve'])) {
        $errors[] = 'You didn\'t enter a valid amount of nerve';
    }
    if (!count($errors)) {
        $db->query('INSERT INTO crimes (name, nerve, stext, ftext, ctext) VALUES (?, ?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['nerve'], $_POST['stext'], $_POST['ftext'], $_POST['ctext']]);
        echo Message('You\'ve added the crime: '.format($_POST['name']));
    }
} elseif (array_key_exists('editcrimedb', $_POST)) {
    if (!csrf_check('crime_edit', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'Invalid crime';
    }
    $db->query('SELECT COUNT(id) FROM crimes WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->result()) {
        $errors[] = 'Invalid crime';
    }
    $strs = ['stext' => 'success text', 'ftext' => 'failure text', 'ctext' => 'jail text'];
    foreach ($strs as $what => $disp) {
        $_POST[$what] = array_key_exists($what, $_POST) && is_string($_POST[$what]) ? strip_tags(trim($_POST[$what])) : null;
        if (empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid '.$disp;
        }
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    $db->query('SELECT COUNT(id) FROM crimes WHERE name = ? AND id <> ?');
    $db->execute([$_POST['name'], $_POST['id']]);
    if ($db->result()) {
        $errors[] = 'Another crime with that name already exists';
    }
    if (empty($_POST['nerve'])) {
        $errors[] = 'You didn\'t enter a valid amount of nerve';
    }
    if (!count($errors)) {
        $db->query('UPDATE crimes SET name = ?, nerve = ?, stext = ?, ftext = ?, ctext = ? WHERE id = ?');
        $db->execute([$_POST['name'], $_POST['nerve'], $_POST['stext'], $_POST['ftext'], $_POST['ctext'], $_POST['id']]);
        echo Message('You\'ve edited the crime: '.format($_POST['name']));
    }
} elseif (array_key_exists('additemdb', $_POST)) {
    if (!csrf_check('item_add', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_POST['buyable'] = isset($_POST['buyable']) ? 1 : 0;
    $strs = ['name', 'description'];
    foreach ($strs as $what) {
        if (empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid '.$what;
        }
    }
    $db->query('SELECT COUNT(id) FROM items WHERE name = ?');
    $db->execute([$_POST['name']]);
    if ($db->result()) {
        $errors[] = 'Another item with that name already exists';
    }
    if (!count($errors)) {
        $db->query('INSERT INTO items (name, description, cost, image, offense, defense, heal, reduce, buyable, level) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['description'], $_POST['cost'], $_POST['image'], $_POST['offense'], $_POST['defense'], $_POST['heal'], $_POST['reduce'], $_POST['buyable'], $_POST['level']]);
        echo Message('You\'ve added the item: '.format($_POST['name']));
    }
} elseif (array_key_exists('edititemdb', $_POST)) {
    if (!csrf_check('item_edit', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'You didn\'t select a valid item';
    }
    $db->query('SELECT COUNT(id) FROM items WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->result()) {
        $errors[] = 'The item you selected doesn\'t exist';
    }
    $_POST['buyable'] = isset($_POST['buyable']) ? 1 : 0;
    $strs = ['name', 'description'];
    foreach ($strs as $what) {
        if (empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid '.$what;
        }
    }
    $db->query('SELECT COUNT(id) FROM items WHERE name = ? AND id <> ?');
    $db->execute([$_POST['name'], $_POST['id']]);
    if ($db->result()) {
        $errors[] = 'Another item with that name already exists';
    }
    if (!count($errors)) {
        $db->query('UPDATE items SET name = ?, description = ?, cost = ?, image = ?, offense = ?, defense = ?, heal = ?, reduce = ?, buyable = ?, level = ? WHERE id = ?');
        $db->execute([$_POST['name'], $_POST['description'], $_POST['cost'], $_POST['image'], $_POST['offense'], $_POST['defense'], $_POST['heal'], $_POST['reduce'], $_POST['buyable'], $_POST['level'], $_POST['id']]);
        echo Message('You\'ve edited the item: '.format($_POST['name']));
    }
} elseif (array_key_exists('deleteitemdb', $_POST)) {
    if (!csrf_check('item_delete', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        $errors[] = 'You didn\'t select a valid item';
    }
    if (!itemExists($_POST['id'])) {
        $errors[] = 'The item you selected doesn\'t exist';
    }
    if (!count($errors)) {
        $db->query('DELETE FROM items WHERE id = ?');
        $db->execute([$_POST['id']]);
        echo Message('You\'ve deleted the item: '.item_popup($_POST['id']));
    }
} elseif (!empty($_GET['takealluser'])) {
    if (!csrf_check('item_take_all_'.$_GET['takealluser'], $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_GET['takeallitem'])) {
        $errors[] = 'Invalid item';
    }
    if (!itemExists($_GET['takeallitem'])) {
        $errors[] = 'The item you selected doesn\'t exist';
    }
    $item = item_popup($_GET['takeallitem']);
    $db->query('SELECT COUNT(id) FROM users WHERE id = ?');
    $db->execute([$_GET['takealluser']]);
    if (!$db->result()) {
        $errors[] = 'That player doesn\'t exist';
    }
    $target = new User($_GET['takealluser']);
    $qty = Check_Item($_GET['takeallitem'], $_GET['takealluser']);
    if (!$qty) {
        $errors[] = $target->formattedname.' doesn\'t have any '.$item.s(2);
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('DELETE FROM inventory WHERE userid = ? AND itemid = ?');
        $db->execute([$_GET['takealluser'], $_GET['takeallitem']]);
        Send_Event($_GET['takealluser'], 'Your '.format($qty).' '.$item.($qty == 1 ? 'has' : 's have').' been removed from you by the Administration');
        $db->trans('end');
        echo Message('You\'ve removed '.format($qty).' '.$item.s($qty).' from '.$target->formattedname);
    }
} elseif (array_key_exists('giveitem', $_POST)) {
    if (!csrf_check('item_give', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['itemnumber'])) {
        $errors[] = 'You didn\'t select a valid item';
    }
    if (!itemExists($_POST['itemnumber'])) {
        $errors[] = 'The item you selected doesn\'t exist';
    }
    $item = item_popup($_POST['itemnumber']);
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $id = Get_ID($_POST['username']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    if (!count($errors)) {
        $target = new User($id);
        $db->trans('start');
        Give_Item($_POST['itemnumber'], $id, $_POST['itemquantity']);
        Send_Event($id, 'You\'ve been credited with '.format($_POST['itemquantity']).' '.$item.s($_POST['itemquantity']));
        $db->trans('end');
        $qty = Check_Item($_POST['itemnumber'], $id);
        echo Message('You\'ve credited '.format($_POST['itemquantity']).' '.$item.s($_POST['itemquantity']).' to '.$target->formattedname.'. They now have '.format($qty));
    }
} elseif (array_key_exists('takeitem', $_POST)) {
    if (!csrf_check('item_take', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['itemnumber'])) {
        $errors[] = 'You didn\'t select a valid item';
    }
    if (!itemExists($_POST['itemnumber'])) {
        $errors[] = 'The item you selected doesn\'t exist';
    }
    $item = $db->result();
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $id = Get_ID($_POST['username']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    if (!count($errors)) {
        $target = new User($id);
        $item = item_popup($_POST['itemnumber']);
        $db->trans('start');
        Take_Item($_POST['itemnumber'], $id, $_POST['itemquantity']);
        Send_Event($id, format($_POST['itemquantity']).' '.$item.s($_POST['itemquantity']).' have been taken from you by the Administration');
        $db->trans('end');
        $qty = Check_Item($_POST['itemnumber'], $id);
        echo Message('You\'ve taken '.format($_POST['itemquantity']).' '.$item.s($_POST['itemquantity']).' from '.$target->formattedname.'. They now have '.format($qty));
    }
} elseif (array_key_exists('listitems', $_POST)) {
    if (!csrf_check('item_view', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $id = Get_ID($_POST['username']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $target = new User($id);
    if (!count($errors)) {
        $db->query('SELECT itemid, quantity, name, cost
        FROM inventory
        INNER JOIN items ON itemid = items.id
        WHERE userid = ?
        ORDER BY name ');
        $db->execute([$id]);
        if ($db->count()) {
            $rows = $db->fetch(); ?><tr>
                <th class="content-head"><?php echo $target->formattedname; ?>'s Items</th>
            </tr>
            <tr>
                <td class="content"><?php
            foreach ($rows as $row) {
                printf('<div>%s - %s - Quantity: %s - <a href="control.php?page=playeritems&amp;takealluser=%u&amp;takeallitem=%u&amp;item_take_all_%u=%s">Take All</a></div>', item_popup($row['itemid'], $row['name']), prettynum($row['cost'], true), format($row['quantity']), $id, $row['itemid'], $id, csrf_create('item_take_all_'.$id, false));
            } ?></td>
            </tr><?php
        } else {
            echo Message($target->formattedname.' doesn\'t have any items');
        }
    }
} elseif (array_key_exists('changemessage', $_POST)) {
    if (!csrf_check('admin_message', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('UPDATE serverconfig SET messagefromadmin = ? WHERE id = 1');
    $db->execute([$_POST['message']]);
    echo Message('You\'ve changed the message from the admin.');
} elseif (array_key_exists('changeserverdown', $_POST)) {
    if (!csrf_check('admin_serverdown', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $db->query('UPDATE serverconfig SET serverdown = ? WHERE id = 1');
    $db->execute([$_POST['message']]);
    echo Message('You\'ve changed the server down text.');
} elseif (array_key_exists('addrmdays', $_POST)) {
    if (!csrf_check('rmoptions_days', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $id = Get_ID($_POST['username']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    if (empty($_POST['rmdays'])) {
        $errors[] = 'You didn\'t enter a valid amount of days';
    }
    if (!count($errors)) {
        $target = new User($id);
        $db->trans('start');
        $db->query('UPDATE users SET rmdays = rmdays + ? WHERE id = ?');
        $db->execute([$_POST['rmdays'], $id]);
        Send_Event($id, 'You\'ve been credited with '.format($_POST['rmdays']).' RM Day'.s($_POST['rmdays']).' by the Administration');
        $db->trans('end');
        echo Message('You\'ve credited '.$target->formattedname.' with '.format($_POST['rmdays']).' RM Day'.s($_POST['rmdays']));
    }
} elseif (array_key_exists('addpoints', $_POST)) {
    if (!csrf_check('rmoptions_points', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $id = Get_ID($_POST['username']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    if (empty($_POST['points'])) {
        $errors[] = 'You didn\'t enter a valid amount of points';
    }
    if (!count($errors)) {
        $target = new User($id);
        $db->trans('start');
        $db->query('UPDATE users SET points = points + ? WHERE id = ?');
        $db->execute([$_POST['points'], $id]);
        Send_Event($id, 'You\'ve been credited with '.format($_POST['points']).' point'.s($_POST['points']).' by the Administration');
        $db->trans('end');
        echo Message('You\'ve credited '.$target->formattedname.' with '.format($_POST['points']).' point'.s($_POST['points']));
    }
} elseif (array_key_exists('addhookers', $_POST)) {
    if (!csrf_check('rmoptions_hookers', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $id = Get_ID($_POST['username']);
    if (!$id) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    if (empty($_POST['hookers'])) {
        $errors[] = 'You didn\'t enter a valid amount of hookers';
    }
    if (!count($errors)) {
        $target = new User($id);
        $db->trans('start');
        $db->query('UPDATE users SET hookers = hookers + ? WHERE id = ?');
        $db->execute([$_POST['hookers'], $id]);
        Send_Event($id, 'You\'ve been credited with '.format($_POST['hookers']).' hooker'.s($_POST['hookers']).' by the Administration');
        $db->trans('end');
        echo Message('You\'ve credited '.$target->formattedname.' with '.format($_POST['hookers']).' hooker'.s($_POST['hookers']));
    }
} elseif (array_key_exists('action', $_GET) && $_GET['action'] === 'deleteallfromip') {
    if (!csrf_check('ip_delete', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $_GET['ip'] = array_key_exists('ip', $_GET) && filter_var($_GET['ip'], FILTER_VALIDATE_IP) ? $_GET['ip'] : null;
    if (empty($_GET['ip'])) {
        $errors[] = 'You didn\'t enter a valid IP address';
    }
    $db->query('SELECT COUNT(id) FROM users WHERE ip = ?');
    $db->execute([$_GET['ip']]);
    $cnt = $db->result();
    if (!$cnt) {
        $errors[] = 'There are no players on IP: '.format($_GET['ip']);
    }
    if (!count($errors)) {
        $db->query('DELETE FROM users WHERE ip = ?');
        $db->execute([$_GET['ip']]);
        echo Message(format($cnt).' account'.s($cnt).' '.($cnt == 1 ? 'has' : 'have').' been deleted');
    }
} elseif (array_key_exists('adminstatus', $_POST)) {
    if (!csrf_check('status_admin_grant', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $db->query('SELECT id, admin FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if (!$db->count()) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $target = new User($row['id']);
    if ($row['admin']) {
        $errors[] = $target->formattedname.' is already an Administrator';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET admin = 1 WHERE id = ?');
        $db->execute([$row['id']]);
        Send_Event($row['id'], 'You\'ve been granted Administrator privileges');
        $db->trans('end');
        echo Message('You\'ve granted Administrator privileges to '.$target->formattedname);
    }
} elseif (array_key_exists('revokeadminstatus', $_POST)) {
    if (!csrf_check('status_admin_revoke', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $db->query('SELECT id, admin FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if (!$db->count()) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $target = new User($row['id']);
    if (!$row['admin']) {
        $errors[] = $target->formattedname.' isn\'t an Administrator';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET admin = 0 WHERE id = ?');
        $db->execute([$row['id']]);
        Send_Event($row['id'], 'Your Administrator privileges have been revoked');
        $db->trans('end');
        echo Message('You\'ve revoked '.$target->formattedname.'\'s Administrator privileges');
    }
} elseif (array_key_exists('banplayer', $_POST)) {
    $_POST['reason'] = array_key_exists('reason', $_POST) && is_string($_POST['reason']) ? strip_tags(trim($_POST['reason'])) : '';
    if (!csrf_check('status_ban', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    if (empty($_POST['reason'])) {
        $erros[] = 'You did not enter a reason';
    }
    $db->query('SELECT id, ban FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if (!$db->count()) {
        $errors[] = 'The player you selected does not exist';
    }
    $row = $db->fetch(true);
    $target = new User($row['id']);
    if ($row['ban']) {
        $errors[] = $target->formattedname.' is already banned';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET ban = 1 WHERE id = ?');
        $db->execute([$row['id']]);
        $db->query('INSERT INTO site_bans (userid,reason,banner) VALUES(?,?,?)');
        $db->execute([$row['id'], $_POST['reason'], $user_class->id]);
        echo Message('You\'ve banned '.$target->formattedname);
    }
} elseif (array_key_exists('president', $_POST)) {
    if (!csrf_check('status_president_grant', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $db->query('SELECT id, admin FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if (!$db->count()) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $target = new User($row['id']);
    if ($row['admin'] == 3) {
        $errors[] = $target->formattedname.' is already a president';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET admin = 3 WHERE id = ?');
        $db->execute([$row['id']]);
        Send_Event($row['id'], 'You\'ve been granted President privileges');
        $db->trans('end');
        echo Message('You\'ve granted Presidential privileges to '.$target->formattedname);
    }
} elseif (array_key_exists('impeachpresident', $_POST)) {
    if (!csrf_check('status_president_revoke', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $db->query('SELECT id, admin FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if (!$db->count()) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $target = new User($row['id']);
    if ($row['admin'] != 3) {
        $errors[] = $target->formattedname.' isn\'t a President';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET admin = 0 WHERE id = ?');
        $db->execute([$row['id']]);
        echo Message('You\'ve revoked '.$target->formattedname.'\'s Presidential privileges');
    }
} elseif (array_key_exists('congress', $_POST)) {
    if (!csrf_check('status_congress_grant', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $db->query('SELECT id, admin FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if (!$db->count()) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $target = new User($row['id']);
    if ($row['admin'] == 4) {
        $errors[] = $target->formattedname.' is already a congeressman';
    }
    if (!count($errors)) {
        $db->trans('start');
        $db->query('UPDATE users SET admin = 4 WHERE id = ?');
        $db->execute([$row['id']]);
        Send_Event($row['id'], 'You\'ve been granted Congress privileges');
        $db->trans('end');
        echo Message('You\'ve granted Congress privileges to '.$target->formattedname);
    }
} elseif (array_key_exists('impeachcongress', $_POST)) {
    if (!csrf_check('status_congress_revoke', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['username'])) {
        $errors[] = 'You didn\'t select a valid player';
    }
    $db->query('SELECT id, admin FROM users WHERE username = ?');
    $db->execute([$_POST['username']]);
    if (!$db->count()) {
        $errors[] = 'The player you selected doesn\'t exist';
    }
    $row = $db->fetch(true);
    $target = new User($row['id']);
    if ($row['admin'] != 4) {
        $errors[] = $target->formattedname.' isn\'t a Congressman';
    }
    if (!count($errors)) {
        $db->query('UPDATE users SET admin = 0 WHERE id = ?');
        $db->execute([$row['id']]);
        echo Message('You\'ve revoked '.$target->formattedname.'\'s Congress privileges');
    }
} elseif (array_key_exists('addvotesite', $_POST)) {
    if (!csrf_check('votesite_add', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $errors = [];
    $strs = ['title', 'url', 'reward_items'];
    foreach ($strs as $what) {
        $_POST[$what] = array_key_exists($what, $_POST) && is_string($_POST[$what]) ? trim(strip_tags($_POST[$what])) : null;
        if ($what === 'url') {
            $_POST['url'] = filter_var($_POST['url'], FILTER_VALIDATE_URL) ? $_POST['url'] : null;
        }
        if ($_POST[$what] !== 'reward_items' && empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid '.$what;
        }
    }
    $nums = ['reward_cash', 'reward_points', 'reward_rmdays', 'req_account_days_min', 'req_account_days_max', 'req_rmdays', 'days_between_vote'];
    foreach ($nums as $what) {
        $_POST[$what] = array_key_exists($what, $_POST) && ctype_digit(str_replace(',', '', $_POST[$what])) ? str_replace(',', '', $_POST[$what]) : 0;
        if (!$_POST['days_between_vote']) {
            $_POST['days_between_vote'] = 1;
        }
    }
    $db->query('SELECT COUNT(id) FROM voting_sites WHERE url = ?');
    $db->execute([$_POST['url']]);
    if ($db->result()) {
        $errors[] = 'Another voting site with that URL already exists';
    }
    if (count($errors)) {
        display_errors($errors);
    } else {
        $addText = '<a href="'.format($_POST['url']).'">'.format($_POST['title']).'</a>';
        $db->query('INSERT INTO voting_sites (title, url, reward_cash, reward_points, reward_rmdays, reward_items, req_account_days_min, req_account_days_max, req_rmdays, days_between_vote) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
        $db->execute([$_POST['title'], $_POST['url'], $_POST['reward_cash'], $_POST['reward_points'], $_POST['reward_rmdays'], $_POST['reward_items'], $_POST['req_account_days_min'], $_POST['req_account_days_max'], $_POST['req_rmdays'], $_POST['days_between_vote']]);
        echo Message('You\'ve added the voting site: '.$addText);
    }
} elseif (array_key_exists('editvote', $_POST)) {
    if (!csrf_check('votesite_edit', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['id'])) {
        echo Message('You didn\'t select a valid site', 'Error', true);
    }
    $db->query('SELECT * FROM voting_sites WHERE id = ?');
    $db->execute([$_POST['id']]);
    if (!$db->count()) {
        echo Message('That voting site doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if (array_key_exists('submit', $_POST)) {
        $errors = [];
        $strs = ['title', 'url', 'reward_items'];
        foreach ($strs as $what) {
            $_POST[$what] = array_key_exists($what, $_POST) && is_string($_POST[$what]) ? trim(strip_tags($_POST[$what])) : null;
            if ($what === 'url') {
                $_POST['url'] = filter_var($_POST['url'], FILTER_VALIDATE_URL) ? $_POST['url'] : null;
            }
            if ($_POST[$what] !== 'reward_items' && empty($_POST[$what])) {
                $errors[] = 'You didn\'t enter a valid '.$what;
            }
        }
        $nums = ['reward_cash', 'reward_points', 'reward_rmdays', 'req_account_days_min', 'req_account_days_max', 'req_rmdays', 'days_between_vote'];
        foreach ($nums as $what) {
            $_POST[$what] = array_key_exists($what, $_POST) && ctype_digit(str_replace(',', '', $_POST[$what])) ? str_replace(',', '', $_POST[$what]) : 0;
        }
        $db->query('SELECT COUNT(id) FROM voting_sites WHERE url = ? AND id <> ?');
        $db->execute([$_POST['url'], $_POST['id']]);
        if ($db->result()) {
            $errors[] = 'Another voting site with that URL already exists';
        }
        if (count($errors)) {
            display_errors($errors);
        } else {
            $editText = '<a href="'.format($_POST['url']).'">'.format($_POST['title']).'</a>';
            $db->trans('start');
            $db->query('UPDATE voting_sites SET title = ?, url = ?, reward_cash = ?, reward_points = ?, reward_rmdays = ?, reward_items = ?, req_account_days_min = ?, req_account_days_max = ?, req_rmdays = ?, days_between_vote = ? WHERE id = ?');
            $db->execute([$_POST['title'], $_POST['url'], $_POST['reward_cash'], $_POST['reward_points'], $_POST['reward_rmdays'], $_POST['reward_items'], $_POST['req_account_days_min'], $_POST['req_account_days_max'], $_POST['req_rmdays'], $_POST['days_between_vote'], $_POST['id']]);
            $db->trans('end');
            echo Message('You\'ve added the voting site: '.$editText);
        }
    }
} elseif (array_key_exists('deletevotesite', $_GET)) {
    if (empty($_GET['id'])) {
        echo Message('You didn\'t select a valid voting site', 'Error', true);
    }
    $db->query('SELECT id, title FROM voting_sites WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('That voting site doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if (array_key_exists('ans', $_GET)) {
        if (!csrf_check('csrf', $_GET)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        $db->query('DELETE FROM voting_sites WHERE id = ?');
        $db->execute([$row['id']]);
        echo Message('You\'ve deleted the voting site: '.format($row['title']));
    } else {
        ?>Are you sure you want to delete &ldquo;<?php echo format($row['title']); ?>&rdquo;?<br />
        <a href="control.php?page=voting&amp;action=delete&amp;id=<?php echo $row['id']; ?>&amp;ans=yes&amp;csrf=<?php echo csrf_create('csrf', false); ?>" class="pure-button pure-button-red"><i class="fa fa-ban" aria-hidden="true"></i> I'm sure, delete it</a>
        <a href="control.php?page=voting" class="pure-button pure-button-primary"><i class="fa fa-tick" aria-hidden="true"></i> No, go back</a><?php
    }
} elseif (array_key_exists('addhouse', $_GET)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_POST['name'])) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    if ($_POST['awake'] < 100) {
        $errors[] = 'You didn\'t enter a valid amount of awake lowest is 100';
    }
    if (empty($_POST['cost']) && !is_numeric($_POST['cost'])) {
        $errors[] = 'You didn\'t enter a valid cost';
    }
    $db->query('SELECT COUNT(id) FROM houses WHERE awake = ?');
    $db->execute([$_POST['awake']]);
    if ($db->result()) {
        $errors[] = 'Another house with '.format($_POST['awake']).' already exists';
    }
    $db->query('SELECT COUNT(id) FROM houses WHERE name = ?');
    $db->execute([$_POST['name']]);
    if ($db->count()) {
        $errors[] = 'There is already a house by that name.';
    }
    $_POST['buyable'] = isset($_POST['buyable']) ? 1 : 0;
    if (count($errors)) {
        display_errors($errors);
    } else {
        $db->query('INSERT INTO houses (name, awake, cost, buyable) VALUES (?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['awake'], $_POST['cost'], $_POST['buyable']]);
        echo Message('You\'ve added the house: '.format($_POST['name']));
    }
} elseif (array_key_exists('edithouse', $_GET)) {
    if ($_GET['id'] === null) {
        echo Message('You didn\'t select a valid house', 'Error', true);
    }
    $db->query('SELECT * FROM houses WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('The house you selected doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if (array_key_exists('submit', $_POST)) {
        if ($_POST['name'] === null) {
            $errors[] = 'You didn\'t enter a valid name';
        }
        if ($_POST['awake'] < 100) {
            $errors[] = 'You didn\'t enter a valid amount of awake';
        }
        if ($_POST['cost'] === null) {
            $errors[] = 'You didn\'t enter a valid cost';
        }
        $db->query('SELECT COUNT(id) FROM houses WHERE awake = ? AND id <> ?');
        $db->execute([$_POST['awake'], $_GET['id']]);
        if ($db->result()) {
            $errors[] = 'Another house with '.format($_POST['awake']).' already exists';
        }
        if (count($errors)) {
            display_errors($errors);
        } else {
            $db->query('UPDATE houses SET name = ?, awake = ?, cost = ? WHERE id = ?');
            $db->execute([$_POST['name'], $_POST['awake'], $_POST['cost'], $_GET['id']]);
            echo Message('You\'ve edited the house: '.format($_POST['name']));
        }
    } else {
        ?>
        <tr>
            <th class="content-head">Edit House</th>
        </tr>
        <tr>
            <td class="content">
                <form action="control.php?page=houses&amp;edithouse&amp;id=<?php echo $row['id']; ?>" method="post" class="pure-form pure-form-aligned">
                    <?php echo csrf_create(); ?>
                    <div class="pure-control-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="<?php echo format($row['name']); ?>" class="pure-u-1-2 pure-u-md-1-2" required autofocus />
                    </div>
                    <div class="pure-control-group">
                        <label for="awake">Awake</label>
                        <input type="text" name="awake" id="awake" value="<?php echo format($row['awake']); ?>" class="pure-u-1-2 pure-u-md-1-2" required />
                    </div>
                    <div class="pure-control-group">
                        <label for="cost">Cost</label>
                        <input type="text" name="cost" id="cost" value="<?php echo format($row['cost']); ?>" class="pure-u-1-2 pure-u-md-1-2" required />
                    </div>
                    <div class="pure-control-group">
                        <label for="buyable" class="pure-radio">
                            <input type="checkbox" name="buyable" id="buyable" value="1"<?php echo $row['buyable'] ? ' checked' : ''; ?>>
                        </label>
                        Buyable
                    </div>
                    <div class="pure-controls">
                        <button type="submit" name="submit" class="pure-button pure-button-primary">Edit House</button>
                    </div>
                </form>
            </td>
        </tr><?php
    }
} elseif (array_key_exists('deletehouse', $_GET)) {
    if ($_GET['id'] === null) {
        echo Message('You didn\'t select a valid house', 'Error', true);
    }
    $db->query('SELECT id, name, awake FROM houses WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('The house you selected doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if (array_key_exists('ans', $_GET)) {
        if (!csrf_check('delcsrf', $_GET)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        $db->trans('start');
        $db->query('DELETE FROM houses WHERE id = ?');
        $db->execute([$row['id']]);
        $db->query('UPDATE users SET house = 1 WHERE house = ?');
        $db->execute([$row['id']]);
        $db->trans('end');
        echo Message('You\'ve deleted the house: '.format($row['name']));
    } else {
        $csrf = csrf_create('delcsrf', false); ?>
        <tr>
            <td class="content">
                Are you sure you wish to delete the <?php echo format($row['awake']); ?> house: &ldquo;<?php echo format($row['name']); ?>&rdquo;?<br />
                <a href="control.php?page=houses&amp;deletehouse&amp;id=<?php echo $row['id']; ?>&amp;ans=yes&amp;delcsrf=<?php echo $csrf; ?>" class="pure-button pure-button-primary">Yes, delete it</a>
            </td>
        </tr><?php
    }
} elseif (array_key_exists('addcar', $_GET)) {
    if (!csrf_check('csrf', $_POST)) {
         echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($_POST['name'] === null) {
        $errors[] = 'You didn\'t enter a valid name';
    }
    if ($_POST['level'] < 1) {
        $errors[] = 'You didn\'t enter a valid level';
    }
    if ($_POST['cost'] === null) {
        $errors[] = 'You didn\'t enter a valid cost';
    }
    if ($_POST['description'] === null) {
        $errors[] = 'You didn\'t enter a valid description';
    }
    if ($_POST['image'] === null) {
        $errors[] = 'You didn\'t enter a valid image URL';
    }
    if (!isImage($_POST['image'])) {
        $errors[] = 'The image you selected didn\'t validate - are you sure it\'s a <strong>direct</strong> URL?';
    }
    $db->query('SELECT COUNT(id) FROM carlot WHERE name = ?');
    $db->execute([$_POST['name']]);
    if ($db->result()) {
        $errors[] = 'Another car with the name of '.format($_POST['name']).' already exists';
    }
    if (count($errors)) {
        display_errors($errors);
    } else {
        $db->query('INSERT INTO carlot (name, description, image, buyable, cost, level) VALUES (?, ?, ?, ?, ?, ?)');
        $db->execute([$_POST['name'], $_POST['description'], $_POST['image'], $_POST['buyable'], $_POST['cost'], $_POST['level']]);
        echo Message('You\'ve added the car: '.format($_POST['name']));
    }
} elseif (array_key_exists('editcar', $_GET)) {
    if ($_GET['id'] === null) {
        echo Message('You didn\'t select a valid car', 'Error', true);
    }
    $db->query('SELECT * FROM carlot WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('The car you selected doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if (array_key_exists('submit', $_POST)) {
        if (!csrf_check('editcsrf', $_POST)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        if ($_POST['name'] === null) {
            $errors[] = 'You didn\'t enter a valid name';
        }
        if ($_POST['level'] < 1) {
            $errors[] = 'You didn\'t enter a valid level';
        }
        if ($_POST['cost'] === null) {
            $errors[] = 'You didn\'t enter a valid cost';
        }
        if ($_POST['description'] === null) {
            $errors[] = 'You didn\'t enter a valid description';
        }
        if ($_POST['image'] === null) {
            $errors[] = 'You didn\'t enter a valid image URL';
        }
        if (!isImage($_POST['image'])) {
            $errors[] = 'The image you selected didn\'t validate - are you sure it\'s a <strong>direct</strong> URL?';
        }
        $db->query('SELECT COUNT(id) FROM carlot WHERE name = ? AND id <> ?');
        $db->execute([$_POST['name'], $row['id']]);
        if ($db->result()) {
            $errors[] = 'Another car with the name of '.format($_POST['name']).' already exists';
        }
        if (count($errors)) {
            display_errors($errors);
        } else {
            $db->query('UPDATE carlot SET name = ?, description = ?, image = ?, buyable = ?, cost = ?, level = ? WHERE id = ?');
            $db->execute([$_POST['name'], $_POST['description'], $_POST['image'], $_POST['buyable'], $_POST['cost'], $_POST['level'], $row['id']]);
            echo Message('You\'ve edited the car: '.format($_POST['name']));
        }
    } else {
        ?>
        <tr>
            <th class="content-head">Edit Car</th>
        </tr>
        <tr>
            <td class="content">
                <form action="control.php?page=cars&amp;editcar&amp;id=<?php echo $row['id']; ?>" method="post" class="pure-form pure-form-aligned">
                    <?php echo csrf_create('editcsrf'); ?>
                    <div class="pure-control-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" value="<?php echo format($row['name']); ?>" class="pure-u-1-2 pure-u-md-1-2" required autofocus />
                    </div>
                    <div class="pure-control-group">
                        <label for="level">Level</label>
                        <input type="text" name="level" id="level" value="<?php echo format($row['level']); ?>" class="pure-u-1-2 pure-u-md-1-2" required />
                    </div>
                    <div class="pure-control-group">
                        <label for="cost">Cost</label>
                        <input type="text" name="cost" id="cost" value="<?php echo format($row['cost']); ?>" class="pure-u-1-2 pure-u-md-1-2" required />
                    </div>
                    <div class="pure-control-group">
                        <label for="image">Image</label>
                        <input type="text" name="image" id="image" value="<?php echo format($row['image']); ?>" class="pure-u-1-2 pure-u-md-1-2" required />
                    </div>
                    <div class="pure-control-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" class="pure-u-1-2 pure-u-md-1-2" required><?php echo format($row['description']); ?></textarea>
                    </div>
                    <div class="pure-control-group">
                        <label for="buyable" class="pure-radio">
                            <input type="checkbox" name="buyable" id="buyable" value="1"<?php echo $row['buyable'] ? ' checked' : ''; ?>>
                        </label>
                        Buyable
                    </div>
                    <div class="pure-controls">
                        <button type="submit" name="submit" class="pure-button pure-button-primary">Edit Car</button>
                    </div>
                </form>
            </td>
        </tr><?php
    }
} elseif (array_key_exists('deletecar', $_GET)) {
    if ($_GET['id'] === null) {
        echo Message('You didn\'t select a valid car', 'Error', true);
    }
    $db->query('SELECT id, name, cost FROM carlot WHERE id = ?');
    $db->execute([$_GET['id']]);
    if (!$db->count()) {
        echo Message('The car you selected doesn\'t exist', 'Error', true);
    }
    $row = $db->fetch(true);
    if (array_key_exists('ans', $_GET)) {
        if (!csrf_check('delcsrf', $_GET)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        $db->trans('start');
        $db->query('DELETE FROM cars WHERE carid = ?');
        $db->execute([$row['id']]);
        $db->query('DELETE FROM carlot WHERE id = ?');
        $db->execute([$row['id']]);
        $db->trans('end');
        echo Message('You\'ve deleted the car: '.format($row['name']));
    } else {
        $csrf = csrf_create('delcsrf', false); ?>
        <tr>
            <td class="content">
                Are you sure you wish to delete the <?php echo formatCurrency($row['cost']); ?> car: &ldquo;<?php echo format($row['name']); ?>&rdquo;?<br />
                <a href="control.php?page=cars&amp;deletecar&amp;id=<?php echo $row['id']; ?>&amp;ans=yes&amp;delcsrf=<?php echo $csrf; ?>" class="pure-button pure-button-primary">Yes, delete it</a>
            </td>
        </tr><?php
    }
} elseif (array_key_exists('settings', $_GET)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $updated = [];
    $_POST['registration'] = array_key_exists('registration', $_POST) && in_array(strtolower($_POST['registration']), ['open', 'closed']) ? strtolower($_POST['registration']) : 'open';
    $db->trans('start');
    if ($_POST['registration'] != settings('registration')) {
        $db->query('UPDATE settings SET conf_value = ? WHERE conf_name = \'registration\'');
        $db->execute([$_POST['registration']]);
        $updated[] = 'registration';
    }
    $db->trans('end');
    $cnt = count($updated);
    if (!$cnt) {
        $msg = 'Nothing was changed';
    } elseif ($cnt == 1) {
        $msg = 'You\'ve updated the '.$updated[0].' setting';
    } else {
        $last_element = array_pop($updated);
        $updated[] = 'and '.$last_element;
        $msg = 'You\'ve updated the '.implode(', ', $updated).' settings';
    }
    echo Message($msg);
}
?><tr>
    <th class="content-head">Control Panel</th>
</tr>
<tr>
    <td class="content">
        Welcome to the control panel. Here you can do just about anything, from giving players items they have paid for with real money, to adding, changing, or deleting jobs, cities, items, etc.<br /><br />
        Please send any ideas for things that need to be added to the control panel to comments@thegrpg.com <br /><br />
        If you are experiencing problems with any of the options, try clicking the submit button instead of pressing the enter key.
    </td>
</tr><?php
if (count($errors)) {
    display_errors($errors);
}
$_GET['page'] = $_GET['page'] ?? null;
if (empty($_GET['page'])) {
    $db->query('SELECT * FROM serverconfig');
    $db->execute();
    $set = $db->fetch(true); ?><tr>
        <th class="content-head">Change Message From The Admin</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('admin_message'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <textarea name="message" id="message" cols="53" rows="7"><?php echo format($set['messagefromadmin']); ?></textarea>
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="changemessage" class="pure-button pure-button-primary">Change Message From Admin</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Change Server Down Text</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('admin_serverdown'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <textarea name="message" id="message" cols="53" rows="7"><?php echo format($set['serverdown']); ?></textarea>
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="changeserverdown" class="pure-button pure-button-primary">Change Server Down Text</button>
                </div>
            </form>
        </td>
    </tr><?php
} elseif ($_GET['page'] === 'rmpacks') {
        ?><tr>
        <th class="content-head">Add RMStore Upgrade</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=rmpacks" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('rmstore_add'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" required />
                    </div>
                    <div class="pure-control-group">
                        <label for="pack_cost">Cost (<?php echo RMSTORE_CURRENCY; ?>)</label>
                        <input type="text" name="pack_cost" id="pack_cost" required />
                    </div>
                    <div class="pure-control-group">
                        <label for="rmdays">RM Days</label>
                        <input type="text" name="rmdays" id="rmdays" placeholder="Optional" />
                    </div>
                    <div class="pure-control-group">
                        <label for="money">Money</label>
                        <input type="text" name="money" id="money" placeholder="Optional" />
                    </div>
                    <div class="pure-control-group">
                        <label for="points">Points</label>
                        <input type="text" name="points" id="points" placeholder="Optional" />
                    </div>
                    <div class="pure-control-group">
                        <label for="prostitutes">Prostitutes</label>
                        <input type="text" name="prostitutes" id="prostitutes" placeholder="Optional" />
                    </div>
                    <div class="pure-control-group">
                        <label for="items">Items<span class="red">*</span></label>
                        <textarea name="items" id="items" rows="5" cols="40"></textarea>
                    </div>
                    <div class="pure-control-group">
                        <label for="enabled" class="pure-checkbox">
                            <input type="checkbox" name="enabled" id="enabled" value="1" checked />
                        </label>
                        Enabled
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addrmpack" class="pure-button pure-button-primary">Add RMStore Upgrade</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Enable/Disable RMStore Upgrade</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=rmpacks" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('rmstore_disenable'); ?>
                <div class="pure-control-group">
                    <label for="id_disenable">Upgrade</label>
                    <?php listRMPacks(true, 'id_disenable'); ?>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="disenablermpack" class="pure-button pure-button-primary">Enable/Disable Upgrade</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Edit RMStore Upgrade</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=rmpacks" method="post" class="pure-form pure-form-aligned">
                <div class="pure-control-group">
                    <label for="id_edit">Upgrade</label>
                    <?php listRMPacks(false, 'id_edit'); ?>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="editrmpackstart" class="pure-button pure-button-primary">Begin Upgrade Edit</button>
                </div>
            </form>
        </td>
    </tr><?php
    if (array_key_exists('editrmpackstart', $_POST)) {
        if (empty($_POST['id'])) {
            echo Message('You didn\'t select a valid upgrade', 'Error', true);
        }
        $db->query('SELECT * FROM rmstore_packs WHERE id = ?');
        $db->execute([$_POST['id']]);
        if (!$db->count()) {
            echo Message('The upgrade you selected doesn\'t exist', 'Error', true);
        }
        $row = $db->fetch(true); ?><tr>
            <td class="content">
                <form action="control.php?page=rmpacks" method="post" class="pure-form pure-form-aligned">
                    <?php echo csrf_create('rmstore_edit'); ?>
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                    <fieldset>
                        <div class="pure-control-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" value="<?php echo format($row['name']); ?>" required />
                        </div>
                        <div class="pure-control-group">
                            <label for="pack_cost">Cost (<?php echo RMSTORE_CURRENCY; ?>)</label>
                            <input type="text" name="pack_cost" id="pack_cost" value="<?php echo format($row['cost'], 2); ?>" required />
                        </div>
                        <div class="pure-control-group">
                            <label for="rmdays">RM Days</label>
                            <input type="text" name="rmdays" id="rmdays" placeholder="Optional" value="<?php echo format($row['days']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="money">Money</label>
                            <input type="text" name="money" id="money" placeholder="Optional" value="<?php echo format($row['money']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="points">Points</label>
                            <input type="text" name="points" id="points" placeholder="Optional" value="<?php echo format($row['points']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="prostitutes">Prostitutes</label>
                            <input type="text" name="prostitutes" id="prostitutes" placeholder="Optional" value="<?php echo format($row['prostitutes']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="items">Items<span class="red">*</span></label>
                            <textarea name="items" id="items" rows="5" cols="40"><?php echo format($row['items']); ?></textarea>
                        </div>
                        <div class="pure-control-group">
                            <label for="enabled" class="pure-checkbox">
                                <input type="checkbox" name="enabled" id="enabled" value="1"<?php echo $row['enabled'] ? ' checked' : ''; ?> />
                            </label>
                            Enabled
                        </div>
                    </fieldset>
                    <div class="pure-controls">
                        <button type="submit" name="editrmpack" class="pure-button pure-button-primary">Edit RMStore Upgrade</button>
                    </div>
                </form>
            </td>
        </tr><?php
    } ?><tr>
        <th class="content-head">Delete RMStore Upgrade</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=rmpacks" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('rmstore_delete'); ?>
                <div class="pure-control-group">
                    <label for="id_delete">Upgrade</label>
                    <?php echo listRMPacks(false, 'id_delete'); ?>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="deletermpack" class="pure-button pure-button-primary">Delete RMStore Upgrade</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <td class="content">
            <span class="red">*****</span><br />
            One of my infamous "tricky-at-first" entries for items! Yay! (I will add an easier method at some point)<br />
            Formats accepted:<br />
            <ul>
                <li><span class="green">item ID</span></li>
                <li><span class="green">item ID</span>:<span class="yellow">quantity</span></li>
                <li><span class="green">item ID</span>:<span class="yellow">quantity</span>,<span class="green">item ID</span>:<span class="yellow">quantity</span>,<span class="green">item ID</span>:<span class="yellow">quantity</span> - rinse and repeat for desired amount of items</li>
            </ul><br />
            You can also mix and match the formats<br />
            <ul>
                <li><span class="green">item ID</span>:<span class="yellow">quantity</span>,<span class="green">item ID</span>,<span class="green">item ID</span>,<span class="green">item ID</span>:<span class="yellow">quantity</span></li>
            </ul><br />
            Any <span class="green">item ID</span> supplied that doesn\'t have a given <span class="yellow">quantity</span> will automatically have a <span class="yellow">quantity</span> of 1<br />
            <span class="red">*****</span>
        </td>
    </tr><?php
    } elseif ($_GET['page'] === 'rmoptions') {
        ?><tr>
        <th class="content-head">Add RM Days</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=rmoptions" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('rmoptions_days'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="rmdays">RM Days</label>
                        <input type="text" name="rmdays" id="rmdays" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addrmdays" class="pure-button pure-button-primary">Add RM Days</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Add Points</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=rmoptions" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('rmoptions_points'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="points">Points</label>
                        <input type="text" name="points" id="points" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addpoints" class="pure-button pure-button-primary">Give Points</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Add Hookers</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=rmoptions" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('rmoptions_hookers'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="hookers">Hookers</label>
                        <input type="text" name="hookers" id="hookers" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addhookers" class="pure-button pure-button-primary">Give Hookers</button>
                </div>
            </form>
        </td>
    </tr><?php
    } elseif ($_GET['page'] === 'setplayerstatus') {
        ?><tr>
        <th class="content-head">Ban a Player</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=setplayerstatus" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('status_ban'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="reason">Reason for banning</label>
                        <input type="text" name="reason" id="reason" size="20" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="banplayer" class="pure-button pure-button-primary">Ban Player</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Give Admin Status</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=setplayerstatus" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('status_admin_grant'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="adminstatus" class="pure-button pure-button-primary">Change Admin Status</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Revoke Admin Status</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=setplayerstatus" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('status_admin_revoke'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="revokeadminstatus" class="pure-button pure-button-primary">Revoke Admin Status</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Presidential Election</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=setplayerstatus" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('status_president_grant'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="president" class="pure-button pure-button-primary">Elect President</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Impeach President</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=setplayerstatus" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('status_president_revoke'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="impeachpresident" class="pure-button pure-button-primary">Impeach President</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Congressional Elections</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=setplayerstatus" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('status_congress_grant'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="congress" class="pure-button pure-button-primary">Elect Congressman</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Impeach Congress</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=setplayerstatus" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('status_congress_revoke'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="impeachcongress" class="pure-button pure-button-primary">Impeach Congressman</button>
                </div>
            </form>
        </td>
    </tr><?php
    } elseif ($_GET['page'] === 'playeritems') {
        $db->query('SELECT id, name, cost FROM items ORDER BY id ');
        $db->execute();
        $rows = $db->fetch(); ?><tr>
        <th class="content-head">List Of All Items</th>
    </tr>
    <tr>
        <td class="content"><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            ?><div><?php echo $row['id']; ?>.) <?php echo item_popup($row['id'], $row['name']); ?> - <?php echo prettynum($row['cost'], true); ?></div><?php
        }
    } else {
        ?>There are no items<?php
    } ?></td>
    </tr>
    <tr>
        <th class="content-head">Add New Item To Database</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=playeritems" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('item_add'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="name">Item Name</label>
                        <input type="text" name="name" id="name" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="description">Description</label>
                        <input type="text" name="description" id="description" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="cost">Cost</label>
                        <input type="text" name="cost" id="cost" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="image">Image URL</label>
                        <input type="text" name="image" id="image" size="10" maxlength="75" value="images/noimage.png" />
                    </div>
                    <div class="pure-control-group">
                        <label for="offense">Offense</label>
                        <input type="text" name="offense" id="offense" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="defense">Defense</label>
                        <input type="text" name="defense" id="defense" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="heal">Heal (percentage)</label>
                        <input type="text" name="heal" id="heal" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="reduce">Jail Reduction (minutes)</label>
                        <input type="text" name="reduce" id="reduce" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="buyable">Buyable</label>
                        <input type="checkbox" name="buyable" id="buyable" value="1" checked />
                    </div>
                    <div class="pure-control-group">
                        <label for="level">Level</label>
                        <input type="text" name="level" id="level" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="additemdb" class="pure-button pure-button-primary">Add Item</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Edit Item</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=playeritems" method="post" class="pure-form pure-form-aligned">
                <div class="pure-control-group">
                    <label for="id">Item</label>
                    <?php echo listItems('id'); ?>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="edititemstart" class="pure-button pure-button-priamry">Begin Item Edit</button>
                </div>
            </form>
        </td>
    </tr><?php
    if (array_key_exists('edititemstart', $_POST)) {
        if (empty($_POST['id'])) {
            echo Message('You didn\'t select a valid item', 'Error', true);
        }
        $db->query('SELECT * FROM items WHERE id = ?');
        $db->execute([$_POST['id']]);
        if (!$db->count()) {
            echo Message('The item you selected doesn\'t exist', 'Error', true);
        }
        $row = $db->fetch(true); ?><tr>
            <td class="content">
                <form action="control.php?page=playeritems" method="post" class="pure-form pure-form-aligned">
                    <?php echo csrf_create('item_edit'); ?>
                    <input tpye="hidden" name="id" value="<?php echo $row['id']; ?>" />
                    <fieldset>
                        <div class="pure-control-group">
                            <label for="name">Item Name</label>
                            <input type="text" name="name" id="name" size="10" maxlength="75" value="<?php echo format($row['name']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="description">Description</label>
                            <input type="text" name="description" id="description" size="10" maxlength="75" value="<?php echo format($row['description']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="cost">Cost</label>
                            <input type="text" name="cost" id="cost" size="10" maxlength="75" value="<?php echo format($row['cost']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="image">Image URL</label>
                            <input type="text" name="image" id="image" size="10" maxlength="75" value="images/noimage.png" value="<?php echo format($row['image']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="offense">Offense</label>
                            <input type="text" name="offense" id="offense" size="10" maxlength="75" value="<?php echo format($row['offense']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="defense">Defense</label>
                            <input type="text" name="defense" id="defense" size="10" maxlength="75" value="<?php echo format($row['defense']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="heal">Heal (percentage)</label>
                            <input type="text" name="heal" id="heal" size="10" maxlength="75" value="<?php echo format($row['heal']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="reduce">Jail Reduction (minutes)</label>
                            <input type="text" name="reduce" id="reduce" size="10" maxlength="75" value="<?php echo format($row['reduce']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="buyable">Buyable</label>
                            <input type="checkbox" name="buyable" id="buyable" value="1"<?php echo $row['buyable'] ? ' checked' : ''; ?> />
                        </div>
                        <div class="pure-control-group">
                            <label for="level">Level</label>
                            <input type="text" name="level" id="level" size="10" maxlength="75" value="<?php echo format($row['level']); ?>" />
                        </div>
                    </fieldset>
                    <div class="pure-controls">
                        <button type="submit" name="edititemdb" class="pure-button pure-button-primary">Edit Item</button>
                    </div>
                </form>
            </td>
        </tr><?php
    } ?><tr>
        <th class="content-head">Delete Item</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=playeritems" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('item_delete'); ?>
                <div class="pure-control-group">
                    <label for="id">Item</label>
                    <?php echo listItems('id'); ?>
                </div>
                <div class="pure-controls">
                    <button type="submit" name="deleteitemdb" class="pure-button pure-button-primary">Delete Item</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Give Item</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=playeritems" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('item_give'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="itemnumber">Item Number/ID</label>
                        <input type="text" name="itemnumber" id="itemnumber" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="itemquantity">Quantity</label>
                        <input type="text" name="itemquantity" id="itemquantity" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="giveitem" class="pure-button pure-button-primary">Give Items</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">Take Item</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=playeritems" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('item_take'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="itemnumber">Item Number/ID</label>
                        <input type="text" name="itemnumber" id="itemnumber" size="10" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="itemquantity">Quantity</label>
                        <input type="text" name="itemquantity" id="itemquantity" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="takeitem" class="pure-button pure-button-primary">Take Items</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">View A Players Items</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=playeritems" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('item_view'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="username">Username</label>
                        <input type="text" name="username" id="username" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="listitems" class="pure-button pure-button-primary">List Items</button>
                </div>
            </form>
        </td>
    </tr><?php
    } elseif ($_GET['page'] === 'referrals') {
        $db->query('SELECT id, referred, referrer, time_added FROM referrals WHERE credited = 0 ORDER BY time_added DESC');
        $db->execute();
        $rows = $db->fetch(); ?><tr>
        <th class="content-head">Manage Referrals</th>
    </tr>
    <tr>
        <td class="content"><?php
    if ($rows !== null) {
        $cache = [];
        foreach ($rows as $row) {
            $date = new DateTime($row['time_added']);
            if (!array_key_exists($row['referrer'], $cache)) {
                $ref = new User($row['referrer']);
                $cache[$row['referrer']] = $ref->formattedname;
            }
            if (!array_key_exists($row['referred'], $cache)) {
                $ref = new User($row['referred']);
                $cache[$row['referred']] = $ref->formattedname;
            } ?><div>
                    <?php echo $row['id']; ?>.)
                    <?php echo $cache[$row['referred']]; ?> was referred by <?php echo $cache[$row['referrer']]; ?>.
                    (<?php echo $date->format('F d, Y g:i:sa'); ?>)
                    <a href="control.php?page=referrals&amp;givecredit=<?php echo $row['id']; ?>&amp;csrfg=<?php echo csrf_create('referral_credit_'.$row['id'], false); ?>">Credit</a> |
                    <a href="control.php?page=referrals&amp;denycredit=<?php echo $row['id']; ?>&amp;csrfg=<?php echo csrf_create('referral_deny_'.$row['id'], false); ?>">Deny</a>
                </div><?php
        }
    } else {
        ?>There are no pending referrals<?php
    } ?></td>
    </tr><?php
    } elseif ($_GET['page'] === 'crimes') {
        $db->query('SELECT id, name, nerve FROM crimes ORDER BY nerve ');
        $db->execute();
        $rows = $db->fetch(); ?><tr>
        <th class="content-head">Crimes</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Nerve</th>
                        <th>Delete</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            ?><tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo format($row['name']); ?></td>
                        <td><?php echo format($row['nerve']); ?></td>
                        <td>[<a href="control.php?page=crimes&amp;deletecrime=<?php echo $row['id']; ?>&amp;csrfg=<?php echo csrf_create('delete_crime_'.$row['id'], false); ?>">Delete Crime</a>]</td>
                    </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="4" class="center">There are no crimes</td>
                </tr><?php
    } ?></table>
        </td>
    </tr>
    <tr>
        <th class="content-head">Add New Crime To Database</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=crimes" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('crime_add'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="name">Crime Name</label>
                        <input type="text" name="name" id="name" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="nerve">Nerve</label>
                        <input type="text" name="nerve" id="nerve" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="stext">Success message</label>
                        <textarea name="stext" id="stext" cols="53" rows="7" placeholder="Success message"></textarea>
                    </div>
                    <div class="pure-control-group">
                        <label for="ftext">Fail message</label>
                        <textarea name="ftext" id="ftext" cols="53" rows="7" placeholder="Fail message"></textarea>
                    </div>
                    <div class="pure-control-group">
                        <label for="ctext">Fail and caught message</label>
                        <textarea name="ctext" id="ctext" cols="53" rows="7" placeholder="Fail and caught message"></textarea>
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addcrimedb" class="pure-button pure-button-primary">Add Crime</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">View/Edit A Crime</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=crimes" method="post" class="pure-form pure-form-aligned">
                <fieldset>
                    <div class="pure-control-group">
                        <label for="crimeid">Crime ID</label>
                        <input type="text" name="crimeid" id="crimeid" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="vieweditcrime" class="pure-button pure-button-primary">View/Edit Crime</button>
                </div>
            </form>
        </td>
    </tr><?php
    if (array_key_exists('vieweditcrime', $_POST) && !empty($_POST['crimeid'])) {
        $db->query('SELECT * FROM crimes WHERE id = ?');
        $db->execute([$_POST['crimeid']]);
        if ($db->count()) {
            $row = $db->fetch(true); ?><tr>
                <th class="content-head">Edit Crime</th>
            </tr>
            <tr>
                <td class="content">
                    <form action="control.php?page=crimes" method="post" class="pure-form pure-form-aligned">
                        <?php echo csrf_create('crime_edit'); ?>
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                        <fieldset>
                            <div class="pure-control-group">
                                <label for="name">Crime Name</label>
                                <input type="text" name="name" id="name" size="30" maxlength="75" value="<?php echo format($row['name']); ?>" />
                            </div>
                            <div class="pure-control-group">
                                <label for="nerve">Nerve</label>
                                <input type="text" name="nerve" id="nerve" size="30" maxlength="75" value="<?php echo format($row['nerve']); ?>" />
                            </div>
                            <div class="pure-control-group">
                                <label for="stext">Success message</label>
                                <textarea name="stext" id="stext" cols="53" rows="7" placeholder="Success message"><?php echo format($row['stext']); ?></textarea>
                            </div>
                            <div class="pure-control-group">
                                <label for="ftext">Fail message</label>
                                <textarea name="ftext" id="ftext" cols="53" rows="7" placeholder="Fail message"><?php echo format($row['ftext']); ?></textarea>
                            </div>
                            <div class="pure-control-group">
                                <label for="ctext">Fail and caught message</label>
                                <textarea name="ctext" id="ctext" cols="53" rows="7" placeholder="Fail and caught message"><?php echo format($row['ctext']); ?></textarea>
                            </div>
                        </fieldset>
                        <div class="pure-controls">
                            <button type="submit" name="editcrimedb" class="pure-button pure-button-primary">Edit Crime</button>
                        </div>
                    </form>
                </td>
            </tr><?php
        }
    }
    } elseif ($_GET['page'] === 'cities') {
        $db->query('SELECT * FROM cities ORDER BY levelreq , id ');
        $db->execute();
        $rows = $db->fetch(); ?><tr>
        <th class="content-head">Cities</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Level Req</th>
                        <th>Land Left</th>
                        <th>Land Price</th>
                        <th>Delete</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            ?><tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo format($row['name']); ?></td>
                        <td><?php echo format($row['levelreq']); ?></td>
                        <td><?php echo format($row['landleft']); ?></td>
                        <td><?php echo prettynum($row['landprice'], true); ?></td>
                        <td>[<a href="control.php?page=cities&amp;deletecity=<?php echo $row['id']; ?>&amp;csrfg=<?php echo csrf_create('delete_city_'.$row['id'], false); ?>">Delete City</a>]</td>
                    </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="6" class="center">There are no cities</td>
                </tr><?php
    } ?></table>
        </td>
    </tr>
    <tr>
        <th class="content-head">Add New City To Database</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=cities" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('city_add'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="name">City Name</label>
                        <input type="text" name="name" id="name" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="levelreq">Level required</label>
                        <input type="text" name="levelreq" id="levelreq" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="landleft">Land Left</label>
                        <input type="text" name="landleft" id="landleft" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="landprice">Land Price</label>
                        <input type="text" name="landprice" id="landprice" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" cols="53" rows="7" placeholder="Description goes here..."></textarea>
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addcitydb" class="pure-button pure-button-primary">Add City</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">View/Edit A City</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=cities" method="post" class="pure-form pure-form-aligned">
                <fieldset>
                    <div class="pure-control-group">
                        <label for="cityid">City ID</label>
                        <input type="text" name="cityid" id="cityid" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="vieweditcity" class="pure-button pure-button-primary">View/Edit City</button>
                </div>
            </form>
        </td>
    </tr><?php
    if (array_key_exists('vieweditcity', $_POST) && !empty($_POST['cityid'])) {
        $db->query('SELECT * FROM cities WHERE id = ?');
        $db->execute([$_POST['cityid']]);
        if ($db->count()) {
            $row = $db->fetch(true); ?><tr>
                <th class="content-head">Edit City</th>
            </tr>
            <tr>
                <td class="content">
                    <form action="control.php?page=cities" method="post" class="pure-form pure-form-aligned">
                        <?php echo csrf_create('city_edit'); ?>
                        <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                        <fieldset>
                            <div class="pure-control-group">
                                <label for="name">City Name</label>
                                <input type="text" name="name" id="name" size="30" maxlength="75" value="<?php echo format($row['name']); ?>" />
                            </div>
                            <div class="pure-control-group">
                                <label for="levelreq">Level required</label>
                                <input type="text" name="levelreq" id="levelreq" size="30" maxlength="75" value="<?php echo format($row['levelreq']); ?>" />
                            </div>
                            <div class="pure-control-group">
                                <label for="landleft">Land Left</label>
                                <input type="text" name="landleft" id="landleft" size="30" maxlength="75" value="<?php echo format($row['landleft']); ?>" />
                            </div>
                            <div class="pure-control-group">
                                <label for="landprice">Land Price</label>
                                <input type="text" name="landprice" id="landprice" size="30" maxlength="75" value="<?php echo format($row['landprice']); ?>" />
                            </div>
                            <div class="pure-control-group">
                                <label for="description">Description</label>
                                <textarea name="description" id="description" cols="53" rows="7" placeholder="Description goes here..."><?php echo format($row['description']); ?></textarea>
                            </div>
                        </fieldset>
                        <div class="pure-controls">
                            <button type="submit" name="editcitydb" class="pure-button pure-button-primary">Edit City</button>
                        </div>
                    </form>
                </td>
            </tr><?php
        }
    }
    } elseif ($_GET['page'] === 'jobs') {
        $db->query('SELECT * FROM jobs ORDER BY level , id ');
        $db->execute();
        $rows = $db->fetch(); ?><tr>
        <th class="content-head">Jobs</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Money</th>
                        <th>Strength</th>
                        <th>Defense</th>
                        <th>Speed</th>
                        <th>Level</th>
                        <th>Delete</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            ?><tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo format($row['name']); ?></td>
                        <td><?php echo prettynum($row['money'], true); ?></td>
                        <td><?php echo format($row['strength']); ?></td>
                        <td><?php echo format($row['defense']); ?></td>
                        <td><?php echo format($row['speed']); ?></td>
                        <td><?php echo format($row['level']); ?></td>
                        <td>[<a href="control.php?page=jobs&amp;deletejob=<?php echo $row['id']; ?>&amp;csrfg=<?php echo csrf_create('delete_job_'.$row['id'], false); ?>">Delete Job</a>]</td>
                    </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="8" class="center">There are no jobs</td>
                </tr><?php
    } ?></table>
        </td>
    </tr>
    <tr>
        <th class="content-head">Add New Job To Database</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=jobs" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('job_add'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" id="name" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="money">Money</label>
                        <input type="text" name="money" id="money" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="strength">Strength</label>
                        <input type="text" name="strength" id="strength" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="defense">Defense</label>
                        <input type="text" name="defense" id="defense" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="speed">Speed</label>
                        <input type="text" name="speed" id="speed" size="30" maxlength="75" />
                    </div>
                    <div class="pure-control-group">
                        <label for="level">Minimum Level</label>
                        <input type="text" name="level" id="level" size="30" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addjobdb" class="pure-butotn pure-button-primary">Add Job</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">View/Edit A Job</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=jobs" method="post" class="pure-form pure-form-aligned">
                <fieldset>
                    <div class="pure-control-group">
                        <label for="jobid">Job ID</label>
                        <input type="text" name="jobid" id="jobid" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="vieweditjob" class="pure-button pure-button-primary">View/Edit Job</button>
                </div>
            </form>
        </td>
    </tr><?php
    if (array_key_exists('vieweditjob', $_POST) && !empty($_POST['jobid'])) {
        $db->query('SELECT * FROM jobs WHERE id = ?');
        $db->execute([$_POST['jobid']]);
        if ($db->count()) {
            $row = $db->fetch(true);
        } ?>
        <tr>
            <th class="content-head">Edit Job</th>
        </tr>
        <tr>
            <td class="content">
                <form action="control.php?page=jobs" method="post" class="pure-form pure-form-aligned">
                    <?php echo csrf_create('job_edit'); ?>
                    <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
                    <fieldset>
                        <div class="pure-control-group">
                            <label for="name">Name</label>
                            <input type="text" name="name" id="name" size="30" maxlength="75" value="<?php echo format($row['name']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="money">Money</label>
                            <input type="text" name="money" id="money" size="30" maxlength="75" value="<?php echo format($row['money']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="strength">Strength</label>
                            <input type="text" name="strength" id="strength" size="30" maxlength="75" value="<?php echo format($row['strength']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="defense">Defense</label>
                            <input type="text" name="defense" id="defense" size="30" maxlength="75" value="<?php echo format($row['defense']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="speed">Speed</label>
                            <input type="text" name="speed" id="speed" size="30" maxlength="75" value="<?php echo format($row['speed']); ?>" />
                        </div>
                        <div class="pure-control-group">
                            <label for="level">Minimum Level</label>
                            <input type="text" name="level" id="level" size="30" maxlength="75" value="<?php echo format($row['level']); ?>" />
                        </div>
                    </fieldset>
                    <div class="pure-controls">
                        <button type="submit" name="editjobdb" class="pure-butotn pure-button-primary">Edit Job</button>
                    </div>
                </form>
            </td>
        </tr><?php
    }
    } elseif ($_GET['page'] === 'voting') {
        $db->query('SELECT * FROM voting_sites ORDER BY id ');
        $db->execute();
        $rows = $db->fetch(); ?><tr>
        <th class="content-head">List of Voting Sites</th>
    </tr>
    <tr>
        <td class="content">
            <table class="table" width="100%">
                <thead>
                    <tr>
                        <th width="20%">ID</th>
                        <th width="20%">Site</th>
                        <th width="30%">Rewards</th>
                        <th width="30%">Requirements</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            $rewards = [];
            if ($row['reward_cash']) {
                $rewards[] = prettynum($row['reward_cash'], true);
            }
            if ($row['reward_points']) {
                $rewards[] = format($row['reward_points']).' point'.s($row['reward_points']);
            }
            if ($row['reward_items']) {
                $items = explode(',', $row['reward_items']);
                foreach ($items as $item) {
                    [$itemID, $qty] = explode(':', $item);
                    $rewards[] = format($qty).'x '.item_popup($itemID);
                }
            }
            if ($row['reward_rmdays']) {
                $rewards[] = 'RM Days: '.time_format($row['reward_rmdays'] * 86400, 'long', false);
            }
            //----------------------
            $reqs = [];
            if ($row['req_account_days_min']) {
                $reqs[] = 'Account age: at least '.time_format($row['req_account_days_min'] * 86400, 'long', false).' old';
            }
            if ($row['req_account_days_max']) {
                $reqs[] = 'Account age: at most '.time_format($row['req_account_days_max'] * 86400, 'long', false).' old';
            }
            if ($row['req_rmdays']) {
                $reqs[] = 'RM Days: at least '.time_format($row['req_rmdays'] * 86400, 'long', false).' left';
            } ?><tr>
                        <td><?php echo format($row['id']); ?> [<a href="control.php?page=voting&amp;deletevotesite&amp;id=<?php echo $row['id']; ?>&amp;csrfg=<?php echo csrf_create('delete_votesite_'.$row['id'], false); ?>">X</a>]</td>
                        <td><a href="<?php echo format($row['url']); ?>" target="new" class="pure-button pure-button-yellow"><i class="fa fa-eye" aria-hidden="true"></i> <?php echo format($row['title']); ?></a></td>
                        <td><?php echo count($rewards) ? implode('<br />', $rewards) : 'None'; ?></td>
                        <td><?php echo count($reqs) ? implode('<br />', $reqs) : 'None'; ?></td>
                    </tr><?php
        }
    } else {
        echo '<tr><td colspan="4" class="centre">There are no voting sites</td></tr>';
    } ?></table>
        </td>
    </tr>
    <tr>
        <th class="content-head">Add Voting Site</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=voting" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('votesite_add'); ?>
                <fieldset>
                    <legend>Basic Information</legend>
                    <div class="pure-control-group">
                        <label for="title">Title</label>
                        <input type="text" name="title" id="title" class="pure-u-1-2 pure-u-md-1-2" required autofocus />
                    </div>
                    <div class="pure-control-group">
                        <label for="url">URL</label>
                        <input type="url" name="url" id="url" class="pure-u-1-2 pure-u-md-1-2" required />
                    </div>
                    <legend>Requirements</legend>
                    <div class="pure-control-group">
                        <label for="req_account_days_min">Minimum Account Age (Days)</label>
                        <input type="text" name="req_account_days_min" id="req_account_days_min" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-control-group">
                        <label for="req_account_days_max">Maximum Account Age (Days)</label>
                        <input type="text" name="req_account_days_max" id="req_account_days_max" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-control-group">
                        <label for="req_rmdays">RM Days</label>
                        <input type="text" name="req_rmdays" id="req_rmdays" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-control-group">
                        <label for="days_between_vote">Days Between Votes</label>
                        <input type="text" name="days_between_vote" id="days_between_vote" placeholder="1" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <legend>Rewards</legend>
                    <div class="pure-control-group">
                        <label for="reward_cash">Cash</label>
                        <input type="text" name="reward_cash" id="reward_cash" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-control-group">
                        <label for="reward_points">Points</label>
                        <input type="text" name="reward_points" id="reward_points" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-control-group">
                        <label for="reward_rmdays">RM Days</label>
                        <input type="text" name="reward_rmdays" id="reward_rmdays" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-control-group">
                        <label for="reward_items">Items<span class="yellow">*</span></label>
                        <input type="text" name="reward_items" id="reward_items" placeholder="0:0,0:0" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-controls">
                        <button type="submit" name="addvotesite" class="pure-button pure-button-primary">Add Voting Site</button>
                    </div>
                </fieldset>
            </form>
            <p>
                <span class="yellow">*</span> This can be a little awkward at first. Don't worry, I'll be replacing this with a much better system shortly..<br />
                For now, however..<br />
                The format is this: <strong>Item ID:Quantity</strong><br />
                For example, I want to give 23 of item ID 4, so I'd write 4:23.<br />
                Now, here's where it can get a little tricky.. If you want to add multiple items, the format is: <strong>Item ID:Quantity,Item ID:Quantity,Item ID:Quantity</strong><br />
                Same as above, just seperated by commas..
            </p>
        </td>
    </tr>
    <tr>
        <th class="content-head">View/Edit A Voting Site</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=voting" method="post" class="pure-form pure-form-aligned">
                <fieldset>
                    <div class="pure-control-group">
                        <label for="id">Vote Site ID</label>
                        <input type="text" name="id" id="id" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="vieweditvotesite" class="pure-button pure-button-primary">View/Edit Voting Site</button>
                </div>
            </form>
        </td>
    </tr><?php
    if (array_key_exists('vieweditvotesite', $_POST)) {
        if (empty($_POST['id'])) {
            echo Message('You didn\'t select a valid site', 'Error', true);
        }
        $db->query('SELECT * FROM voting_sites WHERE id = ?');
        $db->execute([$_POST['id']]);
        if (!$db->count()) {
            echo Message('That voting site doesn\'t exist', 'Error', true);
        }
        $row = $db->fetch(true); ?><form action="control.php?page=voting" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create('votesite_edit'); ?>
            <input type="hidden" name="id" value="<?php echo $row['id']; ?>" />
            <fieldset>
                <legend>Basic Information</legend>
                <div class="pure-control-group">
                    <label for="title">Title</label>
                    <input type="text" name="title" id="title" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['title']); ?>" required autofocus />
                </div>
                <div class="pure-control-group">
                    <label for="url">URL</label>
                    <input type="url" name="url" id="url" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['url']); ?>" required  />
                </div>
                <legend>Requirements</legend>
                <div class="pure-control-group">
                    <label for="req_account_days_min">Minimum Account Age (Days)</label>
                    <input type="text" name="req_account_days_min" id="req_account_days_min" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['req_account_days_min']); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="req_account_days_max">Maximum Account Age (Days)</label>
                    <input type="text" name="req_account_days_max" id="req_account_days_max" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['req_account_days_max']); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="req_rmdays">RM Days</label>
                    <input type="text" name="req_rmdays" id="req_rmdays" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['req_rmdays']); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="days_between_vote">Days Between Votes</label>
                    <input type="text" name="days_between_vote" id="days_between_vote" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['days_between_vote']); ?>" />
                </div>
                <legend>Rewards</legend>
                <div class="pure-control-group">
                    <label for="reward_cash">Cash</label>
                    <input type="text" name="reward_cash" id="reward_cash" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['reward_cash']); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="reward_points">points</label>
                    <input type="text" name="reward_points" id="reward_points" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['reward_points']); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="reward_rmdays">RM Days</label>
                    <input type="text" name="reward_rmdays" id="reward_rmdays" placeholder="0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['reward_rmdays']); ?>" />
                </div>
                <div class="pure-control-group">
                    <label for="reward_items">Items<span class="yellow">*</span></label>
                    <input type="text" name="reward_items" id="reward_items" placeholder="0:0,0:0" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['reward_items']); ?>" />
                </div>
                <div class="pure-controls">
                    <button type="submit" name="editvote" class="pure-button pure-button-primary">Edit Voting Site</button>
                    <button type="reset" class="pure-button pure-button-primary">Reset</button>
                </div>
            </fieldset>
        </form><?php
    }
    } elseif ($_GET['page'] === 'forum') {
        $db->query('SELECT fb_id, fb_name FROM forum_boards ORDER BY fb_id ');
        $db->execute();
        $rows = $db->fetch(); ?><tr>
        <th class="content-head">List of Forum Boards</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <thead>
                    <tr>
                        <th width="5%">ID</th>
                        <th width="90%">Board</th>
                        <th width="5%">Delete</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            ?><tr>
                <td><?php echo $row['fb_id']; ?></td>
                <td><?php echo format($row['fb_name']); ?></td>
                <td>[<a href="control.php?page=forum&amp;deleteforumdb=<?php echo $row['fb_id']; ?>&amp;csrfg=<?php echo csrf_create('delete_board_'.$row['fb_id'], false); ?>">Delete Board</a>]</td>
            </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="3" class="center">There are no boards</td>
                </tr><?php
    } ?></table>
        </td>
    </tr>
    <tr>
        <th class="content-head">Add Board</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=forum" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create('board_add'); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="name">Board Name</label>
                        <input type="text" name="name" class="pure-u-1-2 pure-u-md-1-2" autofocus required />
                    </div>
                    <div class="pure-control-group">
                        <label for="desc">Description (optional)</label>
                        <input type="text" name="desc" class="pure-u-1-2 pure-u-md-1-2" />
                    </div>
                    <div class="pure-control-group">
                        <label for="auth" class="pure-radio">Viewer Authorisation</label><?php
    foreach ($auths as $val) {
        printf('<input type="radio" name="auth" value="%s"%s>%s<br />', $val, $val === 'public' ? ' checked="checked"' : '', ucfirst($val));
    } ?></div>
                    <div class="pure-control-group">
                        <label for="bin" class="pure-radio">Recycle Bin</label>
                        <input type="checkbox" name="bin" value="1" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="addforumdb" class="pure-button pure-button-primary">Add Board</button>
                </div>
            </form>
        </td>
    </tr>
    <tr>
        <th class="content-head">View/Edit A Board</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=forum" method="post" class="pure-form pure-form-aligned">
                <fieldset>
                    <div class="pure-control-group">
                        <label for="board">Board ID</label>
                        <input type="text" name="board" id="board" size="10" maxlength="75" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="vieweditboard" class="pure-button pure-button-primary">View/Edit Board</button>
                </div>
            </form>
        </td>
    </tr><?php
    if (array_key_exists('vieweditboard', $_POST)) {
        $_POST['board'] = array_key_exists('board', $_POST) && ctype_digit($_POST['board']) ? $_POST['board'] : null;
        if (empty($_POST['board'])) {
            echo Message('You didn\'t select a valid board', 'Error', true);
        }
        $db->query('SELECT fb_name, fb_desc, fb_auth, fb_bin FROM forum_boards WHERE fb_id = ?');
        $db->execute([$_POST['board']]);
        if (!$db->count()) {
            echo Message('The board you selected doesn\'t exist', 'Error', true);
        }
        $row = $db->fetch(true); ?>
        <tr>
            <th class="content-head">Edit Forum</th>
        </tr>
        <tr>
            <td class="content">
                <form action="control.php?page=forum" method="post" class="pure-form pure-form-aligned">
                    <?php echo csrf_create('board_edit'); ?>
                    <input type="hidden" name="id" value="<?php echo $_POST['board']; ?>" />
                    <div class="pure-control-group">
                        <label for="name">Name</label>
                        <input type="text" name="name" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['fb_name']); ?>" required autofocus />
                    </div>
                    <div class="pure-control-group">
                        <label for="desc">Description (optional)</label>
                        <input type="text" name="desc" class="pure-u-1-2 pure-u-md-1-2" value="<?php echo format($row['fb_desc']); ?>" />
                    </div>
                    <div class="pure-control-group">
                        <label for="auth" class="pure-radio">Viewer Authorisation</label><?php
        foreach ($auths as $val) {
            printf('<input type="radio" name="auth" value="%s"%s>%s<br />', $val, $val == $row['fb_auth'] ? ' checked="checked"' : '', ucfirst($val));
        } ?></div>
                    <div class="pure-control-group">
                        <label for="bin" class="pure-radio">Recycle Bin</label>
                        <input type="checkbox" name="bin" value="1" <?php echo $row['fb_bin'] ? ' checked="checked"' : ''; ?> />
                    </div>
                    <div class="pure-controls">
                        <button type="submit" name="editforumdb" class="pure-button pure-button-primary">Process Edits</button>
                    </div>
                </form>
            </td>
        </tr><?php
    }
    } elseif ($_GET['page'] === 'houses') {
        $db->query('SELECT * FROM houses ORDER BY awake ');
        $db->execute();
        $rows = $db->fetch(); ?>
    <tr>
        <th class="content-head">Houses</th>
    </tr>
    <tr>
        <td class="content">
            <table class="pure-table pure-table-horizontal" width="100%">
                <thead>
                    <tr>
                        <th>House</th>
                        <th>Awake</th>
                        <th>Cost</th>
                        <th>Purchaseable</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php
                if ($rows !== null) {
                    foreach ($rows as $row) {
                        $buyable = $row['buyable'] ? 'accept' : 'delete';
                        $title = $row['buyable'] ? 'Yes' : 'No'; ?>
                        <tr>
                            <td><?php echo format($row['name']); ?></td>
                            <td><?php echo format($row['awake']); ?></td>
                            <td><?php echo prettynum($row['cost'], true); ?></td>
                            <td><img src="/images/silk/<?php echo $buyable; ?>.png" title="<?php echo $title; ?>" alt="<?php echo $title; ?>" /></td>
                            <td>
                                [<a href="control.php?page=houses&amp;edithouse&amp;id=<?php echo $row['id']; ?>">Edit</a>] &middot;
                                [<a href="control.php?page=houses&amp;deletehouse&amp;id=<?php echo $row['id']; ?>">Delete</a>]
                            </td>
                        </tr><?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5" class="center">There are no houses</td>
                    </tr><?php
                } ?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td class="content-spacer">&nbsp;</td>
    </tr>
    <tr>
        <th class="content-head">Add House</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=houses&amp;addhouse" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create(); ?>
                <div class="pure-control-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="pure-u-1-2 pure-u-md-1-2" required autofocus />
                </div>
                <div class="pure-control-group">
                    <label for="awake">Awake</label>
                    <input type="text" name="awake" id="awake" class="pure-u-1-2 pure-u-md-1-2" required />
                </div>
                <div class="pure-control-group">
                    <label for="cost">Cost</label>
                    <input type="text" name="cost" id="cost" class="pure-u-1-2 pure-u-md-1-2" required />
                </div>
                <div class="pure-control-group">
                    <label for="buyable" class="pure-radio">
                        <input type="checkbox" name="buyable" id="buyable" value="1" checked>
                    </label>
                    Buyable
                </div>
                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary">Add House</button>
                </div>
            </form>
        </td>
    </tr><?php
    } elseif ($_GET['page'] === 'site_settings') {
        $registration = settings('registration'); ?>
    <tr>
        <th class="content-head">Site Settings</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=site_settings&amp;settings" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create(); ?>
                <div class="pure-control-group">
                    <label for="registration">Registration</label>
                    <select name="registration" id="registration">
                        <option value="open"<?php echo $registration === 'open' ? ' selected' : ''; ?>>Open</option>
                        <option value="closed"<?php echo $registration === 'closed' ? ' selected' : ''; ?>>Closed</option>
                    </select>
                </div>
                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary">Update Settings</button>
                </div>
            </form>
        </td>
    </tr><?php
    } elseif ($_GET['page'] === 'cars') {
        $db->query('SELECT * FROM carlot ORDER BY level , cost ');
        $db->execute();
        $rows = $db->fetch(); ?>
    <tr>
        <th class="content-head">Cars</th>
    </tr>
    <tr>
        <td class="content">
            <table class="pure-table pure-table-horizontal" width="100%">
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Level</th>
                        <th>Cost</th>
                        <th>Purchaseable</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody><?php
                if ($rows !== null) {
                    foreach ($rows as $row) {
                        $buyable = $row['buyable'] ? 'accept' : 'delete';
                        $title = $row['buyable'] ? 'Yes' : 'No'; ?>
                        <tr>
                            <td><?php
                                if ($row['image']) {
                                    ?>
                                    <img src="<?php echo format($row['image']); ?>" title="image" alt="image" style="width:150px;height:75px;" /><br /><?php
                                }
                        echo format($row['name']); ?>
                            </td>
                            <td><?php echo format($row['level']); ?></td>
                            <td><?php echo format($row['cost']); ?></td>
                            <td><img src="/images/silk/<?php echo $buyable; ?>.png" title="<?php echo $title; ?>" alt="<?php echo $title; ?>" /></td>
                            <td>
                                [<a href="control.php?page=cars&amp;editcar&amp;id=<?php echo $row['id']; ?>">Edit</a>] &middot;
                                [<a href="control.php?page=cars&amp;deletecar&amp;id=<?php echo $row['id']; ?>">Delete</a>]
                            </td>
                        </tr><?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5" class="center">There are no cars</td>
                    </tr><?php
                } ?>
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td class="content-spacer">&nbsp;</td>
    </tr>
    <tr>
        <th class="content-head">Add Car</th>
    </tr>
    <tr>
        <td class="content">
            <form action="control.php?page=cars&amp;addcar" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create(); ?>
                <div class="pure-control-group">
                    <label for="name">Name</label>
                    <input type="text" name="name" id="name" class="pure-u-1-2 pure-u-md-1-2" required autofocus />
                </div>
                <div class="pure-control-group">
                    <label for="level">Level</label>
                    <input type="text" name="level" id="level" class="pure-u-1-2 pure-u-md-1-2" required />
                </div>
                <div class="pure-control-group">
                    <label for="cost">Cost</label>
                    <input type="text" name="cost" id="cost" class="pure-u-1-2 pure-u-md-1-2" required />
                </div>
                <div class="pure-control-group">
                    <label for="image">Image</label>
                    <input type="text" name="image" id="image" class="pure-u-1-2 pure-u-md-1-2" required />
                </div>
                <div class="pure-control-group">
                    <label for="description">Description</label>
                    <textarea name="description" id="description" class="pure-u-1-2 pure-u-md-1-2" required></textarea>
                </div>
                <div class="pure-control-group">
                    <label for="buyable" class="pure-radio">
                        <input type="checkbox" name="buyable" id="buyable" value="1" checked>
                    </label>
                    Buyable
                </div>
                <div class="pure-controls">
                    <button type="submit" class="pure-button pure-button-primary">Add Car</button>
                </div>
            </form>
        </td>
    </tr><?php
    } elseif ($_GET['page'] === 'giveuseritem') {
        ?>
        <tr><th class="content-head">Give item to user</th></tr> <?php
        if (count($errors)) {
            display_errors($errors);
        } ?>
        <tr><td class="content"> <?php
            if (array_key_exists('who', $_GET)) {
                if (!csrf_check('csrf', $_GET)) {
                    echo Message(SECURITY_TIMEOUT_MESSAGE);
                }
                $_GET['user'] = array_key_exists('user', $_GET) && ctype_digit($_GET['user']) ? abs((int) $_GET['user']) : 0;
                if (empty($_GET['user'])) {
                    $errors[] = 'Invalid input.';
                }
                $db->query('SELECT id FROM users WHERE id = ?');
                $db->execute([$_GET['user']]);
                if (!$db->count()) {
                    $errors[] = 'Invalid user!';
                }
                $user = $db->fetch($_GET['user']); ?>
                <form method="POST">
                    <?php echo csrf_create('updateuser'); ?>
                    <table width='98%' cellspacing='1' style='text-align:center;'>
                        <tr>
                            <td>Level</td>
                            <td><input type='number' name='level' min='1' /></td>
                        </tr>
                        <tr>
                            <td>

            }
            else { ?>
                <form method="GET" class="pure-form pure-form-aligned">
                    <?php echo csrf_create(); ?>
                    <table width='98%' cellspacing='1' style='text-align:center;'>

                    <?php listMobsters(); ?>
                    <button name="who" type="submit">Edit User</button>
                </form> <?php
            } ?>
        </td></tr> <?php
    }
function listRMPacks($showEnabled = false, $formID = 'id')
{
    global $db;
    $db->query('SELECT id, name, cost, enabled FROM rmstore_packs ORDER BY cost , name ');
    $db->execute();
    $rows = $db->fetch();
    if ($rows !== null) {
        ?><select name="id" id="<?php echo $formID; ?>">
            <option value="0">--- NONE ---</option><?php
        foreach ($rows as $row) {
            printf('<option value="%u">%s - %s%s</option>', $row['id'], format($row['name']), formatCurrency($row['cost']), $showEnabled ? ' ('.($row['enabled'] ? 'Enabled' : 'Disabled').')' : '');
        } ?></select><?php
    } else {
        echo 'No upgrades available';
    }
}
