<?php
/** @noinspection AutoloadingIssuesInspection */
declare(strict_types=1);
/*DON'T BE A DICK PUBLIC LICENSE

Everyone is permitted to copy and distribute verbatim or modified copies of this license document, and changing it is allowed as long as the name is changed.

DON'T BE A DICK PUBLIC LICENSE TERMS AND CONDITIONS FOR COPYING, DISTRIBUTION AND MODIFICATION

Do whatever you like with the original work, just don't be a dick.

Being a dick includes - but is not limited to - the following instances:

1a. Outright copyright infringement - Don't just copy this and change the name.
1b. Selling the unmodified original with no work done what-so-ever, that's REALLY being a dick.
1c. Modifying the original work to contain hidden harmful content. That would make you a PROPER dick.

If you become rich through modifications, related works/services, or supporting the original work, share the love. Only a dick would make loads off this work and not buy the original works creator(s) a pint.

Code is provided with no warranty. Using somebody else's code and bitching when it goes wrong makes you a DONKEY dick. Fix the problem yourself. A non-dick would submit the fix back.
*/
if (!defined('GRPG_INC')) {
    exit;
}

class database extends PDO
{
    protected string $last_query;
    private static $host = '';
    private static $user = '';
    private static $pass = '';
    private static $name = '';
    private ?string $error = null;
    public ?array $binds = [];
    private ?PDO $db;
    private ?PDOStatement $stmt;
    public static ?database $inst = null;

    /**
     *    Initialize once and once only.
     *
     * @return object
     */
    public static function getInstance()
    {
        return self::$inst = new self();
    }

