<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$db->query('SELECT id, referred FROM referrals WHERE referrer = ? ORDER BY referrer ');
$db->execute([$user_class->id]);
$rows = $db->fetch();
?><tr>
    <th class="content-head">Refer To Earn Points</th>
</tr>
<tr>
    <td class="content">
        Your Referer Link: <?php echo BASE_URL; ?>register.php?referer=<?php echo $user_class->id; ?><br />
        UPDATE: You will receive your points only <em>after</em> we filter out multis. This is due to too many people abusing the referral system.<br />
        Because we have to do this manually now, this could take anywhere from an hour to 2 days, but rest assured that you will receive your points.
    </td>
</tr>
<tr>
    <th class="content-head">Players You Have Referred</th>
</tr>
<tr>
    <td class="content"><?php
if ($rows !== null) {
        foreach ($rows as $row) {
            $referred = new User(Get_ID($row['referred'])); ?><div><?php echo $referred->formattedname; ?> - <?php echo !$row['credited'] ? 'Pending' : 'Accepted'; ?></div><?php
        }
    } else {
        ?>You haven't referred anyone<?php
    }
?></td>
</tr>
