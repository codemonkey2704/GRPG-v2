<?php
declare(strict_types=1);
global $owner;
require_once __DIR__.'/inc/header.php';
$errors = [];
if ((1 == $user_class->admin) && array_key_exists('submit', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $strs = ['text' => 'description', 'status' => 'status'];
    foreach ($strs as $what => $disp) {
        $_POST[$what] = array_key_exists($what, $_POST) && is_string($_POST[$what]) ? strip_tags(trim($_POST[$what])) : null;
        if (empty($_POST[$what])) {
            $errors[] = 'You didn\'t enter a valid '.$disp;
        }
    }
    if (!count($errors)) {
        $db->query('SELECT COUNT(id) FROM todo WHERE LOWER(content) = ?');
        $db->execute([strtolower($_POST['text'])]);
        if ($db->result()) {
            $db->query('UPDATE todo SET status = ? WHERE LOWER(content) = ?');
            $db->execute([$_POST['status'], strtolower($_POST['text'])]);
            echo Message('TODO list updated', 'Success');
        } else {
            $db->query('INSERT INTO todo (content, status) VALUES (?, ?)');
            $db->execute([$_POST['text'], $_POST['status']]);
            echo Message('Added to TODO list', 'Success');
        }
    }
}
if ($user_class->admin == 1 && array_key_exists('id', $_GET) && ctype_digit($_GET['id'])) {
    $db->query('SELECT id, content, status FROM todo WHERE id = ?');
    $db->execute([$_GET['id']]);
    if ($db->count()) {
        $row = $db->fetch(true);
    }
}
$db->query('SELECT * FROM todo ORDER BY status ');
$db->execute();
$rows = $db->fetch();
?><tr>
    <th class="content-head"><?php echo $owner->formattedname; ?>'s To-Do List</th>
</tr>
<tr>
    <td class="content">Here you can view what <?php echo $owner->formattedname; ?> currently has in the works for <?php echo GAME_NAME; ?>.</td>
</tr><?php
if ($user_class->admin == 1) {
    ?><tr>
        <th class="content-head">Add Item</th>
    </tr>
    <tr>
        <td class="content">
            <form action="todo.php" method="post" class="pure-form pure-form-aligned">
                <?php echo csrf_create(); ?>
                <fieldset>
                    <div class="pure-control-group">
                        <label for="text">Goal/Content</label>
                        <textarea name="text" id="text" rows="7" cols="53"><?php echo isset($row) ? format($row['content']) : ''; ?></textarea>
                    </div>
                    <div class="pure-control-group">
                        <label for="status">Status (percentage)</label>
                        <input type="text" name="status" size="10" maxlength="75" value="<?php echo isset($row) ? $row['status'] : ''; ?>" />
                    </div>
                </fieldset>
                <div class="pure-controls">
                    <button type="submit" name="submit" class="pure-button pure-button-primary">Add Goal</button>
                </div>
            </form>
        </td>
    </tr><?php
}
?><tr>
    <td class="content">
        <table width="100%" cellpadding="8">
            <tr>
                <th>Date Added</th>
                <th>Goal</th>
                <th>Status</th>
                <?php echo $user_class->admin == 1 ? '<th>Actions</th>' : ''; ?>
            </tr><?php
if ($rows !== null) {
        foreach ($rows as $row) {
            $date = new DateTime($row['time_added']); ?><tr>
                    <td><?php echo $date->format('F d, Y g:i:sa'); ?></td>
                    <td><?php echo format($row['content']); ?></td>
                    <td><?php echo $row['status'] == 100 ? '<span class="green-light">'.format($row['status']).'%</span>' : format($row['status']).'%'; ?></td><?php
                    if ($user_class->admin == 1) {
                        ?>
                        <td><a href="todo.php?id=<?php echo $row['id']; ?>">Edit</a></td><?php
                    } ?>
                </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="<?php echo $user_class->admin == 1 ? 4 : 3; ?>" class="center">Nothing's been added to the TODO list (doesn't mean nothing's due to happen ;))</td>
                </tr><?php
    }
?></table>
    </td>
</tr>
