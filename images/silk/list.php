<?php
declare(strict_types=1);
$files = glob('*.png');
$cnt = 1;
?><table width="100%">
    <tr><?php
foreach ($files as $file) {
    ?><td><img src="<?php echo $file; ?>" /><br /><span style="font-size:0.8em;"><?php echo $file; ?></span></td><?php
    if (!($cnt % 10)) {
        echo '</tr><tr>';
    }
    ++$cnt;
}
?>  </tr>
</table>
