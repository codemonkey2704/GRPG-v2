<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id FROM users WHERE city = ? ORDER BY CAST(strength AS CHAR) + CAST(speed AS CHAR) + CAST(defense AS CHAR) DESC LIMIT 3');
$db->execute([$user_class->city]);
$rows = $db->fetch();
$i = 1;
$leaders = [];
foreach ($rows as $row) {
    $leaders[$i] = new User($row['id']);
    ++$i;
}
?><tr>
    <th class="content-head"><?php echo $user_class->cityname; ?></th>
</tr>
<tr>
    <td class="content"><?php echo format($user_class->citydesc); ?></td>
</tr>
<tr>
    <th class="content-head">Top Deadlist Mobsters in <?php echo format($user_class->cityname); ?></th>
</tr>
<tr>
    <td class="content">
        <div class="city"><?php
for ($i = 1; $i <= 3; ++$i) {
    if (isset($leaders[$i])) {
        ?><div class="box<?php echo $i; ?>">
            <span><img height="50" width="50" src="/images/medals/<?php echo ordinal($i); ?>.png" /></span><br />
            <span><?php echo formatImage($leaders[$i]->avatar); ?></span><br />
            <span><?php echo $leaders[$i]->formattedname; ?></span><br />
            <span>Level: <?php echo format($leaders[$i]->level); ?></span><br />
            <span><?php echo !$leaders[$i]->gang ? '<br />' : $leaders[$i]->formattedgang; ?></span>
        </div><?php
    }
}
?></div>
    </td>
</tr>
<tr>
    <th class="content-head">Places To Go</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="pure-table pure-table-horizontal">
            <tr>
                <td width="33%" class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Shops</h3><br />
                    <a href="astore.php">Crazy Rileys Armor Emporium</a><br />
                    <a href="store.php">Weapon Sales</a><br />
                    <a href="itemmarket.php">Item Market</a><br />
                    <a href="pointmarket.php">Points Market</a><br />
                    <a href="spendpoints.php">Point Shop</a><br />
                    <a href="pharmacy.php">Pharmacy</a><br />
                    <?php echo $user_class->city == 2 ? '<a href="carlot.php">Big Bob\'s Used Car Lot</a>' : ''; ?>
                </td>
                <td width="34%" class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Town Hall</h3><br />
                    <a href="halloffame.php">Hall Of Fame</a><br />
                    <a href="worldstats.php">World Stats</a><br />
                    <a href="viewstaff.php">Town Hall</a><br />
                    <a href="search.php">Mobster Search</a><br />
                    <a href="citizens.php">Mobsters List</a><br />
                    <a href="online.php">Mobsters Online</a><br />
                    <a href="expguide.php">Experience Guide</a><br />
                </td>
                <td width="33%" class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Casino</h3><br />
                    <a href="lottery.php">Lottery</a><br />
                    <a href="slots.php">Slot Machine</a><br />
                    <a href="5050game.php">50/50 Game</a><br />
                </td>
            </tr>
            <tr>
                <td class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Your Home</h3><br />
                    <a href="pms.php">Mailbox <!_-mail-_!></a><br />
                    <a href="events.php">Events <!_-events-_!></a><br />
                    <a href="spylog.php">Spy Log</a><br />
                    <a href="inventory.php">Inventory</a><br />
                    <a href="refer.php">Referrals</a><br />
                    <a href="house.php">Move House</a><br />
                    <a href="fields.php">Manage Land</a>
                </td>
                <td class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Travel</h3><br />
                    <a href="bus.php">Bus Station</a><br />
                    <a href="drive.php">Drive</a><br />
                </td>
                <td class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Downtown</h3><br />
                    <a href="buydrugs.php">Shady-Looking Stranger</a><br />
                    <a href="downtown.php">Search Downtown</a><br />
                    <a href="jobs.php">Job Center</a><br />
                    <a href="gang_list.php">Gang List</a><br />
                    <a href="<?php echo !$user_class->gang ? 'create' : ''; ?>gang.php">Your Gang</a><br />
                    <a href="bank.php">Bank</a><br />
                    <a href="realestate.php">Real Estate Agency</a>
                </td>
            </tr>
            <tr>
                <td class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Car Central</h3><br />
                    <a href="garage.php">Your Garage</a><br />
                </td>
                <td class="top" style="padding-bottom:10px;">&nbsp;</td>
                <td class="top" style="padding-bottom:10px;">
                    <h3 style="padding:0;margin:0;font-size:1.4em;">Generic Street</h3><br />
                    <a href="viewstocks.php">View Stock Market</a><br />
                    <a href="brokerage.php">Brokerage Firm</a><br />
                    <a href="portfolio.php">View Portfolio</a>
                </td>
            </tr>
        </table>
    </td>
</tr>
