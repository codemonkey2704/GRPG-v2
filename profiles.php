<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$id = $_GET['id'] ?? $user_class->id;
$csrfg = csrf_create('csrfg', false);
$profile_class = $id == $user_class->id ? $user_class : new User($id);
if (!$profile_class->id) {
    echo Message('That player wasn\'t found', 'Error', true);
}
?><tr>
    <th class="content-head">Profile</th>
</tr><?php
if ($user_class->admin) {
    echo Message('<a href="profiles.php?harbinger='.$profile_class->id.'">Take Over Account</a>', 'Harbinger');
}
?><tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <tr>
                <td colspan="4">
                    <table width="100%" height="100%" cellpadding="5" cellspacing="2" class="center">
                        <tr>
                            <td width="120"><img height="100" width="100" src="<?php echo $profile_class->avatar; ?>" /></td>
                            <td><strong>Favorite Quote: </strong>&ldquo;<?php echo $profile_class->quote; ?>&rdquo;</td>
                        </tr>
                    </table>
                </td>
            </tr>
            <tr>
                <td width="12.5%"><strong>Name</strong>:</td>
                <td width="37.5%"><?php echo $profile_class->formattedname; ?></td>
                <td width="12.5%"><strong>HP</strong>:</td>
                <td width="37.5%"><?php echo $profile_class->formattedhp; ?></td>
            </tr>
            <tr>
                <td><strong>Type</strong>:</td>
                <td><?php echo $profile_class->type; ?></td>
                <td><strong>Crimes</strong>:</td>
                <td><?php echo $profile_class->crimetotal; ?></td>
            </tr>
            <tr>
                <td><strong>Level</strong>:</td>
                <td><?php echo $profile_class->level; ?></td>
                <td><strong>Money</strong>:</td>
                <td>$<?php echo $profile_class->money; ?></td>
            </tr>
            <tr>
                <td><strong>Age</strong>:</td>
                <td><?php echo $profile_class->age; ?></td>
                <td><strong>Last Active</strong>:</td>
                <td><?php echo $profile_class->formattedlastactive; ?></td>
            </tr>
            <tr>
                <td><strong>Online</strong>:</td>
                <td><?php echo $profile_class->formattedonline; ?></td>
                <td><strong>Gang</strong>:</td>
                <td><?php echo $profile_class->formattedgang; ?></td>
            </tr>
            <tr>
                <td><strong>City</strong>:</td>
                <td><?php echo $profile_class->cityname; ?></td>
                <td><strong>House</strong>:</td>
                <td><?php echo $profile_class->housename == true ? $profile_class->housename : 'None'; ?></td>
            </tr>
        </table>
    </td>
</tr><?php
if ($user_class->id != $profile_class->id) {
    ?><tr>
        <th class="content-head">Actions</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="center">
                <tr>
                    <td width="25%"><a href="pms.php?to=<?php echo $profile_class->username; ?>&amp;csrfg=<?php echo $csrfg; ?>">Message</a></td>
                    <td width="25%"><a href="attack.php?attack=<?php echo $profile_class->id; ?>&amp;csrfg=<?php echo $csrfg; ?>">Attack</a></td>
                    <td width="25%"><a href="mug.php?mug=<?php echo $profile_class->id; ?>&amp;csrfg=<?php echo $csrfg; ?>">Mug</a></td>
                    <td width="25%"><a href="spy.php?id=<?php echo $profile_class->id; ?>&amp;csrfg=<?php echo $csrfg; ?>">Spy</a></td>
                </tr>
                <tr>
                    <td><a href="sendmoney.php?person=<?php echo $profile_class->id; ?>">Send Money</a></td>
                    <td><a href="sendpoints.php?person=<?php echo $profile_class->id; ?>">Send Points</a></td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            </table>
        </td>
    </tr><?php
}
