<?php
declare(strict_types=1);
global $parser;
require_once __DIR__.'/inc/header.php';
require_once __DIR__.'/inc/jbbcode/Parser.php';
require_once __DIR__.'/inc/page.class.php';
$_GET['act'] = array_key_exists('act', $_GET) && ctype_alpha($_GET['act']) ? strtolower(trim($_GET['act'])) : '';
$_GET['viewtopic'] = array_key_exists('viewtopic', $_GET) && ctype_digit($_GET['viewtopic']) ? $_GET['viewtopic'] : null;
$_GET['viewforum'] = array_key_exists('viewforum', $_GET) && ctype_digit($_GET['viewforum']) ? $_GET['viewforum'] : null;
$_GET['reply'] = array_key_exists('reply', $_GET) && ctype_digit($_GET['reply']) ? $_GET['reply'] : null;
if (!empty($_GET['viewtopic']) && $_GET['act'] !== 'quote') {
    $_GET['act'] = 'viewtopic';
}
if (!empty($_GET['viewforum'])) {
    $_GET['act'] = 'viewforum';
}
if (!empty($_GET['reply'])) {
    $_GET['act'] = 'reply';
}
$db->query('SELECT days FROM bans WHERE type = \'forum\' AND id = ?');
$db->execute([$user_class->id]);
if ($db->count()) {
    echo Message('You\'ve been banned from the forum. Ban time remaining: '.time_format($db->result() * 86400), 'Error', true);
}
if (array_key_exists('success', $_SESSION)) {
    echo Message($_SESSION['success'], 'Success');
    unset($_SESSION['success']);
}
?><tr>
    <th class="content-head">Forum</th>
</tr>
<tr>
    <td class="content">
        <?php echo $_GET['act'] !== 'managesub' ? '<p><a href="forum.php?act=managesub" class="pure-button pure-button-grey">Manage Subscriptions</a></p>' : '<p><a href="forum.php" class="pure-button pure-button-grey">Back to Forum</a></p>'; ?>
    </td>
