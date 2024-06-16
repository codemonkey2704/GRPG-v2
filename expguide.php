<?php
declare(strict_types=1);
require_once __DIR__.'/inc/header.php';
?>
<tr class="top">
    <th class="content-head">Exp Guide</th>
</tr>
<tr class="top">
    <td class="content">
        <table width="100%" border="0">
            <tr class="top">
                <td width="33%">
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 1; $x <= 100; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
                <td width="34%">
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 101; $x <= 200; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
                <td width="33%">
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 201; $x <= 300; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 301; $x <= 400; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
                <td>
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 401; $x <= 500; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
                <td>
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 501; $x <= 600; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 601; $x <= 700; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
                <td>
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 701; $x <= 800; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
                <td>
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 801; $x <= 900; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
            </tr>
            <tr>
                <td>
                    <table width="100%" class="pure-table pure-table-horizontal">
                        <tr class="top">
                            <th width="25%">Level</th>
                            <th width="75%">Exp</th>
                        </tr><?php
                        for ($x = 901; $x <= 1000; ++$x) {
                            if ($x > $user_class->level) {
                                $color = 'orange';
                            } elseif ($x == $user_class->level) {
                                $color = 'lightblue;font-weight:700';
                            } else {
                                $color = 'green';
                            } ?><tr valign="top" style="color:<?php echo $color; ?>;">
                                <td><?php echo format($x); ?></td>
                                <td><?php echo format(experience($x)); ?></td>
                            </tr><?php
                        }
                        ?>
                    </table>
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
        </table>
    </td>
</tr>
