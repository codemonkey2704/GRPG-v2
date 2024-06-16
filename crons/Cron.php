<?php
declare(strict_types=1);

class Cron
{
    public database $db;
    private array $conf;
    private int $currentTime;

    public function __construct(database $db)
    {
        $this->db = $db;
        $this->conf = [
            '1min' => [
                'function-name' => 'runOneMinute',
                'interval' => 60,
            ],
            '5min' => [
                'function-name' => 'runFiveMinute',
                'interval' => 300,
            ],
            '1hour' => [
                'function-name' => 'runOneHour',
                'interval' => 3600,
            ],
            '1day' => [
                'function-name' => 'runOneDay',
                'interval' => 86400,
            ],
        ];
        $this->currentTime = time();
    }

    /**
     * @param string $cron
     *
     * @return array|null
     */
    public function isDue(string $cron): ?array
    {
        if (!array_key_exists($cron, $this->conf)) {
            return null;
        }
        $this->db->query('SELECT lastdone FROM updates WHERE name = ?', [$cron]);
        $this->db->execute();
        $update = $this->db->result();
        $timeSinceUpdate = $this->currentTime - $update;
        if ($timeSinceUpdate >= $this->conf[$cron]['interval']) {
            return [
                'timeSinceUpdate' => $timeSinceUpdate,
                'iterationCount' => floor($timeSinceUpdate / $this->conf[$cron]['interval']),
            ];
        }
        return null;
    }

    public function runCron(string $cron, array $data): void
    {
        if (!array_key_exists($cron, $this->conf)) {
            return;
        }
        $func = $this->conf[$cron]['function-name'];
        $this->$func($data);
        $this->postRunCommands($cron, $data);
    }

    private function postRunCommands(string $cron, array $data): void
    {
        $leftOverTime = $data['timeSinceUpdate'] - (floor($data['timeSinceUpdate'] / $this->conf[$cron]['interval']) * $this->conf[$cron]['interval']);
        $this->db->query('UPDATE updates SET lastdone = ? WHERE name = ?', [$this->currentTime, $cron]);
        if ($leftOverTime > 0) {
            $this->db->query('UPDATE updates SET lastdone = ? WHERE name = ?',
                [$this->currentTime - $leftOverTime, $cron]);
        }
    }

    private function runOneMinute(array $data): void
    {
        $seconds = $data['iterationCount'] * $this->conf['1min']['interval'];
        $this->db->trans('start');
        $this->db->query('UPDATE users SET hospital = IF(hospital > 0, GREATEST(hospital - ?, 0), 0), jail = IF(jail > 0, GREATEST(jail - ?, 0), 0)',
            [$seconds, $seconds]);
        $this->db->query('UPDATE effects SET timeleft = GREATEST(timeleft - ?, 0)', [$data['iterationCount']]);
        $this->db->query('DELETE FROM effects WHERE timeleft = 0');
        $this->db->execute();
        $this->db->trans('end');
    }

    private function runFiveMinute(array $data): void
    {
        $this->updateStocks();
        $this->db->query('SELECT id, rmdays FROM users');
        $this->db->execute();
        $users = $this->db->fetch();
        $this->db->trans('start');
        foreach ($users as $row) {
            $user = new User($row['id']);
            $multiplier = $row['rmdays'] ? 2 : 1;
            $this->db->query('
                UPDATE users SET
                    awake = LEAST(awake + ?, ?),
                    energy = LEAST(energy + ?, ?),
                    nerve = LEAST(nerve + ?, ?),
                    hp = LEAST(hp + ?, ?)
                WHERE id = ?
            ', [
                ceil(($data['iterationCount'] * 5) * $multiplier),
                $user->maxawake,
                ceil(($data['iterationCount'] * 2) * $multiplier),
                $user->maxenergy,
                ceil(($data['iterationCount'] * 2) * $multiplier),
                $user->maxnerve,
                ceil(($data['iterationCount'] * 10) * $multiplier),
                $user->maxhp,
                $row['id'],
            ]);
        }
        $this->db->trans('end');
    }

    private function updateStocks(): void
    {
        $this->db->query('SELECT id, cost FROM stocks ORDER BY id ');
        $this->db->execute();
        $rows = $this->db->fetch();
        if ($rows !== null) {
            $this->db->trans('start');
            foreach ($rows as $row) {
                $len = strlen($row['cost']);
                $amount = mt_rand($len * -1, $len);
                $this->db->query('UPDATE stocks SET cost = GREATEST(cost + ?, 1) WHERE id = ?');
                $this->db->execute([$amount, $row['id']]);
            }
            $this->db->trans('end');
        }
    }

    private function runOneHour(array $data): void
    {
        $this->updateStocks();
    }

    private function runOneDay(array $data): void
    {
        $this->doGrow();
        $this->doLottery();

        $this->db->query('SELECT users.id, jobs.money AS wage
            FROM users
            LEFT JOIN jobs ON job = jobs.id
        ');
        $this->db->execute();
        $rows = $this->db->fetch();
        $this->db->trans('start');
        foreach ($rows as $row) {
            $updates_user = new User($row['id']);
            $interest = $updates_user->rmdays ? .04 : .02;
            $bank = ceil($updates_user->bank * $interest);
            $money = (int) $row['wage'];
            if ($updates_user->hookers) {
                $money += $updates_user->hookers * 300;
            }
            $this->db->query('UPDATE users SET money = money + ?, rmdays = GREATEST(rmdays - 1, 0), bank = IF(bank > 0, bank + ?, bank), searchdowntown = 100 WHERE id = ?');
            $this->db->execute([$money, $bank, $row['id']]);
        }
        $this->db->trans('end');
    }

    private function doGrow()
    {
        $this->db->query('SELECT * FROM growing ORDER BY userid');
        $this->db->execute();
        $rows = $this->db->fetch();
        if ($rows !== null) {
            $this->db->trans('start');
            foreach ($rows as $row) {
                $lost = floor(mt_rand(0, $row['amount'] * 5));
                if ($lost > 0) {
                    if ($lost > $row['amount']) {
                        $this->db->query('DELETE FROM growing WHERE id = ?');
                        $this->db->execute([$row['id']]);
                        Give_Land($row['cityid'], $row['userid'], $row['amount']);
                        $extra = 'All ';
                    } else {
                        $this->db->query('UPDATE growing SET cropamount = GREATEST(cropamount - ?, 0) WHERE id = ?');
                        $this->db->execute([$lost, $row['id']]);
                        $extra = '';
                    }
                    Send_Event($row['userid'], $extra . format($lost) . ' of your ' . format($row['croptype']) . ' have died. Crop ID: ' . format($row['id']));
                }
            }
            $this->db->trans('end');
        }
    }

    private function doLottery()
    {
        $this->db->query('SELECT COUNT(userid) FROM lottery');
        $tickets = $this->db->result();
        $lotto = $tickets * 750;
        if ($lotto < 1) {
            return;
        }
        $this->db->query('SELECT userid FROM lottery ORDER BY RAND() LIMIT 1');
        $this->db->execute();
        $winner = $this->db->result();
        $this->db->trans('start');
        $this->db->query('UPDATE users SET money = money + ? WHERE id = ?');
        $this->db->execute([$lotto, $winner]);
        Send_Event($winner, 'Congratulations! You\'ve won the lottery! You won ' . prettynum($lotto, true));
        $this->db->query('TRUNCATE TABLE lottery');
        $this->db->execute();
        $this->db->trans('end');
    }
}