</tr><?php
if ($_GET['act'] !== 'viewtopic') {
    ?><tr>
        <td class="content"><?php
}
$_GET['forum'] = array_key_exists('forum', $_GET) && ctype_digit($_GET['forum']) ? $_GET['forum'] : null;
$_GET['topic'] = array_key_exists('topic', $_GET) && ctype_digit($_GET['topic']) ? $_GET['topic'] : null;
$_GET['post'] = array_key_exists('post', $_GET) && ctype_digit($_GET['post']) ? $_GET['post'] : null;
switch ($_GET['act']) {
    case 'viewforum':
        viewforum($db, $user_class, $parser);
    break;
    case 'viewtopic':
        viewtopic($db, $user_class, $parser);
    break;
    case 'reply':
        reply($db, $user_class, $parser);
    break;
    case 'newtopic':
        newtopic($db, $user_class, $parser);
    break;
    case 'quote':
        quote($db, $user_class, $parser);
    break;
    case 'edit':
        edit($db, $user_class, $parser);
    break;
    case 'move':
        move($db, $user_class, $parser);
    break;
    case 'lock':
        lock($db, $user_class, $parser);
    break;
    case 'delepost':
        delepost($db, $user_class, $parser);
    break;
    case 'deletopic':
        deletopic($db, $user_class, $parser);
    break;
    case 'pin':
        pin($db, $user_class, $parser);
    break;
    case 'sub':
        subscribe($db, $user_class, $parser);
    break;
    case 'managesub':
        manage_subscriptions($db, $user_class, $parser);
    break;
        // case 'recache':
        //     if(!empty($_GET['forum']))
        //         recache_forum($_GET['forum']);
        //     break;

    default:
        index($db, $user_class, $parser);
    break;
}
function index($db, $user_class, $parser)
{
    $db->query('SELECT * FROM forum_boards WHERE fb_auth = \'public\' ORDER BY fb_id ');
    $db->execute();
    $rows = $db->fetch(); ?><table class="pure-table pure-table-horizontal center" width="100%">
        <thead>
            <tr>
                <th width="40%">Forum</th>
                <th width="10%">Posts</th>
                <th width="10%">Topics</th>
                <th width="40%">Last Post</th>
            </tr>
        </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            ?><tr>
                <td><a href="forum.php?viewforum=<?php echo $row['fb_id']; ?>" class="bold"><?php echo format($row['fb_name']); ?></a><br /><span class="small"><?php echo format($row['fb_desc']); ?></span></td>
                <td><?php echo format($row['fb_posts']); ?></td>
                <td><?php echo format($row['fb_topics']); ?></td>
                <td><?php
            if ($row['fb_latest_topic']) {
                $poster = $row['fb_latest_poster'] ? new User($row['fb_latest_poster']) : (object) ['formattedname' => 'None'];
                $db->query('SELECT ft_name FROM forum_topics WHERE ft_id = ?');
                $db->execute([$row['fb_latest_topic']]);
                $date = new DateTime($row['fb_latest_time']);
                echo $date->format('F d, Y g:i:sa'); ?><br />
                        In: <a href="forum.php?viewtopic=<?php echo $row['fb_latest_topic']; ?>&amp;latest"><?php echo format($db->result()); ?></a><br />
                        By: <?php echo $poster->formattedname;
            } else {
                echo 'No posts';
            } ?></td>
            </tr><?php
        }
    } else {
        ?><tr>
            <td colspan="4" class="center">There are no boards in this category</td>
        </tr><?php
    } ?></table><?php
    if ($user_class->admin > 0) {
        $db->query('SELECT * FROM forum_boards WHERE fb_auth = \'staff\' ORDER BY fb_id ');
        $db->execute();
        $rows = $db->fetch(); ?><h2 class="center">Staff Boards</h2>
        <table class="pure-table pure-table-horizontal center" width="100%">
            <thead>
                <tr>
                    <th width="40%">Forum</th>
                    <th width="10%">Posts</th>
                    <th width="10%">Topics</th>
                    <th width="40%">Last Post</th>
                </tr>
            </thead><?php
        if ($rows !== null) {
            foreach ($rows as $row) {
                ?><tr>
                    <td><a href="forum.php?viewforum=<?php echo $row['fb_id']; ?>" class="bold"><?php echo format($row['fb_name']); ?></a><br /><span class="small"><?php echo format($row['fb_desc']); ?></span></td>
                    <td><?php echo format($row['fb_posts']); ?></td>
                    <td><?php echo format($row['fb_topics']); ?></td>
                    <td><?php
                if ($row['fb_latest_topic']) {
                    $poster = $row['fb_latest_poster'] ? new User($row['fb_latest_poster']) : (object) ['formattedname' => 'None'];
                    $db->query('SELECT ft_name FROM forum_topics WHERE ft_id = ?');
                    $db->execute([$row['fb_latest_topic']]);
                    $date = new DateTime($row['fb_latest_time']);
                    echo $date->format('F d, Y g:i:sa'); ?><br />
                            In: <a href="forum.php?viewtopic=<?php echo $row['fb_latest_topic']; ?>&amp;latest"><?php echo format($db->result()); ?></a><br />
                            By: <?php echo $poster->formattedname;
                } else {
                    echo 'No posts';
                } ?></td>
                </tr><?php
            }
        } else {
            ?><tr>
            <td colspan="4" class="center">There are no boards in this category</td>
        </tr><?php
        } ?></table><?php
    }
}
function viewforum($db, $user_class, $parser)
{
    if (empty($_GET['viewforum'])) {
        echo Message('You didn\'t select a valid board', 'Error', true);
    }
    $db->query('SELECT fb_name, fb_auth, fb_owner FROM forum_boards WHERE fb_id = ?');
    $db->execute([$_GET['viewforum']]);
    if (!$db->count()) {
        echo Message('That board doesn\'t exist', 'Error', true);
    }
    $board = $db->fetch(true); ?><div class="big">
        <a href="forum.php">Index</a> &rarr;
        <a href="forum.php?viewforum=<?php echo $_GET['viewforum']; ?>"><?php echo format($board['fb_name']); ?></a><?php echo $_GET['viewforum'] != 1 || $user_class->admin == 1 ? '<br /><a href="forum.php?act=newtopic&amp;forum='.$_GET['viewforum'].'" class="pure-button">Create New Topic</a>' : ''; ?>
    </div><br /><?php
    accessCheck($board, $user_class);
    $db->query('SELECT COUNT(ft_id) FROM forum_topics WHERE ft_board = ?');
    $db->execute([$_GET['viewforum']]);
    $cnt = $db->result();
    if (!$cnt) {
        echo Message('There are no topics', 'Error', true);
    }
    $pages = new Paginator($cnt);
    $db->query('SELECT ft_id, ft_name, ft_creation_time, ft_creation_user, ft_latest_time, ft_latest_user, ft_latest_post, ft_locked, ft_pinned, id AS subbed
        FROM forum_topics
        LEFT JOIN forum_subscriptions ON topic = ft_id
        WHERE ft_board = ?
        ORDER BY ft_pinned DESC, ft_latest_time DESC
        LIMIT '.$pages->limit_start.', '.$pages->limit_end);
    $db->execute([$_GET['viewforum']]);
    $topics = $db->fetch();
    echo $pages->display_pages(); ?>
    <table class="pure-table pure-table-horizontal center" width="100%">
        <thead>
            <tr>
                <th width="40%">Topic</th>
                <th width="10%">Posts</th>
                <th width="25%">Started</th>
                <th width="25%">Latest Post</th>
            </tr>
        </thead><?php
    if ($topics !== null) {
        foreach ($topics as $topic) {
            $date_created = new DateTime($topic['ft_creation_time']);
            $date_latest = new DateTime($topic['ft_latest_time']);
            $creator = $topic['ft_creation_user'] ? new User($topic['ft_creation_user']) : (object) ['formattedname' => 'None']; ?><tr>
                <td><?php
            echo $topic['ft_pinned'] ? '<img src="/images/silk/exclamation.png" title="Pinned" alt="Pinned" /> ' : '';
            echo $topic['ft_locked'] ? '<img src="/images/silk/lock.png" title="Locked" alt="Locked" /> ' : ''; ?><a href="forum.php?viewtopic=<?php echo $topic['ft_id']; ?>"><?php echo format($topic['ft_name']); ?></a>
                    <?php echo isset($topic['subbed']) ? ' <img src="/images/silk/eye.png" title="Subscribed" alt="[Subscribed]" />' : ''; ?>
                </td>
                <td><?php echo getCount($topic['ft_id'], 'posts_topics'); ?></td>
                <td>
                    <?php echo $creator->formattedname; ?><br />
                    <span class="small"><?php echo $date_created->format('F d, Y g:i:sa'); ?></span>
                </td>
                <td><?php
            if ($topic['ft_latest_user']) {
                $poster = $topic['ft_latest_user'] ? new User($topic['ft_latest_user']) : (object) ['formattedname' => 'None'];
                echo $poster->formattedname; ?><br />
                        <span class="small"><?php echo $date_latest->format('F d, Y g:i:sa'); ?></span><br />
                        <a href="forum.php?viewtopic=<?php echo $topic['ft_id']; ?>&amp;latest"><img src="/images/silk/arrow_right.png" title="Go to latest post" alt="Go to latest post" /></a><?php
            } else {
                echo 'No responses yet';
            } ?></td>
            </tr><?php
        }
    } else {
        ?><tr>
            <td colspan="4" class="center">There are no topics</td>
        </tr><?php
    } ?></table>
    <?php echo $pages->display_pages();
}
function viewtopic($db, $user_class, $parser)
{
    $precache = [];
    if (empty($_GET['viewtopic'])) {
        echo Message('You didn\'t select a valid topic', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_board, ft_locked, ft_pinned, id AS subbed
        FROM forum_topics
        LEFT JOIN forum_subscriptions ON topic = ft_id
        WHERE ft_id = ?');
    $db->execute([$_GET['viewtopic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $db->query('SELECT fb_id, fb_name, fb_owner, fb_auth FROM forum_boards WHERE fb_id = ?');
    $db->execute([$topic['ft_board']]);
    if (!$db->count()) {
        echo Message('The board for this topic doesn\'t exist'.(trashTopic($topic['ft_id']) ? '. This topic has been automatically deleted/recycled' : ''), 'Error', true);
    }
    $board = $db->fetch(true); ?><tr>
        <th class="content-head">
            <div class="big">
                <a href="forum.php">Index</a> &rarr;
                <a href="forum.php?viewforum=<?php echo $board['fb_id']; ?>"><?php echo format($board['fb_name']); ?></a> &rarr;
                <a href="forum.php?viewtopic=<?php echo $topic['ft_id']; ?>"><?php echo format($topic['ft_name']); ?></a>
            </div>
        </th>
    </tr>
    <tr>
        <td class="content"><?php
    accessCheck($board, $user_class);
    $pages = new Paginator(getCount($topic['ft_id'], 'posts_topics'));
    if (array_key_exists('latest', $_GET)) {
        exit(header('Location: forum.php?viewtopic='.$topic['ft_id'].'&page='.$pages->num_pages.'#latest'));
    }
    $blocked = [];
    $db->query('SELECT blocked_id FROM users_blocked WHERE userid = ?');
    $db->execute([$user_class->id]);
    if ($db->count()) {
        $blockrows = $db->fetch();
        foreach ($blockrows as $row) {
            $blocked[] = $row['blocked_id'];
        }
    }
    $extra = count($blocked) ? ' AND fp_poster NOT IN ('.implode(',', $blocked).')' : '';
    $db->query('SELECT * FROM forum_posts WHERE fp_topic = ?'.$extra.' ORDER BY fp_time ASC LIMIT '.$pages->limit_start.', '.$pages->limit_end);
    $db->execute([$topic['ft_id']]);
    $posts = $db->fetch();
    $csrfg = csrf_create('csrfg', false);
    if ($user_class->admin == 1) {
        $pinOpposite = $topic['ft_pinned'] ? 'Unpin' : 'Pin';
        $lockOpposite = $topic['ft_locked'] ? 'Unlock' : 'Lock'; ?>
        <div class="pure-g center">
            <div class="pure-u-1-2">
                <form action="forum.php?act=move&amp;topic=<?php echo $topic['ft_id']; ?>" method="post" class="pure-form pure-form-aligned">
                    <div class="pure-control-group">
                        <label for="board">Move topic to</label>
                        <?php echo forums_boards('board'); ?>
                    </div>
                    <div class="pure-controls">
                        <button type="submit" class="pure-button pure-button-primary"><i class="fa fa-arrow-circle-right" aria-hidden="true"></i> Move</button>
                    </div>
                </form>
            </div>
            <div class="pure-u-1-2">
                <a href="forum.php?act=pin&amp;topic=<?php echo $topic['ft_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><img src="images/silk/exclamation.png" alt="<?php echo $pinOpposite; ?>" title="<?php echo $pinOpposite; ?>" /></a> &middot;
                <a href="forum.php?act=lock&amp;topic=<?php echo $topic['ft_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><img src="images/silk/lock.png" alt="<?php echo $lockOpposite; ?>" title="<?php echo $lockOpposite; ?>" /></a> &middot;
                <a href="forum.php?act=deletopic&amp;topic=<?php echo $topic['ft_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><img src="images/silk/delete.png" title="Delete Topic" alt="Delete Topic" /></a>
            </div>
        </div><?php
    }
    $subWhich = isset($topic['subbed']) ? 'Uns' : 'S'; ?><div class="pure-g center"><?php
    if ($topic['ft_locked'] && $user_class->admin != 1) {
        ?><div class="pure-u-1-1 pure-info-message">This topic is locked. Only staff members can respond</div><?php
    } else {
        $csrfReply = csrf_create(); ?><div class="pure-u-1-1 center">
                <form action="forum.php?reply=<?php echo $topic['ft_id']; ?>" method="post" class="pure-form pure-form-aligned">
                    <?php echo $csrfReply; ?>
                    <div class="pure-control-group">
                        <label for="message">Enter a response</label>
                        <textarea name="message" id="message" rows="7" cols="85%" required></textarea>
                    </div>
                    <div class="pure-controls">
                        <button type="submit" class="pure-button pure-button-primary"><i class="fa fa-cog" aria-hidden="true"></i> Post Response</button>
                    </div>
                </form>
            </div><?php
    } ?>
    </div>
    <div class="pure-g center">
        <div class="pure-u-1-6">
            <a href="forum.php?act=sub&amp;topic=<?php echo $topic['ft_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>" class="pure-button pure-button-grey"><i class="fa fa-envelope" aria-hidden="true"></i> <?php echo $subWhich; ?>ubscribe</a>
        </div>
    </div>
</td>
</tr>
<tr>
    <th class="content-head">&nbsp;</th>
</tr>
<tr>
    <td class="content"><?php
    echo $pages->display_pages(); ?>
    <table class="pure-table pure-table-horizontal" width="100%">
        <thead>
            <tr>
                <th width="25%">Poster</th>
                <th width="75%">Content</th>
            </tr>
        </thead><?php
    if($posts !== null) {
    $cnt = count($posts);
    $no = isset($_GET['page']) && $_GET['page'] > 1 ? ($pages->items_per_page * $_GET['page']) - $pages->items_per_page : 0;
    foreach ($posts as $post) {
        $date = new DateTime($post['fp_time']);
        ++$no;
        if (isset($precache[$post['fp_poster']])) {
            $memb = $precache[$post['fp_poster']];
        } else {
            $db->query('SELECT id FROM users WHERE id = ?');
            $db->execute([$post['fp_poster']]);
            if ($db->count()) {
                $tmp = new User($post['fp_poster']);
                $memb = ['id' => $tmp->id, 'level' => $tmp->level, 'avatar' => $tmp->avatar, 'signature' => $tmp->signature];
            } else {
                $memb = ['id' => 0, 'level' => 0, 'avatar' => '', 'signature' => ''];
            }
            $precache[$memb['id']] = $memb;
        }
        if ($post['fp_edit_times']) {
            $edit = new DateTime($post['fp_edit_time']);
            $post['fp_text'] .= "\n\n".'[i]Edited '.$edit->format('F d, Y g:i:sa').'. Reason: '.($post['fp_edit_reason'] ?: 'None').'[/i]';
        } ?><tr>
            <th class="center">Post #<?php echo $cnt == ($no - 25) ? '<a id="latest" href="#latest">'.$no.'</a>' : $no; ?></th>
            <th class="center top">
                <?php echo $date->format('F d, Y g:i:sa'); ?><br />
                <span class="small">
                    <a href="forum.php?act=quote&amp;viewtopic=<?php echo $topic['ft_id']; ?>&amp;quote=<?php echo $post['fp_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><img src="images/silk/page_attach.png" title="Quote" alt="[Quote]" /></a><?php
        if ($user_class->admin == 1) {
            if ($post['fp_poster'] == $user_class->id) {
                ?><a href="forum.php?act=edit&amp;topic=<?php echo $topic['ft_id']; ?>&amp;post=<?php echo $post['fp_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><img src="images/silk/pencil_go.png" title="Edit" alt="[Edit]" /></a><?php
            } ?><a href="forum.php?act=delepost&amp;topic=<?php echo $topic['ft_id']; ?>&amp;post=<?php echo $post['fp_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><img src="images/silk/page_delete.png" title="Delete" alt="[Delete]" /></a><?php
        } ?></span>
            </th>
        </tr>
        <tr>
            <td><?php
        if ($memb['id']) {
            $poster = new User($post['fp_poster']);
            echo $poster->formattedname; ?><br />
                    <?php echo formatImage($poster->avatar); ?><br />
                    <?php echo forums_rank(getCount($post['fp_poster'], 'posts_user'));
        } else {
            echo '<span class="bold italic">Deleted user</span>';
        } ?></td>
            <td>
                <?php echo tag($parser->getAsHTML($parser->parse(nl2br(format($post['fp_text'])))), true); ?><br />
                <hr width="50%" />
                <?php echo $parser->getAsHTML($parser->parse(nl2br(format($memb['signature'])))); ?>
            </td>
        </tr><?php
    }
    }?></table>
    <?php echo $pages->display_pages(); ?><br /><br /><?php
    if (!$topic['ft_locked'] || ($user_class->admin == 1 && $topic['ft_locked'])) {
        if ($user_class->admin == 1 && $topic['ft_locked']) {
            echo Message('This topic is locked. Only staff members (with access) can respond');
        }
        if (!isset($csrfReply)) {
            $csrfReply = csrf_create();
        } ?><form action="forum.php?reply=<?php echo $topic['ft_id']; ?>" method="post" class="pure-form pure-form-aligned">
            <?php echo $csrfReply; ?>
            <fieldset>
                <legend>Post a reply to this topic</legend>
                <textarea name="message" rows="7" cols="50" required></textarea>
                <button type="submit" class="pure-button pure-button-primary"><i class="fa fa-cog" aria-hidden="true"></i> Post Response</button>
            </fieldset>
        </form><?php
    } else {
        echo '<span class="italic">This topic has been locked, you can\'t respond</span>';
    } ?></td></tr><?php
}
function newtopic($db, $user_class, $parser)
{
    if (empty($_GET['forum'])) {
        echo Message('You didn\'t select a valid board', 'Error', true);
    }
    $db->query('SELECT fb_id, fb_name, fb_owner, fb_auth FROM forum_boards WHERE fb_id = ?');
    $db->execute([$_GET['forum']]);
    $board = $db->fetch(true);
    if ($board === null) {
        echo Message('That board doesn\'t exist', 'Error', true);
    } ?>
    <div class="big">
        <a href="forum.php">Index</a> &rarr;
        <a href="forum.php?viewforum=<?php echo $board['fb_id']; ?>"><?php echo format($board['fb_name']); ?></a> &rarr;
        Topic Creation
    </div><?php
    accessCheck($board, $user_class);
    $errors = [];
    if (array_key_exists('submit', $_POST)) {
        if (!csrf_check('csrf', $_POST)) {
            echo Message(SECURITY_TIMEOUT_MESSAGE);
        }
        $_POST['name'] = array_key_exists('name', $_POST) && is_string($_POST['name']) ? strip_tags(trim($_POST['name'])) : null;
        if (empty($_POST['name'])) {
            $errors[] = 'You didn\'t enter a valid topic name';
        }
        $_POST['message'] = array_key_exists('message', $_POST) && is_string($_POST['message']) ? strip_tags(trim($_POST['message'])) : null;
        if (empty($_POST['message'])) {
            $errors[] = 'You didn\'t enter a valid message';
        }
        $db->query('SELECT ft_id FROM forum_topics WHERE ft_name = ? AND ft_creation_user = ? ORDER BY ft_id DESC LIMIT 1');
        $db->execute([$_POST['name'], $user_class->id]);
        $topic = $db->fetch(true);
        if ($topic !== null) {
            $db->query('SELECT fp_id FROM forum_posts WHERE fp_text = ? AND fp_poster = ? AND fp_topic = ? ORDER BY fp_id DESC LIMIT 1');
            $db->execute([$_POST['message'], $user_class->id, $topic['ft_id']]);
            if ($db->count()) {
                $error[] = 'You\'ve already made that topic/post';
            }
        }
        if (count($errors)) {
            display_errors($errors);
        }

        $db->trans('start');
        $db->query('INSERT INTO forum_topics (ft_board, ft_name, ft_creation_user, ft_latest_user) VALUES (?, ?, ?, ?)');
        $db->execute([$board['fb_id'], $_POST['name'], $user_class->id, $user_class->id]);
        $topicID = $db->id();
        $db->query('INSERT INTO forum_posts (fp_board, fp_topic, fp_poster, fp_text) VALUES (?, ?, ?, ?)');
        $db->execute([$board['fb_id'], $topicID, $user_class->id, $_POST['message']]);
        $postID = $db->id();
        $db->query('UPDATE forum_topics SET ft_latest_post = ? WHERE ft_id = ?');
        $db->execute([$postID, $topicID]);
        $db->query('UPDATE forum_boards SET fb_topics = fb_topics + 1, fb_posts = fb_posts + 1, fb_latest_topic = ?, fb_latest_post = ?, fb_latest_poster = ?, fb_latest_time = NOW() WHERE fb_id = ?');
        $db->execute([$topicID, $postID, $user_class->id, $board['fb_id']]);
        $db->query('UPDATE users SET posts = posts + 1 WHERE id = ?');
        $db->execute([$user_class->id]);
        $db->trans('end');
        echo Message('Your new topic has been created!', 'Success');
        $_GET['viewtopic'] = $topicID;
        $_GET['latest'] = true;
        exit(viewtopic($db, $user_class, $parser));
    } ?><form action="forum.php?act=newtopic&amp;forum=<?php echo $board['fb_id']; ?>" method="post" class="pure-form pure-form-aligned">
        <?php echo csrf_create(); ?>
        <fieldset>
            <div class="pure-control-group">
                <label for="name">Topic Name</label>
                <input type="text" name="name" autofocus required />
            </div>
            <div class="pure-control-group">
                <label for="message">Topic Message</label>
                <textarea name="message" rows="7" cols="50"></textarea>
            </div>
            <div class="pure-controls">
                <button type="submit" name="submit" class="pure-button pure-button-primary">Create Topic</button>
            </div>
        </fieldset>
    </form><?php
}
function reply($db, $user_class, $parser)
{
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_GET['reply'])) {
        echo Message('Well.. Something screwed up.. Please try again later', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_locked, ft_board FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['reply']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $db->query('SELECT fb_id, fb_owner, fb_auth FROM forum_boards WHERE fb_id = ?');
    $db->execute([$topic['ft_board']]);
    if (!$db->count()) {
        echo Message('The board for this topic doesn\'t exist'.(trashTopic($topic['ft_id']) ? '. This topic has been automatically deleted/recycled' : ''), 'Error', true);
    }
    $board = $db->fetch(true);
    accessCheck($board, $user_class);
    if ($topic['ft_locked'] && $user_class->admin != 1) {
        echo Message('This topic has been locked. No further responses are permitted', 'Error', true);
    }
    $_POST['message'] = array_key_exists('message', $_POST) && is_string($_POST['message']) ? strip_tags(trim($_POST['message'])) : null;
    if (empty($_POST['message'])) {
        echo Message('You didn\'t enter a valid response', 'Error', true);
    }
    $db->query('SELECT userid FROM forum_subscriptions WHERE topic = ?');
    $db->execute([$topic['ft_id']]);
    $notify = $db->fetch();
    $db->trans('start');
    if ($notify !== null) {
        foreach ($notify as $user) {
            if ($user != $user_class->id) {
                Send_Event($user, '{extra} has posted on your subscription: <a href="forum.php?viewtopic='.$topic['ft_id'].'&amp;latest">'.format($topic['ft_name']).'</a>', $user_class->id);
            }
        }
    }
    $db->query('INSERT INTO forum_posts (fp_board, fp_topic, fp_poster, fp_text) VALUES (?, ?, ?, ?)');
    $db->execute([$board['fb_id'], $topic['ft_id'], $user_class->id, $_POST['message']]);
    $post = $db->id();
    $db->query('UPDATE forum_topics SET ft_latest_time = NOW(), ft_latest_user = ?, ft_latest_post = ? WHERE ft_id = ?');
    $db->execute([$user_class->id, $post, $topic['ft_id']]);
    $db->query('UPDATE forum_boards SET fb_posts = fb_posts + 1, fb_latest_topic = ?, fb_latest_post = ?, fb_latest_poster = ?, fb_latest_time = NOW() WHERE fb_id = ?');
    $db->execute([$topic['ft_id'], $post, $user_class->id, $board['fb_id']]);
    $db->query('UPDATE users SET posts = posts + 1 WHERE id = ?');
    $db->execute([$user_class->id]);
    tag($_POST['message'], false, $topic['ft_id']);
    $db->trans('end');
    echo Message('Your response has been posted', 'Success');
    $_GET['latest'] = true;
    $_GET['viewtopic'] = $_GET['reply'];
    viewtopic($db, $user_class, $parser);
}
function quote($db, $user_class, $parser)
{
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_GET['viewtopic'])) {
        echo Message('You didn\'t select a valid topic', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_board, ft_locked FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['viewtopic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $_GET['quote'] = array_key_exists('quote', $_GET) && ctype_digit($_GET['quote']) ? $_GET['quote'] : null;
    if (empty($_GET['quote'])) {
        echo Message('You didn\'t select a valid quote', 'Error', true);
    }
    $db->query('SELECT fp_id, fp_topic, fp_poster, fp_text FROM forum_posts WHERE fp_id = ?');
    $db->execute([$_GET['quote']]);
    if (!$db->count()) {
        echo Message('That quote doesn\'t exist', 'Error', true);
    }
    $post = $db->fetch(true);
    if ($post['fp_topic'] != $topic['ft_id']) {
        echo Message('That post doesn\'t belong to that topic ('.format($topic['ft_name']).')', 'Error', true);
    }
    $quoter = new User($post['fp_poster']);
    $db->query('SELECT fb_id, fb_name, fb_auth, fb_owner FROM forum_boards WHERE fb_id = ?');
    $db->execute([$topic['ft_board']]);
    if (!$db->count()) {
        echo Message('The board for this topic doesn\'t exist'.(trashTopic($topic['ft_id']) ? '. This topic has been automatically deleted/recycled' : ''), 'Error', true);
    }
    $board = $db->fetch(true); ?><div class="big">
        <a href="forum.php">Index</a> &rarr;
        <a href="forum.php?viewforum=<?php echo $board['fb_id']; ?>"><?php echo format($board['fb_name']); ?></a> &rarr;
        <a href="forum.php?viewtopic=<?php echo $topic['ft_id']; ?>"><?php echo format($topic['ft_name']); ?></a> &rarr;
        Quote
    </div><br /><?php
    accessCheck($board, $user_class);
    if ($topic['ft_locked'] && $user_class->admin != 1) {
        echo Message('This topic has been locked. No further responses are permitted', 'Error', true);
    } ?><form action="forum.php?reply=<?php echo $topic['ft_id']; ?>&amp;csrfg=<?php echo csrf_create('csrfg', false); ?>" method="post" class="pure-form pure-form-aligned">
        <div class="pure-control-group">
            <label for="message">Quote/Response</label>
            <textarea name="message" rows="7" cols="50" autofocus required>[quote=<?php echo $quoter->username; ?>]<?php echo format($post['fp_text']); ?>[/quote]</textarea>
        </div>
        <div class="pure-controls">
            <button type="submit" name="submit" class="pure-button pure-button-primary">Post Response</button>
        </div>
    </form><?php
}
// FORUM SUBSCRIPTION FUNCTIONS
function subscribe($db, $user_class, $parser)
{
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_GET['topic'])) {
        echo Message('You didn\'t select a valid topic', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['topic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $db->query('SELECT id FROM forum_subscriptions WHERE userid = ? AND topic = ?');
    $db->execute([$user_class->id, $topic['ft_id']]);
    if ($db->count()) {
        $id = $db->result();
        $db->query('DELETE FROM forum_subscriptions WHERE id = ?');
        $db->execute([$id]);
        $which = 'unsubscribed from';
    } else {
        $db->query('INSERT INTO forum_subscriptions (userid, topic) VALUES (?, ?)');
        $db->execute([$user_class->id, $topic['ft_id']]);
        $which = 'subscribed to';
    }
    $_SESSION['success'] = 'You\'ve '.$which.' <a href="forum.php?viewtopic='.$topic['ft_id'].'">'.format($topic['ft_name']).'</a>';
    if (array_key_exists('from', $_GET)) {
        if ($_GET['from'] === 'manage') {
            exit(header('Location: forum.php?act=managesub'));
        }
    } else {
        $_GET['latest'] = true;
        $_GET['viewtopic'] = $topic['ft_id'];
        viewtopic($db, $user_class, $parser);
    }
}
function manage_subscriptions($db, $user_class, $parser)
{
    $db->query('SELECT id, date_subbed, ft_id, ft_name, ft_latest_post, ft_latest_user, ft_latest_time
        FROM forum_subscriptions
        LEFT JOIN forum_topics ON topic = ft_id
        WHERE userid = ?
        ORDER BY date_subbed ');
    $db->execute([$user_class->id]);
    $rows = $db->fetch(); ?><table class="pure-table pure-table-horizontal" width="100%">
        <thead>
            <tr>
                <th width="35%">Topic</th>
                <th width="35%">Latest Poster</th>
                <th width="30%">Actions</th>
            </tr>
        </thead><?php
    if ($rows !== null) {
        $csrfg = csrf_create('csrfg', false);
        foreach ($rows as $row) {
            $poster = $row['ft_latest_user'] ? new User($row['ft_latest_user']) : (object) ['formattedname' => 'No activity yet']; ?><tr>
                <td><a href="forum.php?viewtopic=<?php echo $row['ft_id']; ?>&amp;csrfg=<?php echo $csrfg; ?>"><?php echo format($row['ft_name']); ?></a></td>
                <td><?php echo $row['ft_latest_user'] ? $poster->formattedname : 'No activity yet'; ?></td>
                <td><a href="forum.php?act=sub&amp;topic=<?php echo $row['ft_id']; ?>&amp;from=manage&amp;csrfg=<?php echo $csrfg; ?>" class="pure-button pure-button-grey">Unsubscribe</a></td>
            </tr><?php
        }
    } else {
        ?><tr>
            <td colspan="3" class="center">You don't have any subscriptions</td>
        </tr><?php
    } ?></table><?php
}
// FORUM MODERATION FUNCTIONS
function edit($db, $user_class, $parser)
{
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_GET['post']) || empty($_GET['topic'])) {
        echo Message('You didn\'t select a valid '.(empty($_GET['post']) ? 'post' : 'topic'), 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_board FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['topic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $db->query('SELECT fb_id, fb_name, fb_auth, fb_owner FROM forum_boards WHERE fb_id = ?');
    $db->execute([$topic['ft_board']]);
    if (!$db->count()) {
        echo Message('The board for this topic doesn\'t exist'.(trashTopic($topic['ft_id']) ? '. This topic has been automatically deleted/recycled' : ''), 'Error', true);
    }
    $board = $db->fetch(true); ?><div class="big">
        <a href="forum.php">Index</a> &rarr;
        <a href="forum.php?viewforum=<?php echo $board['fb_id']; ?>"><?php echo format($board['fb_name']); ?></a> &rarr;
        <a href="forum.php?viewtopic=<?php echo $topic['ft_id']; ?>"><?php echo format($topic['ft_name']); ?></a> &rarr;
        Edit Post
    </div><br /><?php
    accessCheck($board, $user_class);
    $db->query('SELECT fp_id, fp_topic, fp_poster, fp_text FROM forum_posts WHERE fp_id = ?');
    $db->execute([$_GET['post']]);
    if (!$db->count()) {
        echo Message('That post wasn\'t found', 'Error', true);
    }
    $post = $db->fetch(true);
    if ($post['fp_topic'] != $topic['ft_id']) {
        echo Message('That post doesn\'t belong to that topic ('.format($topic['ft_name']).')', 'Error', true);
    }
    if (!($user_class->admin == 1 || $user_class->id == $post['fp_poster'])) {
        echo Message('You don\'t have access', 'Error', true);
    }
    if (array_key_exists('submit', $_POST)) {
        $_POST['message'] = array_key_exists('message', $_POST) && is_string($_POST['message']) ? strip_tags(trim($_POST['message'])) : null;
        $_POST['reason'] = array_key_exists('reason', $_POST) && is_string($_POST['reason']) ? strip_tags(trim($_POST['reason'])) : '';
        if (empty($_POST['message'])) {
            echo Message('You didn\'t enter a valid message', 'Error', true);
        }
        if ($_POST['message'] == $post['fp_text']) {
            echo Message('You didn\'t make an edit', 'Error', true);
        }
        $db->query('UPDATE forum_posts SET fp_text = ?, fp_edit_times = fp_edit_times + 1, fp_edit_reason = ?, fp_edit_time = NOW() WHERE fp_id = ?');
        $db->execute([$_POST['message'], $_POST['reason'], $_GET['post']]);
        echo Message('Your edits has been posted', 'Success');
        $_GET['viewtopic'] = $_GET['topic'];
        exit(viewtopic($db, $user_class, $parser));
    } ?><form action="forum.php?act=edit&amp;topic=<?php echo $topic['ft_id']; ?>&amp;post=<?php echo $post['fp_id']; ?>" method="post" class="pure-form">
        <div class="pure-control-group">
            <label for="message">Post</label><br />
            <textarea name="message" rows="7" cols="50" autofocus><?php echo format($post['fp_text']); ?></textarea>
        </div>
        <div class="pure-control-group">
            <label for="reason">Reason for editing (optional):</label>
            <input type="text" name="reason" />
        </div>
        <div class="pure-controls">
            <button type="submit" name="submit" class="pure-button pure-button-primary">Edit Post</button>
        </div>
    </form><?php
}
function delepost($db, $user_class, $parser)
{
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_GET['post'])) {
        echo Message('You didn\'t select a valid post', 'Error', true);
    }
    $db->query('SELECT fp_id, fp_topic, fp_board, fp_poster FROM forum_posts WHERE fp_id = ?');
    $db->execute([$_GET['post']]);
    if (!$db->count()) {
        echo Message('That post doesn\'t exist', 'Error', true);
    }
    $post = $db->fetch(true);
    if (($post['fp_poster'] != $user_class->id) && 1 != $user_class->admin) {
        echo Message('You don\'t have access', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name FROM forum_topics WHERE ft_id = ?');
    $db->execute([$post['fp_topic']]);
    if (!$db->count()) {
        echo Message('The parent topic for this post doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $db->query('SELECT fb_id, fb_name FROM forum_boards WHERE fb_id = ?');
    $db->execute([$post['fp_board']]);
    if (!$db->count()) {
        echo Message('The parent board for this topic/post doesn\'t exist', 'Error', true);
    }
    $board = $db->fetch(true);
    $db->trans('start');
    $db->query('DELETE FROM forum_posts WHERE fp_id = ?');
    $db->execute([$post['fp_id']]);
    recache_topic($post['fp_topic']);
    recache_forum($post['fp_board']);
    $db->trans('end');
    $_GET['viewtopic'] = $topic['ft_id'];
    echo Message('Post #'.format($post['fp_id']).' has been deleted', 'Success');
    viewtopic($db, $user_class, $parser);
}
function deletopic($db, $user_class, $parser)
{
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if (empty($_GET['topic'])) {
        echo Message('You didn\'t select a valid topic', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_board FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['topic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $db->query('SELECT fb_id FROM forum_boards WHERE fb_bin = 1');
    $db->execute();
    if ($db->count()) {
        $bin = $db->result();
        $db->trans('start');
        $db->query('UPDATE forum_topics SET ft_board = ? WHERE ft_id = ?');
        $db->execute([$bin, $topic['ft_id']]);
        $db->query('UPDATE forum_posts SET fp_board = ? WHERE fp_topic = ?');
        $db->execute([$bin, $topic['ft_id']]);
        recache_forum($topic['ft_board']);
        $db->trans('end');
        echo Message(format($topic['ft_name']).' has been sent to the Recycle Bin', 'Success');
    } else {
        $db->trans('start');
        $db->query('DELETE FROM forum_topics WHERE ft_id = ?');
        $db->execute([$topic['ft_id']]);
        $db->query('DELETE FROM forum_posts WHERE fp_topic = ?');
        $db->execute([$topic['ft_id']]);
        recache_forum($topic['ft_board']);
        $db->trans('end');
        echo Message(format($topic['ft_name']).' has been deleted', 'Success');
    }
}
function move($db, $user_class, $parser)
{
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($user_class->admin != 1) {
        echo Message('You don\'t have access', 'Error', true);
    }
    if (empty($_GET['topic'])) {
        echo Message('You didn\'t select a valid topic', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_board FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['topic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $_POST['board'] = array_key_exists('board', $_POST) && ctype_digit($_POST['board']) ? $_POST['board'] : null;
    if (empty($_POST['board'])) {
        echo Message('You didn\'t select a valid board', 'Error', true);
    }
    if ($_POST['board'] == $topic['ft_board']) {
        echo Message('The destination board you selected is the same as the original board', 'Error', true);
    }
    $db->query('SELECT fb_id, fb_name FROM forum_boards WHERE fb_id = ?');
    $db->execute([$_POST['board']]);
    if (!$db->count()) {
        echo Message('That board doesn\'t exist', 'Error', true);
    }
    $board = $db->fetch(true);
    $postCount = getCount($topic['ft_id'], 'posts_topics');
    $db->trans('start');
    $db->query('UPDATE forum_posts SET fp_board = ? WHERE fp_topic = ?');
    $db->execute([$board['fb_id'], $topic['ft_id']]);
    $db->query('UPDATE forum_topics SET ft_board = ? WHERE ft_id = ?');
    $db->execute([$board['fb_id'], $topic['ft_id']]);
    $db->query('UPDATE forum_boards SET fb_posts = fb_posts + ?, fb_topics = fb_topics + 1 WHERE fb_id = ?');
    $db->execute([$postCount, $board['fb_id']]);
    recache_forum($board['fb_id']);
    recache_forum($topic['ft_board']);
    $db->trans('end');
    echo Message('You\'ve moved '.format($topic['ft_name']).' to '.format($board['fb_name']), 'Success');
    $_GET['viewtopic'] = $topic['ft_id'];
    viewtopic($db, $user_class, $parser);
}
function lock($db, $user_class, $parser)
{
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($user_class->admin != 1) {
        echo Message('You don\'t have access', 'Error', true);
    }
    if (empty($_GET['topic'])) {
        echo Message('You didn\'t select a valid topic', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_locked, ft_board FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['topic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $opposite = $topic['ft_locked'] ? 'Unl' : 'L';
    $db->trans('start');
    $db->query('UPDATE forum_topics SET ft_locked = IF(ft_locked = 1, 0, 1) WHERE ft_id = ?');
    $db->execute([$topic['ft_id']]);
    $db->trans('end');
    echo Message('You\'ve '.strtolower($opposite).'ocked '.format($topic['ft_name']), 'Success');
    $_GET['viewforum'] = $topic['ft_board'];
    viewforum($db, $user_class, $parser);
}
function pin($db, $user_class, $parser)
{
    if (!csrf_check('csrfg', $_GET)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    if ($user_class->admin != 1) {
        echo Message('You don\'t have access', 'Error', true);
    }
    if (empty($_GET['topic'])) {
        echo Message('You didn\'t select a valid topic', 'Error', true);
    }
    $db->query('SELECT ft_id, ft_name, ft_pinned, ft_board FROM forum_topics WHERE ft_id = ?');
    $db->execute([$_GET['topic']]);
    if (!$db->count()) {
        echo Message('That topic doesn\'t exist', 'Error', true);
    }
    $topic = $db->fetch(true);
    $opposite = $topic['ft_pinned'] ? 'Unp' : 'P';
    $db->trans('start');
    $db->query('UPDATE forum_topics SET ft_pinned = IF(ft_pinned = 1, 0, 1) WHERE ft_id = ?');
    $db->execute([$topic['ft_id']]);
    $db->trans('end');
    echo Message('You\'ve '.strtolower($opposite).'inned '.format($topic['ft_name']), 'Success');
    $_GET['viewforum'] = $topic['ft_board'];
    viewforum($db, $user_class, $parser);
}
// FORUM HELPER FUNCTIONS
function recache_topic($id = 0)
{
    global $db;
    if (!$id || !ctype_digit($id)) {
        return false;
    }
    $db->query('SELECT COUNT(ft_id) FROM forum_topics WHERE ft_id = ?', [$id]);
    $topic = $db->result();
    if($topic > 0) {
        echo 'Recaching topic ID ' . $id;
        $db->query('SELECT fp_id, fp_poster, fp_time, fp_topic, fp_board FROM forum_posts WHERE fp_topic = ? ORDER BY fp_time DESC LIMIT 1',
            [$id]);
        $post = $db->fetch(true);
        if ($post !== null) {
            $postCount = getCount($post['fp_board'], 'posts_boards');
            $topicCount = getCount($post['fp_board'], 'topics');
            $db->query('UPDATE forum_boards SET fb_topics = ?, fb_posts = ?, fb_latest_topic = ?, fb_latest_post = ?, fb_latest_poster = ?, fb_latest_time = ? WHERE fb_id = ?', [
                $topicCount, $postCount, $post['fp_topic'], $post['fp_id'], $post['fp_poster'], $post['fp_time'], $post['fp_board']
            ]);
            echo ' ... Done<br />';
        } else {
            $db->query('UPDATE forum_topics SET ft_latest_time = NULL, ft_latest_user = 0, ft_latest_post = 0 WHERE ft_id = ?', [$id]);
            echo ' ... Done<br />';
        }
    }
}
function recache_forum($id = 0)
{
    global $db;
    if (!$id || !ctype_digit($id)) {
        return false;
    }
    echo 'Recaching forum ID #'.$id;
    $db->query('SELECT fp_id, fp_poster, fp_time, ft_id, ft_name
        FROM forum_posts
        LEFT JOIN forum_topics ON fp_topic = ft_id
        WHERE fp_board = ?
        ORDER BY fp_time DESC LIMIT 1');
    $db->execute([$id]);
    if ($db->count()) {
        $row = $db->fetch(true);
        $postCount = getCount($id, 'posts_boards');
        $topicCount = getCount($id, 'topics');
        $db->query('UPDATE forum_boards SET fb_topics = ?, fb_posts = ?, fb_latest_topic = ?, fb_latest_post = ?, fb_latest_poster = ?, fb_latest_time = ? WHERE fb_id = ?');
        $db->execute([$topicCount, $postCount, $row['ft_id'], $row['fp_id'], $row['fp_poster'], $row['fp_time'], $id]);
        echo '... Done<br />';
    } else {
        $db->query('UPDATE forum_boards SET fb_topics = 0, fb_posts = 0, fb_latest_topic = 0, fb_latest_post = 0, fb_latest_poster = 0, fb_latest_time = NULL WHERE fb_id = ?');
        $db->execute([$id]);
        echo '... Done<br />';
    }
}
function accessCheck($data, $user_class)
{
    if (!isset($data['fb_auth'], $data['fb_owner'])) {
        $extra = '';
        $text = 'Missing resource in block check.';
        if (!empty($_GET['viewforum'])) {
            $text .= "\n".'Board ID: '.$_GET['viewforum'];
        }
        if (!empty($_GET['forum'])) {
            $text .= "\n".'Board ID: '.$_GET['forum'];
        }
        if (!empty($_GET['viewtopic'])) {
            $text .= "\n".'Topic ID: '.$_GET['viewtopic'];
        }
        if (!empty($_GET['topic'])) {
            $text .= "\n".'Topic ID: '.$_GET['topic'];
        }
        if (!empty($_GET['reply'])) {
            $text .= "\n".'Topic ID: '.$_GET['reply'];
        }
        if (!empty($_GET['act'])) {
            $text .= "\n".'Act: '.$_GET['act'];
        }
        $extra .= generate_ticket($user_class->id, 'Forum resource not found', $text) ? '. A bug report has been generated on your account. You don\'t need to do anything else' : '. This bug has already been reported. You don\'t have to worry about it ;)';
        echo Message('Resource not defined'.$extra, 'Error', true);
    }
    if ($data['fb_auth'] === 'gang' && $user_class->gang != $data['fb_owner'] && 1 != $user_class->admin) {
        echo Message('You don\'t have access', 'Error', true);
    }
    if ($data['fb_auth'] === 'staff' && $user_class->admin != 1) {
        echo Message('You don\'t have access', 'Error', true);
    }
}
function getCount($id = null, $type = null)
{
    global $db;
    if (!ctype_digit($id)) {
        return 0;
    }
    switch ($type) {
        case 'boards':
            $db->query('SELECT COUNT(fb_id) FROM forum_boards');
            $db->execute();

            return $db->result();
        break;
        case 'topics':
            $db->query('SELECT COUNT(ft_id) FROM forum_topics WHERE ft_board = ?');
            $db->execute([$id]);

            return $db->result();
        break;
        case 'posts_boards':
            $db->query('SELECT COUNT(fp_id) FROM forum_posts WHERE fp_board = ?');
            $db->execute([$id]);

            return $db->result();
        break;
        case 'posts_topics':
            $db->query('SELECT COUNT(fp_id) FROM forum_posts WHERE fp_topic = ?');
            $db->execute([$id]);

            return $db->result();
        break;
        case 'posts_user':
            $db->query('SELECT COUNT(fp_id) FROM forum_posts WHERE fp_poster = ?');
            $db->execute([$id]);

            return $db->result();
        break;
        default:
            return 0;
        break;
    }
}
function trashTopic($id = null)
{
    global $db;
    if (!ctype_digit($id)) {
        return false;
    }
    $db->query('SELECT ft_board FROM forum_topics WHERE ft_id = ?');
    $db->execute([$id]);
    if (!$db->count()) {
        return false;
    }
    $board = $db->result();
    $db->query('SELECT fb_id FROM forum_boards WHERE fb_bin = 1');
    $db->execute();
    if ($db->count()) {
        $bin = $db->result();
        $db->query('UPDATE forum_topics SET ft_board = ? WHERE ft_board = ?');
        $db->execute([$bin, $board]);
    } else {
        $db->query('DELETE FROM forum_topics WHERE ft_board = ?');
        $db->execute([$board]);
    }

    return true;
}
function tag($text, $display = false, $id = false)
{
    global $db;
    $cnt = 0;
    preg_match_all('/@(\w+)/', $text, $matches);
    $ids = [];
    if (count($matches) && count($matches[0])) {
        foreach ($matches as $match) {
            ++$cnt;
            $db->query('SELECT id FROM users WHERE LOWER(username) = ? LIMIT 1', [str_replace('__', ' ', strtolower(ltrim($match[0], '@')))]);
            $row = $db->fetch(true);
            if ($row !== null) {
                ++$cnt;
                $tagged = new User($row['id']);
                if (!$display && !isset($ids[$row['id']]) && !isset($event_sent)) {
                    Send_Event($row['id'], 'You\'ve been tagged in the forum!<br /><a href="forum.php?viewtopic='.$id.'">View it here</a>');
                    $event_sent = true;
                } else {
                    return preg_replace('/@(\w+)/', $tagged->formattedname, $text);
                }
                $ids[] = $row['userid'];
                if ($cnt == 10) {
                    break;
                }
            } else {
                return $display ? $text : null;
            }
        }
    } else {
        return $display ? $text : null;
    }
}
if ($_GET['act'] !== 'viewtopic') {
    ?>  </td>
    </tr><?php
}
