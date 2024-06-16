<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
$csrfg = csrf_create('csrfg', false);
?>
<tr>
    <th class="content-head">Equipped</th>
</tr>
<tr>
    <td class="content">
        <table width="100%" class="center">
            <tr>
                <td width="50%"><?php
if ($user_class->eqweapon) {
    echo formatImage($user_class->weaponimg); ?><br />
                    <?php echo item_popup($user_class->eqweapon, $user_class->weaponname); ?><br />
                    [<a href="equip.php?unequip=weapon&amp;csrfg=<?php echo $csrfg; ?>">Unequip</a>]<?php
} else {
        ?>You don't have a weapon equipped<?php
    }
?></td>
                <td width="50%"><?php
if ($user_class->eqarmor) {
    echo formatImage($user_class->armorimg); ?><br />
                    <?php echo item_popup($user_class->eqarmor, $user_class->armorname); ?><br />
                    [<a href="equip.php?unequip=armor&amp;csrfg=<?php echo $csrfg; ?>">Unequip</a>]<?php
} else {
        ?>You don't have any armor equipped<?php
    }
?></td>
            </tr>
        </table>
    </td>
</tr><?php
$db->query('SELECT itemid, quantity, cost, offense, defense, image, name, heal, reduce, drugstr, drugdef, drugspe
FROM inventory
INNER JOIN items ON itemid = items.id
WHERE userid = ?
ORDER BY name ');
$db->execute([$user_class->id]);
if (!$db->count()) {
    echo Message('You don\'t have any items', 'Error', true);
}
$rows = $db->fetch();
$weaponsCnt = 0;
$armorCnt = 0;
$healCnt = 0;
$miscCnt = 0;
$drugsCnt = 0;
$weapons = '';
$armor = '';
$misc = '';
$drugs = '';
$heal = '';
foreach ($rows as $row) {
    if ($row['offense']) {
        $weapons .= '
        <td width="25%" class="center">
            '.formatImage($row['image']).'<br />
            '.item_popup($row['itemid'], $row['name']).' [x'.format($row['quantity']).']<br />
            '.prettynum($row['cost'], true).'<br />
            '.($row['cost'] ? '[<a href="sellitem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Sell</a>]' : '').'
            [<a href="putonmarket.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Market</a>]
            [<a href="senditem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Send</a>]
            [<a href="equip.php?eq=weapon&amp;id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Equip</a>]
        </td>';
        if (!($weaponsCnt % 4)) {
            $weapons .= '</tr><tr>';
        }
        ++$weaponsCnt;
    }
    if ($row['defense']) {
        $armor .= '
        <td width="25%" class="center">
            '.formatImage($row['image']).'<br />
            '.item_popup($row['itemid'], $row['name']).' [x'.format($row['quantity']).']<br />
            '.prettynum($row['cost'], true).'<br />
            '.($row['cost'] ? '[<a href="sellitem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Sell</a>]' : '').'
            [<a href="putonmarket.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Market</a>]
            [<a href="senditem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Send</a>]
            [<a href="equip.php?eq=armor&amp;id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Equip</a>]
        </td>';
        if (!($armorCnt % 4)) {
            $armor .= '</tr><tr>';
        }
        ++$armorCnt;
    }
    if ($row['heal']) {
        $heal .= '
        <td width="25%" class="center">
            '.formatImage($row['image']).'<br />
            '.item_popup($row['itemid'], $row['name']).' [x'.format($row['quantity']).']<br />
            '.prettynum($row['cost'], true).'<br />
            '.($row['cost'] ? '[<a href="sellitem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Sell</a>]' : '').'
            [<a href="putonmarket.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Market</a>]
            [<a href="senditem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Send</a>]
            [<a href="equip.php?eq=armor&amp;id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Equip</a>]
        </td>';
        if (!($healCnt % 4)) {
            $heal .= '</tr><tr>';
        }
        ++$healCnt;
    }
    // if ($row['drugstr'] || $row['drugspe'] || $row['drugdef']) {
    //     $drugs .= '
    //     <td width="25%" class="center">
    //         '.formatImage($row['image']).'<br />
    //         '.item_popup($row['itemid'], $row['name']).' [x'.format($row['quantity']).']<br />
    //         '.prettynum($row['cost'], true).'<br />
    //         '.($row['cost'] ? '[<a href="sellitem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Sell</a>]' : '').'
    //         [<a href="putonmarket.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Market</a>]
    //         [<a href="senditem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Send</a>]
    //     </td>';
    //     if (!($drugsCnt % 4)) {
    //         $drugs .= '</tr><tr>';
    //     }
    //     ++$drugsCnt;
    // }
    if (!$row['offense'] && !$row['defense'] && !$row['heal'] && !$row['drugstr'] && !$row['drugspe'] && !$row['drugdef']) {
        $misc .= '
        <td width="25%" class="center">
            '.formatImage($row['image']).'<br />
            '.item_popup($row['itemid'], $row['name']).' [x'.format($row['quantity']).']<br />
            '.prettynum($row['cost'], true).'<br />
            '.($row['cost'] ? '[<a href="sellitem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Sell</a>]' : '').'
            [<a href="putonmarket.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Market</a>]
            [<a href="senditem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Send</a>]
        </td>';
        if (!($miscCnt % 4)) {
            $misc .= '</tr><tr>';
        }
        ++$miscCnt;
    }
    if ($row['reduce']) {
        $misc .= '
        <td width="25%" class="center">
            '.formatImage($row['image']).'<br />
            '.item_popup($row['itemid'], $row['name']).' [x'.format($row['quantity']).']<br />
            '.prettynum($row['cost'], true).'<br />
            '.($row['cost'] ? '[<a href="sellitem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Sell</a>]' : '').'
            [<a href="putonmarket.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Market</a>]
            [<a href="senditem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Send</a>]
            [<a href="useitem.php?id='.$row['itemid'].'&amp;csrfg='.$csrfg.'">Use</a>]
        </td>';
        if (!($miscCnt % 4)) {
            $misc .= '</tr><tr>';
        }
        ++$miscCnt;
    }
}
if ($user_class->cocaine) {
    $drugs .= '
    <td width="25%" class="center">
        '.formatImage('images/noimage.png').'<br />
        Cocaine [x'.format($user_class->cocaine).']<br />
        '.prettynum(0, true).'<br />
        [<a href="drugs.php?use=cocaine&amp;csrfg='.$csrfg.'">Use</a>]
    </td>';
}
if ($user_class->nodoze) {
    $drugs .= '
    <td width="25%" class="center">
        '.formatImage('images/noimage.png').'<br />
        No-Doze [x'.format($user_class->nodoze).']<br />
        '.prettynum(0, true).'<br />
        [<a href="drugs.php?use=nodoze&amp;csrfg='.$csrfg.'">Use</a>]
    </td>';
}
if ($user_class->genericsteroids) {
    $drugs .= '
    <td width="25%" class="center">
        '.formatImage('images/noimage.png').'<br />
        Generic Steroids [x'.format($user_class->genericsteroids).']<br />
        '.prettynum(0, true).'<br />
        [<a href="drugs.php?use=genericsteroids&amp;csrfg='.$csrfg.'">Use</a>]
    </td>';
}
?><tr>
    <th class="content-head">Your Inventory</th>
</tr>
<tr>
    <td class="content">Everything you have collected.</td>
</tr><?php
if ($weapons) {
    ?><tr>
        <th class="content-head">Weapons</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <tr>
                    <?php echo $weapons; ?>
                </tr>
            </table>
        </td>
    </tr><?php
}
if ($armor) {
    ?><tr>
        <th class="content-head">Armor</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <tr>
                    <?php echo $armor; ?>
                </tr>
            </table>
        </td>
    </tr><?php
}
if ($misc) {
    ?><tr>
        <th class="content-head">Miscellaneous</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <tr>
                    <?php echo $misc; ?>
                </tr>
            </table>
        </td>
    </tr><?php
}
if ($drugs) {
    ?><tr>
        <th class="content-head">Drugs</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <tr>
                    <?php echo $drugs; ?>
                </tr>
            </table>
        </td>
    </tr><?php
}
if ($heal) {
    ?><tr>
        <th class="content-head">Healing</th>
    </tr>
    <tr>
        <td class="content">
            <table width="100%" class="pure-table pure-table-horizontal">
                <tr>
                    <?php echo $heal; ?>
                </tr>
            </table>
        </td>
    </tr><?php
}
