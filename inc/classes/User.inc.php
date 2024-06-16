<?php
declare(strict_types=1);
if(!defined('GRPG_INC')) {
    exit;
}

class User
{
    /**
     * @var int
     */
    public int $cocaine;
    /**
     * @var int
     */
    public int $houseawake = 0;
    /**
     * @var int
     */
    public int $eqweapon = 0;
    /**
     * @var int
     */
    public int $eqarmor = 0;
    /**
     * @var int
     */
    public int $id = 0;
    /**
     * @var int
     */
    public int $level = 0;
    /**
     * @var int
     */
    public int $city = 0;
    /**
     * @var int
     */
    public int $marijuana = 0;
    /**
     * @var int
     */
    public int $potseeds = 0;
    /**
     * @var int
     */
    public int $nodoze = 0;
    /**
     * @var int
     */
    public int $genericsteroids = 0;
    /**
     * @var int
     */
    public int $hookers = 0;
    /**
     * @var int
     */
    public int $ban = 0;
    /**
     * @var int
     */
    public int $age_int = 0;
    /**
     * @var int
     */
    public int $rmdays = 0;
    /**
     * @var int
     */
    public int $house = 0;
    /**
     * @var int
     */
    public int $admin = 0;
    /**
     * @var int
     */
    public int $gang = 0;
    /**
     * @var int
     */
    public int $whichbank = 0;
    /**
     * @var int
     */
    public int $gangleader = 0;
    /**
     * @var int
     */
    public int $jail = 0;
    /**
     * @var int
     */
    public int $job = 0;
    /**
     * @var int
     */
    public int $hospital = 0;
    /**
     * @var int
     */
    public int $searchdowntown = 0;
    /**
     * @var string
     */
    public string $weaponname = '';
    /**
     * @var string
     */
    public string $weaponimg = '';
    /**
     * @var string
     */
    public string $armorname = '';
    /**
     * @var string
     */
    public string $armorimg = '';
    /**
     * @var string
     */
    public string $housename = '';
    /**
     * @var string
     */
    public string $ip = '';
    /**
     * @var string
     */
    public string $cityname = '';
    /**
     * @var string
     */
    public string $citydesc = '';
    /**
     * @var string
     */
    public string $signature = '';
    /**
     * @var string
     */
    public string $username = '';
    /**
     * @var string
     */
    public string $gender = '';
    /**
     * @var string
     */
    public string $formattedexp = '';
    /**
     * @var string
     */
    public string $formattedhp = '';
    /**
     * @var string
     */
    public string $formattedenergy = '';
    /**
     * @var string
     */
    public string $formattednerve = '';
    /**
     * @var string
     */
    public string $formattedawake = '';
    /**
     * @var string
     */
    public string $lastactive = '';
    /**
     * @var string
     */
    public string $signuptime = '';
    /**
     * @var string
     */
    public string $age = '';
    /**
     * @var string
     */
    public string $formattedlastactive = '';
    /**
     * @var string
     */
    public string $email = '';
    /**
     * @var string
     */
    public string $quote = '';
    /**
     * @var string
     */
    public string $avatar = '';
    /**
     * @var string
     */
    public string $class = '';
    /**
     * @var string
     */
    public string $gangname = '';
    /**
     * @var string
     */
    public string $gangtag = '';
    /**
     * @var string
     */
    public string $gangdescription = '';
    /**
     * @var string
     */
    public string $formattedgang = '';
    /**
     * @var string
     */
    public string $formattedname = '';
    /**
     * @var string
     */
    public string $type = '';
    /**
     * @var string
     */
    public string $formattedonline = '';
    /**
     * @var float
     */
    public float $weaponoffense = 0;
    /**
     * @var float
     */
    public float $armordefense = 0;
    /**
     * @var float
     */
    public float $speedbonus = 0;
    /**
     * @var float
     */
    public float $exp = 0;
    /**
     * @var float
     */
    public float $maxexp = 0;
    /**
     * @var float
     */
    public float $exppercent = 0;
    /**
     * @var float
     */
    public float $money = 0;
    /**
     * @var float
     */
    public float $bank = 0;
    /**
     * @var float
     */
    public float $maxhp = 0;
    /**
     * @var float
     */
    public float $energy = 0;
    /**
     * @var float
     */
    public float $maxenergy = 0;
    /**
     * @var float
     */
    public float $nerve = 0;
    /**
     * @var float
     */
    public float $maxnerve = 0;
    /**
     * @var float
     */
    public float $awake = 0;
    /**
     * @var float
     */
    public float $maxawake = 0;
    /**
     * @var float
     */
    public float $hppercent = 0;
    /**
     * @var float
     */
    public float $energypercent = 0;
    /**
     * @var float
     */
    public float $nervepercent = 0;
    /**
     * @var float
     */
    public float $awakepercent = 0;
    /**
     * @var float
     */
    public float $workexp = 0;
    /**
     * @var float
     */
    public float $strength = 0;
    /**
     * @var float
     */
    public float $defense = 0;
    /**
     * @var float
     */
    public float $speed = 0;
    /**
     * @var float
     */
    public float $totalattrib = 0;
    /**
     * @var float
     */
    public float $battlewon = 0;
    /**
     * @var float
     */
    public float $battlelost = 0;
    /**
     * @var float
     */
    public float $battletotal = 0;
    /**
     * @var float
     */
    public float $battlemoney = 0;
    /**
     * @var float
     */
    public float $crimesucceeded = 0;
    /**
     * @var float
     */
    public float $crimefailed = 0;
    /**
     * @var float
     */
    public float $crimetotal = 0;
    /**
     * @var float
     */
    public float $crimemoney = 0;
    /**
     * @var float
     */
    public float $points = 0;
    /**
     * @var float
     */
    public float $hp = 0;
    /**
     * @var float
     */
    public float $moddedstrength = 0;
    /**
     * @var float
     */
    public float $moddedspeed = 0;
    /**
     * @var float
     */
    public float $moddeddefense = 0;
    /**
     * @var bool
     */
    public bool $validated;


