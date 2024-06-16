<?php
declare(strict_types=1);
if (!defined('GRPG_INC')) {
    define('GRPG_INC', true);
}
require_once __DIR__.'/inc/dbcon.php';
if (empty($_GET['id'])) {
    exit('Invalid');
}
$db->query('SELECT * FROM items WHERE id = ?');
$db->execute([$_GET['id']]);
if (!$db->count()) {
    exit('That item doesn\'t seem to exist');
}
$row = $db->fetch(true); ?>
<html>
    <head> <?php
        if (defined('BASE_URL')) {
            ?>
            <base href="<?php echo BASE_URL; ?>" /> <?php
        } ?>
        <title>Description: <?php echo format($row['name']); ?></title>
        <link rel="stylesheet" type="text/css" media="all" href="css/descriptions.css" />
    </head>
    <body>
        <table class="wrap center" width="100%" height="100%" cellpadding="5" cellspacing="0">
            <tr>
                <td valign="top">
                    <table class="header center" width="100%" cellpadding="5" cellspacing="0">
                        <tr>
                            <td><p style="color:white;font-size:16px;font-weight:bold;"><?php echo format($row['name']); ?></p></td>
                        </tr>
                    </table><br />
                    <table width="100%" cellpadding="4" cellspacing="0">
                        <tr>
                            <td colspan="2" class="style1">.: Description</td>
                        </tr>
                        <tr>
                            <td class="textl center"><img src="<?php echo format($row['image']); ?>" width="100" height="100" style="border: 1px solid #333333"></td>
                            <td class="textm2"><?php echo format($row['description']); ?></td>
                        </tr>
                    </table><br />
                    <table width="100%" cellpadding="4" cellspacing="0">
                        <tr>
                            <td colspan="4" class="style2">.: Details</td>
                        </tr>
                        <tr>
                            <td class="textm">Name: </td>
                            <td class="textr"><?php echo format($row['name']); ?></td>
                        </tr>
                        <tr>
                            <td class="textm">Sell Value: </td>
                            <td class="textr"><?php echo prettynum($row['cost'] * .6); ?></td>
                        </tr>
                        <tr>
                            <td class="textm">Shop Cost: </td>
                            <td class="textr"><?php echo prettynum($row['cost']); ?></td>
                        </tr>
                        <tr>
                            <td class="textm" valign="top">Attack Modifier: </td>
                            <td class="textr"><?php echo format($row['offense']); ?></td>
                        </tr>
                        <tr>
                            <td class="textm" valign="top">Defense Modifier: </td>
                            <td class="textr"><?php echo format($row['defense']); ?></td>
                        </tr>
                        <tr>
                            <td class="textm" valign="top">Required Level: </td>
                            <td class="textr"><?php echo format($row['level']); ?></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </body>
</html>
