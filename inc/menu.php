<?php
declare(strict_types=1);
if (!defined('GRPG_INC')) {
    exit;
}
global $owner;
?>
<div>
    <div class="headbox">Menu</div>
    <a class="leftmenu style1" href="index.php">Home</a>
    <a class="leftmenu" href="city.php"><!_-cityname-_!></a>
    <a class="leftmenu" href="pms.php">Mailbox <!_-mail-_!></a>
    <a class="leftmenu" href="events.php">Events <!_-events-_!></a>
    <a class="leftmenu" href="todo.php"><?php echo $owner->username; ?>'s To-Do List</a>
    <a class="leftmenu" href="forum.php">Forum</a>
    <a class="leftmenu" href="classifieds.php">Classified Ads</a>
    <a class="leftmenu" href="inventory.php">Inventory</a>
    <a class="leftmenu" href="bank.php">Bank</a>
    <a class="leftmenu" href="<?php echo !$user_class->gang ? 'create' : ''; ?>gang.php">Your Gang</a>
    <a class="leftmenu" href="gym.php">Gym</a>
    <a class="leftmenu" href="hospital.php">Hospital <!_-hospital-_!></a>
    <a class="leftmenu" href="jail.php">Jail <!_-jail-_!></a>
    <a class="leftmenu" href="crime.php">Crime</a>
    <a class="leftmenu" href="rmstore.php">RM Store</a><?php
    if ($user_class->admin == 1) {
        ?>
        <div class="headbox" style="color:yellow;">Staff</div>
        <a class="leftmenu" href="control.php">Staff Panel</a><?php
    } ?>
    <div class="headbox">Account</div><?php
if ($user_class->rmdays) {
        ?><a class="leftmenu" href="blocklist.php">Blocklist</a><?php
    }
?><a class="leftmenu" href="index.php?logout">Logout</a>
    <a class="leftmenu" href="preferences.php">Change Preferences</a>
    <a class="leftmenu" href="cpassword.php">Change Password</a>
    <a class="leftmenu" href="tickets.php">Support Desk</a>
    <!-- <a class="leftmenu" href="changestyle.php">Change Color Scheme</a> -->
</div>
