<?php
declare(strict_types=1);
if (!defined('GRPG_INC')) {
    exit;
}
$db->query('SELECT COUNT(id) FROM tickets WHERE status IN (\'open\', \'pending\')');
$db->execute();
$tickets = $db->result();
?>
<div>
    <div class="headbox leftmenu">Return</div>
    <a href="index.php" class="leftmenu">Back to the game</a>
</div>
<div>
    <div class="headbox leftmenu">Control Panel</div>
    <a href="control.php?page=site_settings" class="leftmenu">Site Settings</a>
    <a href="control.php" class="leftmenu">Marquee/Maintenance</a>
    <a href="control.php?page=rmoptions" class="leftmenu">RM Options</a>
    <a href="control.php?page=rmpacks" class="leftmenu">RM Upgrades</a>
    <a href="control.php?page=setplayerstatus" class="leftmenu">Player Options</a>
    <a href="massmail.php" class="leftmenu">Mass Mail</a>
    <a href="control.php?page=referrals" class="leftmenu">Manage Referrals</a>
    <div class="headbox">Game Modification</div>
    <a class="leftmenu" href="control.php?page=cars">Manage Cars</a>
    <a class="leftmenu" href="control.php?page=cities">Manage Cities</a>
    <a class="leftmenu" href="control.php?page=crimes">Manage Crimes</a>
    <a class="leftmenu" href="control.php?page=forum">Manage Forum</a>
    <a class="leftmenu" href="control.php?page=houses">Manage Houses</a>
    <a class="leftmenu" href="control.php?page=playeritems">Manage Items</a>
    <a class="leftmenu" href="control.php?page=jobs">Manage Jobs</a>
    <a class="leftmenu" href="control.php?page=voting">Manage Voting</a>
</div>
<div>
    <div class="headbox leftmenu">Misc</div>
    <a href="managetickets.php" class="leftmenu">Support Desk [<?php echo $tickets; ?>]</a>
</div>
