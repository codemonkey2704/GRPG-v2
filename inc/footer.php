<?php
declare(strict_types=1);
if (!defined('GRPG_INC')) {
    exit;
}
if (!isset($db)) {
    require_once __DIR__.'/dbcon.php';
}
$stats = new User_Stats();
if (!defined('LOAD_TIME_END')) {
    define('LOAD_TIME_END', microtime_float());
}
$year = date('Y');
$totaltime = defined('LOAD_TIME_START') ? round(LOAD_TIME_END - LOAD_TIME_START, 3) : 0.01;
ob_start(); ?>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<table width="827">
    <tr>
        <td height="20" colspan="2" align="center" class="content">
            | <a href="citizens.php"><?php echo $stats->playerstotal; ?> Total Mobsters</a>&nbsp; | &nbsp;
            <a href="online.php"><?php echo $stats->playersloggedin; ?> Mobster<?php echo s($stats->playersloggedin); ?> Online</a>&nbsp; | &nbsp;
            <a href="24hour.php"><?php echo $stats->playersonlineinlastday; ?> Mobster<?php echo s($stats->playersonlineinlastday); ?> Online (24 Hours)</a> |<br />
            This page was generated in <?php echo format($totaltime, 3); ?> seconds<br />
            &copy; <?php echo GAME_NAME; ?> 2017<?php echo $year != 2017 ? '-'.$year : ''; ?> gRPG Dev Team
        </td>
    </tr>
</table>
</body>
</html><?php
if(ob_get_level() > 0) {
    ob_end_flush();
}
