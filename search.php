<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
?><tr>
    <th class="content-head">Mobster Search</th>
</tr>
<tr>
    <td class="content">Find mobsters that meet your search criteria.</td>
</tr><?php
if (array_key_exists('search', $_POST)) {
    if (!csrf_check('csrf', $_POST)) {
        echo Message(SECURITY_TIMEOUT_MESSAGE);
    }
    $criteria = '';
    $nums = ['level', 'level2', 'money'];
    foreach ($nums as $what) {
        $_POST[$what] = array_key_exists($what, $_POST) && ctype_digit(str_replace(',', '', $_POST[$what])) ? str_replace(',', '', $_POST[$what]) : null;
    }
    $_POST['attack'] = array_key_exists('attack', $_POST) && in_array($_POST['attack'], [0, 1]) ? $_POST['attack'] : 0;
    if (!empty($_POST['level'])) {
        $criteria .= ' AND level <= '.$_POST['level'];
    }
    if (!empty($_POST['level2'])) {
        $criteria .= ' AND level >= '.$_POST['level'];
    }
    if (!empty($_POST['money'])) {
        $criteria .= ' AND money >= '.$_POST['money'];
    }
    if ($_POST['attack'] == 1) {
        $criteria .= ' AND jail = 0 AND hospital = 0 AND city = '.$user_class->city;
    }
    $db->query('SELECT id FROM users WHERE id = id'.$criteria);
    $db->execute();
    $rows = $db->fetch(); ?><tr>
        <th class="content-head">Search Results</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <thead>
                    <tr>
                        <th>Mobster</th>
                        <th>Money</th>
                        <th>Level</th>
                    </tr>
                </thead><?php
    if ($rows !== null) {
        foreach ($rows as $row) {
            $user_search = new User($row['id']); ?><tr>
                        <td><?php echo $user_search->formattedname; ?></td>
                        <td><?php echo prettynum($user_search->money, true); ?></td>
                        <td><?php echo format($user_search->level); ?></td>
                    </tr><?php
        }
    } else {
        ?><tr>
                    <td colspan="3" class="center">Nothing matches your search critera</td>
                </tr><?php
    } ?></table>
        </td>
    </tr><?php
}
?><tr>
    <td class="content">
        <form action="search.php" method="post" class="pure-form pure-form-aligned">
            <?php echo csrf_create(); ?>
            <fieldset>
                <div class="pure-control-group">
                    <label for="level">Level</label>
                    <input type="text" name="level" id="level" size="7" maxlength="10" /> to <input type="text" name="level2" size="7" maxlength="10" />(inclusive)
                </div>
                <div class="pure-control-group">
                    <label for="money">Money</label>
                    $<input type="text" name="money" id="money" size="12" maxlength="16" /> and more
                </div>
                <div class="pure-control-group">
                    <label for="attack">Attackable</label>
                    <select name="attack" id="attack">
                        <option value="1">Yes</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </fieldset>
            <div class="pure-controls">
                <button type="submit" name="search" class="pure-button pure-button-primary">Search</button>
            </div>
        </form>
    </td>
</tr>
