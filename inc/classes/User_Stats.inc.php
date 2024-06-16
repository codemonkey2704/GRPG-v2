<?php
declare(strict_types=1);
if(!defined('GRPG_INC')) {
    exit;
}
class User_Stats
{
    public int $playersloggedin = 0;
    public int $playersonlineinlastday = 0;
    public int $playerstotal = 0;

    public function __construct()
    {
        global $db;
        $db->query('SELECT id, UNIX_TIMESTAMP(lastactive) AS last_active_nix FROM users ORDER BY id ');
        $db->execute();
        $rows = $db->fetch();
        foreach ($rows as $row) {
            ++$this->playerstotal;
            $deficit = time() - $row['last_active_nix'];
            if ($deficit <= 300) {
                ++$this->playersloggedin;
            }
            if ($deficit <= 86400) {
                ++$this->playersonlineinlastday;
            }
        }
    }
}