    /**
     *    Connect to database.
     */
    public function __construct()
    {
        mb_internal_encoding('UTF-8');
        mb_regex_encoding('UTF-8');
        self::$host = getenv('MYSQL_HOST');
        self::$user = getenv('MYSQL_USER');
        self::$pass = getenv('MYSQL_PASS');
        self::$name = getenv('MYSQL_BASE');
        try {
            $opts = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $this->db = parent::__construct('mysql:host=' . static::$host . ';dbname=' . static::$name . ';charset=utf8',
                static::$user, static::$pass, $opts);
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>CONSTRUCT ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
    }

    /**
     *    Kill database connection.
     */
    public function __destruct()
    {
        $this->db = null;
    }

    /**
     *    Prepare query.
     *
     * @param string $query [MySQL database query]
     * @param array|null $params
     *
     * @return bool
     *
     * @noinspection PhpSignatureMismatchDuringInheritanceInspection
     */
    public function query($query, array $params = null): ?bool
    {
        $this->last_query = $query;
        try {
            $this->stmt = $this->prepare($query);
            if (is_array($params) && count($params)) {
                return $this->execute($params);
            }
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>QUERY ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
        return null;
    }

    /**
     *    Bind values to prepared query.
     *
     * @param string $param [unique hard-coded parameter identifier]
     * @param mixed $value [a value to bind to prepared query]
     * @param int $type [PDO constants - https://php.net/manual/en/pdo.constants.php]
     */
    public function bind($param, $value, $type = null): void
    {
        if ($type === null) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case $value === null:
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }
        } else {
            switch ($type) {
                case 'int':case 'float':
                    $type = PDO::PARAM_INT;
                    break;
                case 'str':
                case 'string':
                    $type = PDO::PARAM_STR;
                    break;
                case 'null':
                    $type = PDO::PARAM_NULL;
                    break;
                case 'bool':
                    $type = PDO::PARAM_BOOL;
                    break;
            }
        }
        try {
            $this->stmt->bindValue($param, $value, $type);
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>BIND ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
    }

    /**
     *    Execute a prepared query.
     *
     * @param array $binds [in order of "appearance" within the prepared query]
     * @return bool
     */
    public function execute(array $binds = null): ?bool
    {
        $this->binds = $binds;
        if (!isset($this->stmt)) {
            return false;
        }
        try {
            return (bool)is_array($binds) && count($binds) > 0 ? $this->stmt->execute($binds) : $this->stmt->execute();
        } catch (PDOException $e) {
            echo '<p style="color:red;"><strong>EXECUTION ERROR</strong></p><pre>' . $e->getMessage() . '</pre><p><pre>';
            /** @noinspection ForgottenDebugOutputInspection */
            var_dump($this->stmt->debugDumpParams());
            echo '</pre></p>';
            exit;
        }
    }

    /**
     *    Return single or multiple rows.
     *
     * @param bool $shift [shift the returned array of data up a step - loses the requirement of a loop for one 1 row]
     *
     * @return array
     */
    public function fetch($shift = false): ?array
    {
        if (!isset($this->stmt)) {
            return null;
        }
        try {
            $this->execute();
            $ret = $this->stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>FETCH ROW ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
        if(empty($ret)) {
            return null;
        }
        if ($shift) {
            $ret = array_shift($ret);
        }

        return $ret;
    }

    /**
     *    Returns a single field.
     *
     * @param int $col
     *
     * @return mixed
     */
    public function result($col = 0)
    {
        if (!isset($this->stmt)) {
            return null;
        }
        try {
            $this->execute();

            return $this->stmt->fetchColumn($col);
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>FETCH SINGLE ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
    }

    /**
     *    Returns an objective array of data.
     *
     * @return object
     */
    public function fetchObj()
    {
        if (!isset($this->stmt)) {
            return null;
        }
        try {
            $this->execute();

            return $this->stmt->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>FETCH OBJECT ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
    }

    /**
     *    Returns amount of rows affected by single previous query.
     *
     * @return int|null
     */
    public function affected(): ?int
    {
        try {
            return $this->stmt->rowCount();
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>AFFECTED ROWS ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
    }

    /**
     *    Returns the amount of rows found from single previous query.
     *
     * @return int
     */
    public function count(): ?int
    {
        try {
            return (int)$this->stmt->fetchColumn();
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>NUM ROWS ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
    }

    /**
     *    Returns ID inserted from auto_increment'd column from single previous query.
     *
     * @return int|null
     */
    public function id(): ?int
    {
        try {
            $id = $this->lastInsertId();
            return $id > 0 ? (int)$id : null;
        } catch (PDOException $e) {
            exit('<p style="color:red;"><strong>LAST INSERT ID ERROR</strong></p><pre>' . $e->getMessage() . '</pre>');
        }
    }

    /**
     *    Begin, end, or rollback a PDO transaction.
     *
     * @param string $which [[start|end|cancel] - pretty obvious for what they stand]
     *
     * @return  void
     */
    public function trans($which): void
    {
        $opts = [
            'start' => 'beginTransaction',
            'end' => 'commit',
            'cancel' => 'rollBack'
        ];
        $function = $opts[$which];
        try {
            $this->$function();
        } catch (Exception $e) {
            exit('<strong>' . strtoupper($which) . ' TRANSACTION ERROR:</strong> ' . $e->getMessage());
        }
    }

    /**
     *    "Internal-ish" error function for queries - can be called in-file.
     *
     * @return string|null
     */
    public function error(): ?string
    {
        echo '<pre>';
        /** @noinspection ForgottenDebugOutputInspection */
        var_dump($this->stmt->debugDumpParams());
        echo '</pre>';
    }

    // Helper function(s)

    /**
     *    Truncate an array of tables.
     *
     * @param array $tables [array list of table names]
     * @param bool $trans [true if in part of an existing transaction, false[default] if not]
     * @return bool
     */
    public function truncate(array $tables = null, $trans = false): bool
    {
        if (!is_array($tables) || !count($tables)) {
            return false;
        }
        if (!$trans) {
            $this->trans('start');
        }
        foreach ($tables as $table) {
            $this->exec('TRUNCATE TABLE `' . $table . '`');
        }
        if (!$trans) {
            $this->trans('end');
        }
        return true;
    }

    /**
     *    Check if a table exists.
     *
     * @param string $table [a single table name]
     *
     * @return bool
     */
    public function tableExists($table): bool
    {
        try {
            $result = $this->query('SELECT 1 FROM ' . $table . ' LIMIT 1');
        } catch (Exception $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e->getMessage());
            return false;
        }

        return $result !== false;
    }

    /**
     *    Check if a column exists within a table.
     *
     * @param string $column [a column name]
     * @param string $table [the parent table name]
     *
     * @return bool
     */
    public function columnExists($column, $table): bool
    {
        try {
            $result = $this->db->query('SHOW COLUMNS FROM `' . $table . '` WHERE `Fieldname` = "' . $column . '"');
        } catch (Exception $e) {
            /** @noinspection ForgottenDebugOutputInspection */
            error_log($e->getMessage());
            return false;
        }

        return $result !== false;
    }
    public function exists(string $table, string $column, $criteria = ''): bool
    {
        $query = 'SELECT COUNT('.$column.') FROM '.$table;
        $params = [];
        if($criteria !== '') {
            $query .= ' WHERE '.$column.' = ?';
            $params[] = $criteria;
        }
        $this->query($query);
        $this->execute($params);
        return (bool)$this->result();
    }
}

$db = database::getInstance();
