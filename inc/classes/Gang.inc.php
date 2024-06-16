<?php
declare(strict_types=1);
if(!defined('GRPG_INC')) {
    exit;
}
class Gang
{
    public int $id;
    public int $leader;
    public int $members;
    public int $level;
    public float $exp;
    public float $moneyvault;
    public float $pointsvault;
    public string $name;
    public string $tag;
    public string $formattedleader;
    public string $formattedname;
    public string $description;
    protected ?database $db = null;
    public function __construct(int $id = 0, bool $getLeader = false)
    {
        global $db;
        $this->db = $db;
        if (!$id) {
            code_error('Invalid argument passed to gang');
            return;
        }
        $db->query('SELECT * FROM gangs WHERE id = ?', [$id]);
        $row = $db->fetch(true);
        if ($row === null) {
            return;
        }
        $this->id = (int)$row['id'];
        $this->members = $this->getMemberCount();
        $this->name = $row['name'];
        $this->description = $row['description'];
        $this->leader = (int)$row['leader'];
        $this->tag = $row['tag'];
        $this->exp = (int)$row['experience'];
        $this->level = (int)Get_The_Level($row['experience']);
        $this->moneyvault = (int)$row['moneyvault'];
        $this->pointsvault = (int)$row['pointsvault'];
        $this->formattedname = '<a href="viewgang.php?id='.$id.'">['.$row['tag'].'] '.format($row['name']).'</a>';
        if ($getLeader === true) {
            $this->setLeader();
        }
    }
    public function getMemberCount(): int
    {
        $this->db->query('SELECT COUNT(id) FROM users WHERE gang = ?', [$this->id]);
        return (int)$this->db->result();
    }
    public function setLeader(): void
    {
        $leader = new User($this->leader);
        $this->formattedleader = $leader->formattedname;
    }
    public function getLeader(): string
    {
        return $this->formattedleader;
    }
}
