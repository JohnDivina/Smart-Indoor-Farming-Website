<?php
date_default_timezone_set("Asia/Manila");

// Check for PostgreSQL environment variables (Neon PostgreSQL / Vercel / Railway)
$db_url = getenv('DATABASE_URL') ?: getenv('POSTGRES_URL');
$pghost = getenv('PGHOST') ?: getenv('POSTGRES_HOST');

if ($db_url || $pghost) {
    class SmartFarmPostgresResult {
        private $rows;
        private $pointer = 0;
        public $num_rows = 0;

        public function __construct($rows) {
            $this->rows = is_array($rows) ? $rows : [];
            $this->num_rows = count($this->rows);
        }

        public function fetch_assoc() {
            if ($this->pointer < $this->num_rows) {
                return $this->rows[$this->pointer++];
            }
            return null;
        }

        public function fetch_all($mode = 1) {
            return $this->rows;
        }
    }

    class SmartFarmPostgresDB {
        public $pdo;
        public $connect_error = null;
        public $insert_id = 0;

        public function __construct($dsn, $user, $password) {
            try {
                $this->pdo = new PDO($dsn, $user, $password, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]);
            } catch (PDOException $e) {
                $this->connect_error = $e->getMessage();
            }
        }

        private function cleanSql($sql) {
            // Translate MySQL backticks to PostgreSQL double quotes
            $sql = str_replace('`', '"', $sql);
            // Replace RAND() with RANDOM()
            $sql = preg_replace('/\bNOW\(\)/i', 'CURRENT_TIMESTAMP', $sql);
            return $sql;
        }

        public function query($sql) {
            if ($this->connect_error) return false;
            $cleanSql = $this->cleanSql($sql);
            try {
                $trimmed = ltrim(strtoupper($cleanSql));
                if (strpos($trimmed, 'SELECT') === 0 || strpos($trimmed, 'SHOW') === 0 || strpos($trimmed, 'EXPLAIN') === 0) {
                    $stmt = $this->pdo->query($cleanSql);
                    $rows = $stmt->fetchAll();
                    return new SmartFarmPostgresResult($rows);
                } else {
                    $affected = $this->pdo->exec($cleanSql);
                    if (strpos($trimmed, 'INSERT') === 0) {
                        try {
                            $this->insert_id = (int)$this->pdo->lastInsertId();
                        } catch (Exception $ex) {
                            $this->insert_id = 0;
                        }
                    }
                    return true;
                }
            } catch (Exception $e) {
                error_log("PG Query Error: " . $e->getMessage() . " | SQL: " . $cleanSql);
                return false;
            }
        }

        public function prepare($sql) {
            if ($this->connect_error) return false;
            $cleanSql = $this->cleanSql($sql);
            try {
                return $this->pdo->prepare($cleanSql);
            } catch (Exception $e) {
                error_log("PG Prepare Error: " . $e->getMessage() . " | SQL: " . $cleanSql);
                return false;
            }
        }
    }

    // Connect to PostgreSQL (Neon)
    if ($db_url) {
        $parsed = parse_url($db_url);
        $user = isset($parsed['user']) ? urldecode($parsed['user']) : '';
        $pass = isset($parsed['pass']) ? urldecode($parsed['pass']) : '';
        $host = isset($parsed['host']) ? $parsed['host'] : 'localhost';
        $port = isset($parsed['port']) ? $parsed['port'] : '5432';
        $path = isset($parsed['path']) ? ltrim($parsed['path'], '/') : 'neondb';
        $dsn = "pgsql:host=$host;port=$port;dbname=$path;sslmode=require";
    } else {
        $host = $pghost;
        $user = getenv('PGUSER') ?: getenv('POSTGRES_USER');
        $pass = getenv('PGPASSWORD') ?: getenv('POSTGRES_PASSWORD');
        $dbname = getenv('PGDATABASE') ?: getenv('POSTGRES_DATABASE') ?: 'neondb';
        $port = getenv('PGPORT') ?: '5432';
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    }

    $conn = new SmartFarmPostgresDB($dsn, $user, $pass);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} else {
    // Local MySQL / XAMPP fallback
    $servername = getenv('MYSQLHOST') ?: (getenv('MYSQL_HOST') ?: 'localhost');
    $username   = getenv('MYSQLUSER') ?: (getenv('MYSQL_USER') ?: 'root');
    $password   = getenv('MYSQLPASSWORD') ?: (getenv('MYSQL_PASSWORD') ?: '');
    $dbname     = getenv('MYSQLDATABASE') ?: (getenv('MYSQL_DATABASE') ?: 'smartfarm');
    $port       = (int)(getenv('MYSQLPORT') ?: (getenv('MYSQL_PORT') ?: 3306));

    $conn = new mysqli($servername, $username, $password, $dbname, $port);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
}
?>