    public function __construct($id = 0)
    {
        global $db;
        if (!is_numeric($id)) {
            code_error('Invalid ID passed to User');
            return;
        }
        $db->query('SELECT * FROM users WHERE id = ?', [$id]);
        $row = $db->fetch(true);
        if ($row === null) {
            return;
        }
        $db->query('SELECT COUNT(id) FROM pending_validations WHERE username = ?', [$row['username']]);
        $validated = (bool)$db->result() !== true;
        $equipment = [];
        $equipArray = [$row['eqweapon'], $row['eqarmor']];
        $equipIDs = array_filter($equipArray, static function ($key) {
            return $key !== 0;
        });
        if(count($equipIDs) > 0) {
            $db->query('SELECT id, offense, defense, name, image FROM items WHERE id IN ('.implode(', ', $equipIDs).')');
            $db->execute();
            $equipRows = $db->fetch();
            if($equipRows !== null) {
                foreach ($equipRows as $equip_row) {
                    $equipment[$equip_row['id']] = $equip_row;
                }
            }
        }
        if (isset($equipment[$row['eqweapon']])) {
            $wep = $equipment[$row['eqweapon']];
            $this->eqweapon = (int)$wep['id'];
            $this->weaponoffense = (int)$wep['offense'];
            $this->weaponname = $wep['name'];
            $this->weaponimg = $wep['image'];
        }
        if (isset($equipment[$row['eqarmor']])) {
            $arm = $equipment[$row['eqarmor']];
            $this->eqarmor = (int)$arm['id'];
            $this->armordefense = (int)$arm['defense'];
            $this->armorname = $arm['name'];
            $this->armorimg = $arm['image'];
        }
        $this->cocaine = (int)$row['cocaine'];
        $db->query('SELECT COUNT(id) FROM effects WHERE userid = ? AND LOWER(effect) = \'cocaine\'', [$id]);
        $this->cocaine += $db->result();
        if ($this->cocaine > 0) {
            $this->speedbonus = floor($row['speed'] * .3);
        }
        $db->query('SELECT id, name, description FROM cities WHERE id = ?', [$row['city']]);
        $city = $db->fetch(true);
        $db->query('SELECT id, name, awake FROM houses WHERE id = ?', [$row['house']]);
        $house = $db->fetch(true) ?? ['name' => '', 'awake' => 100];
        $this->housename = $house['name'];
        $this->houseawake = (int) $house['awake'];
        $this->id = (int)$row['id'];
        $this->ip = $row['ip'];
        $this->exp = (float)$row['experience'];
        $this->level = Get_The_Level($this->exp);
        $this->maxexp = experience($this->level + 1);
        $this->exppercent = (float)($this->exp > 0 && $this->maxexp > 0 ? floor(($this->exp / $this->maxexp) * 100) : 0);
        $this->city = (int)$row['city'];
        if($city !== null) {
            $this->cityname = $city['name'];
            $this->citydesc = $city['description'];
        }
        $this->signature = $row['signature'];
        $this->username = $row['username'];
        $this->marijuana = (int)$row['marijuana'];
        $this->potseeds = (int)$row['potseeds'];
        $this->nodoze = (int)$row['nodoze'];
        $this->genericsteroids = (int)$row['genericsteroids'];
        $this->hookers = (int)$row['hookers'];
        $this->money = (float)$row['money'];
        $this->bank = (float)$row['bank'];
        $this->gender = $row['gender'];
        $this->whichbank = (int)$row['whichbank'];
        $this->ban = (int)$row['ban'];
        $this->hp = (float)$row['hp'];
        $this->maxhp = $this->level * 50;
        $this->energy = (float)$row['energy'];
        $this->maxenergy = $this->level + 9;
        $this->nerve = (float)$row['nerve'];
        $this->maxnerve = $this->level + 4;
        $this->awake = (float)$row['awake'];
        $this->maxawake = (float)$this->houseawake;
        $this->hppercent = $this->hp > 0 ? floor(($this->hp / $this->maxhp) * 100) : 0;
        $this->energypercent = $this->energy > 0 ? floor(($this->energy / $this->maxenergy) * 100) : 0;
        $this->nervepercent = $this->nerve > 0 ? floor(($this->nerve / $this->maxnerve) * 100) : 0;
        $this->awakepercent = $this->awake > 0 ? floor(($this->awake / $this->maxawake) * 100) : 0;
        $this->formattedexp = $this->exp.' / '.$this->maxexp.' ['.$this->exppercent.'%]';
        $this->formattedhp = $this->hp.' / '.$this->maxhp.' ['.$this->hppercent.'%]';
        $this->formattedenergy = $this->energy.' / '.$this->maxenergy.' ['.$this->energypercent.'%]';
        $this->formattednerve = $this->nerve.' / '.$this->maxnerve.' ['.$this->nervepercent.'%]';
        $this->formattedawake = $this->awake.' / '.$this->maxawake.' ['.$this->awakepercent.'%]';
        $this->workexp = (float)$row['workexp'];
        $this->strength = (float)$row['strength'];
        $this->defense = (float)$row['defense'];
        $this->speed = (float)$row['speed'];
        $this->totalattrib = $this->speed + $this->strength + $this->defense;
        $this->battlewon = (float)$row['battlewon'];
        $this->battlelost = (float)$row['battlelost'];
        $this->battletotal = $this->battlewon + $this->battlelost;
        $this->battlemoney = (float)$row['battlemoney'];
        $this->crimesucceeded = (float)$row['crimesucceeded'];
        $this->crimefailed = (float)$row['crimefailed'];
        $this->crimetotal = $this->crimesucceeded + $this->crimefailed;
        $this->crimemoney = (float)$row['crimemoney'];
        $this->lastactive = $row['lastactive'];
        $this->signuptime = $row['signuptime'];
        $this->age = howlongago($this->signuptime);
        $this->age_int = time() - strtotime($this->signuptime);
        $this->formattedlastactive = howlongago($this->lastactive, true);
        $this->points = (float)$row['points'];
        $this->rmdays = (int)$row['rmdays'];
        $this->house = (int)$row['house'];
        $this->email = $row['email'];
        $this->admin = (int)$row['admin'];
        $this->quote = $row['quote'];
        $this->avatar = $row['avatar'];
        $this->gang = (int)$row['gang'];
        $this->class = $row['class'];
        $this->validated = $validated;
        if ($this->gang) {
            $db->query('SELECT id, name, leader, tag, description FROM gangs WHERE id = ?', [$this->gang]);
            $gang = $db->fetch(true);
            if ($gang !== null) {
                $this->gangname = $gang['name'];
                $this->gangleader = (int)$gang['leader'];
                $this->gangtag = $gang['tag'];
                $this->gangdescription = $gang['description'];
                $this->formattedgang = '<a href="viewgang.php?id='.$this->gang.'">'.$this->gangname.'</a>';
                $this->formattedname = '<a href="viewgang.php?id='.$this->gang.'" '.($this->gangleader === $this->id ? 'title="Gang Leader">[<strong>'.$this->gangtag.'</strong>]' : '>['.$this->gangtag.']').'</a> ';
            } else {
                generate_ticket('Non-existent gang', 'ID: '.$this->gang);
            }
        }
        $this->jail = (int)$row['jail'];
        $this->job = (int)$row['job'];
        $this->hospital = (int)$row['hospital'];
        $this->searchdowntown = (int)$row['searchdowntown'];
        $this->moddedstrength = $this->strength * ($this->weaponoffense * .01 + 1);
        $this->moddedspeed = $this->speed + $this->speedbonus;
        $this->moddeddefense = $this->defense * ($this->armordefense * .01 + 1);
        $this->type = '';
        $color = '';
        if ($this->rmdays !== 0) {
            $this->type = 'Respected Mobster';
            $color = 'green';
        } else {
            $this->type = 'Regular Mobster';
        }
        if ($this->admin === 1) {
            $this->type = 'Admin';
            $color = 'blue';
        }
        if ($this->admin === 2) {
            $this->type = 'Staff';
        }
        if ($this->admin === 3) {
            $this->type = 'To be filled';
            $color = 'red';
        }
        if ($this->admin === 4) {
            $this->type = 'Congress';
            $color = 'red';
        }
        if ($this->rmdays > 0) {
            $this->formattedname .= '<a href="profiles.php?id='.$this->id.'" style="font-weight:700;'.($color !== '' ? 'color:'.$color.';' : '').'" title="Respected Mobster ['.$this->rmdays.' RM Day'.s($this->rmdays).' Left]">'.$this->username.'</a>';
        } elseif ($this->admin > 0) {
            $this->formattedname .= '<a href="profiles.php?id='.$this->id.'" style="font-weight:700;'.($color !== '' ? 'color:'.$color.';' : '').'">'.$this->username.'</a>';
        } else {
            $this->formattedname .= '<a href="profiles.php?id='.$this->id.'" '.($color !== '' ? 'style="color:'.$color.';"' : '').'>'.$this->username.'</a>';
        }
        if((time() - strtotime($this->lastactive)) <= 300) {
            $conf = [
                'color' => 'green',
                'text' => '[online]',
            ];
        } else {
            $conf = [
                'color' => 'red',
                'text' => 'offline',
            ];
        }
        $this->formattedonline = '<span style="color:'.$conf['color'].';padding:2px;font-weight:bold;">'.$conf['text'].'</span>';
        if ($this->exp >= $this->maxexp) {
            ++$this->level;
            $expRemaining = $this->exp - $this->maxexp;
            $db->query('UPDATE users SET level = ?, experience = ? WHERE id = ?', [$this->level, $expRemaining, $this->id]);
        }
    }
}
