<?php

if (!defined('MYSQLI_ASSOC')) {
    define('MYSQLI_ASSOC', 1);
}

if (!defined('MYSQLI_NUM')) {
    define('MYSQLI_NUM', 2);
}

/*
|--------------------------------------------------------------------------
| Load .env Manual
|--------------------------------------------------------------------------
| PHP biasa tidak otomatis membaca file .env.
| Jadi kita buat loader sederhana agar DATABASE_URL bisa terbaca saat local.
*/
function loadEnvFile(): void
{
    $paths = [
        getcwd() . '/.env',
        __DIR__ . '/../.env',
        __DIR__ . '/../../.env',
        dirname(__DIR__) . '/.env',
        dirname(__DIR__, 2) . '/.env',
    ];

    foreach ($paths as $path) {
        if (!file_exists($path)) {
            continue;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (substr($line, 0, 1) === '#') {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);

            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($key !== '') {
                putenv($key . '=' . $value);
                $_ENV[$key] = $value;
            }
        }

        break;
    }
}

loadEnvFile();

function envValue(string $key, ?string $default = null): ?string
{
    $value = getenv($key);

    if ($value === false && isset($_ENV[$key])) {
        $value = $_ENV[$key];
    }

    if ($value === false || $value === '') {
        return $default;
    }

    return $value;
}

/*
|--------------------------------------------------------------------------
| DB Result Wrapper
|--------------------------------------------------------------------------
| Supaya kode lama yang mirip mysqli tetap jalan.
*/
class DBResult
{
    private array $rows = [];
    private int $index = 0;

    public int $num_rows = 0;

    public function __construct(PDOStatement $stmt)
    {
        $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $this->num_rows = count($this->rows);
    }

    public function fetch_assoc(): ?array
    {
        if (!isset($this->rows[$this->index])) {
            return null;
        }

        $row = $this->rows[$this->index];
        $this->index++;

        return $row;
    }

    public function fetch_row(): ?array
    {
        if (!isset($this->rows[$this->index])) {
            return null;
        }

        $row = array_values($this->rows[$this->index]);
        $this->index++;

        return $row;
    }

    public function fetch_all(int $mode = MYSQLI_ASSOC): array
    {
        if ($mode === MYSQLI_NUM) {
            return array_map('array_values', $this->rows);
        }

        return $this->rows;
    }
}

/*
|--------------------------------------------------------------------------
| DB Statement Wrapper
|--------------------------------------------------------------------------
| Supaya bind_param(), execute(), get_result(), bind_result(), fetch()
| masih bisa dipakai seperti pola mysqli lama.
*/
class DBStatement
{
    private PDOStatement $stmt;
    private array $params = [];
    private array $paramTypes = [];
    private array $boundResultRefs = [];

    public int $affected_rows = 0;

    public function __construct(PDOStatement $stmt)
    {
        $this->stmt = $stmt;
    }

    public function bind_param(string $types, &...$vars): void
    {
        $this->params = [];
        $this->paramTypes = str_split($types);

        foreach ($vars as &$var) {
            $this->params[] =& $var;
        }
    }

    private function pdoParamType(string $type, mixed $value): int
    {
        if ($value === null) {
            return PDO::PARAM_NULL;
        }

        return match ($type) {
            'i' => PDO::PARAM_INT,
            'b' => PDO::PARAM_LOB,
            default => PDO::PARAM_STR,
        };
    }

    public function execute(): bool
    {
        if (!empty($this->params)) {
            foreach ($this->params as $index => &$value) {
                $type = $this->paramTypes[$index] ?? 's';
                $pdoType = $this->pdoParamType($type, $value);

                if ($pdoType === PDO::PARAM_LOB && is_string($value)) {
                    $stream = fopen('php://temp', 'rb+');
                    fwrite($stream, $value);
                    rewind($stream);
                    $this->stmt->bindValue($index + 1, $stream, PDO::PARAM_LOB);
                } else {
                    $this->stmt->bindValue($index + 1, $value, $pdoType);
                }
            }

            $success = $this->stmt->execute();
        } else {
            $success = $this->stmt->execute();
        }

        $this->affected_rows = $this->stmt->rowCount();

        return $success;
    }

    public function get_result(): DBResult
    {
        return new DBResult($this->stmt);
    }

    public function bind_result(&...$vars): void
    {
        $this->boundResultRefs = [];

        foreach ($vars as &$var) {
            $this->boundResultRefs[] =& $var;
        }
    }

    public function fetch(): bool
    {
        $row = $this->stmt->fetch(PDO::FETCH_NUM);

        if ($row === false) {
            return false;
        }

        foreach ($this->boundResultRefs as $index => &$ref) {
            $ref = $row[$index] ?? null;
        }

        return true;
    }

    public function close(): void
    {
        $this->stmt->closeCursor();
    }
}

/*
|--------------------------------------------------------------------------
| DB Connection Wrapper
|--------------------------------------------------------------------------
*/
class DBConnection
{
    private PDO $pdo;

    public int $affected_rows = 0;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function query(string $sql): DBResult|false
    {
        $stmt = $this->pdo->query($sql);

        if ($stmt === false) {
            return false;
        }

        $this->affected_rows = $stmt->rowCount();

        return new DBResult($stmt);
    }

    public function prepare(string $sql): DBStatement
    {
        return new DBStatement($this->pdo->prepare($sql));
    }

    public function pdo(): PDO
    {
        return $this->pdo;
    }

    public function real_escape_string(string $value): string
    {
        return substr($this->pdo->quote($value), 1, -1);
    }

    public function insert_id(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    public function begin_transaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }

    public function close(): void
    {
        // PDO akan otomatis menutup koneksi saat script selesai.
    }

    public function __get(string $name)
    {
        if ($name === 'insert_id') {
            return $this->pdo->lastInsertId();
        }

        return null;
    }
}

/*
|--------------------------------------------------------------------------
| Build PostgreSQL DSN dari DATABASE_URL Neon
|--------------------------------------------------------------------------
*/
function buildPostgresDsnFromUrl(string $databaseUrl): array
{
    $parts = parse_url($databaseUrl);

    if ($parts === false || empty($parts['host']) || empty($parts['path'])) {
        throw new RuntimeException('Format DATABASE_URL tidak valid.');
    }

    $query = [];

    if (!empty($parts['query'])) {
        parse_str($parts['query'], $query);
    }

    $host = $parts['host'];
    $port = $parts['port'] ?? 5432;
    $dbname = ltrim($parts['path'], '/');
    $user = isset($parts['user']) ? urldecode($parts['user']) : '';
    $pass = isset($parts['pass']) ? urldecode($parts['pass']) : '';
    $sslmode = $query['sslmode'] ?? 'require';

    $endpoint = explode('.', $host)[0];

    if (!empty($query['options']) && str_starts_with($query['options'], 'endpoint=')) {
        $endpoint = str_replace('endpoint=', '', $query['options']);
    }

    $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";

    return [$dsn, $user, $pass, $endpoint];
}
/*
|--------------------------------------------------------------------------
| Get Database Connection
|--------------------------------------------------------------------------
*/
function getDB(): DBConnection
{
    static $conn = null;

    if ($conn !== null) {
        return $conn;
    }

    $databaseUrl = envValue('DATABASE_URL');
    $driver = strtolower((string) envValue('DB_DRIVER', $databaseUrl ? 'pgsql' : 'pgsql'));

    try {
        if ($databaseUrl && preg_match('/^postgres(ql)?:\/\//i', $databaseUrl)) {
            [$dsn, $user, $pass, $endpoint] = buildPostgresDsnFromUrl($databaseUrl);
        } elseif ($driver === 'pgsql') {
            $host = envValue('DB_HOST', '127.0.0.1');
            $port = envValue('DB_PORT', '5432');
            $dbname = envValue('DB_NAME', 'db_reservasi_ruangan');
            $user = envValue('DB_USER', 'postgres');
            $pass = envValue('DB_PASS', '');
            $sslmode = envValue('DB_SSLMODE', 'disable');

            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";
        } elseif ($driver === 'mysql') {
            $host = envValue('DB_HOST', '127.0.0.1');
            $port = envValue('DB_PORT', '3306');
            $dbname = envValue('DB_NAME', 'db_reservasi_ruangan');
            $user = envValue('DB_USER', 'root');
            $pass = envValue('DB_PASS', '');
            $charset = envValue('DB_CHARSET', 'utf8mb4');

            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        } else {
            throw new RuntimeException('DB_DRIVER tidak valid. Gunakan pgsql atau mysql.');
        }

        try {
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (Throwable $e) {
            if (
                isset($endpoint) &&
                str_contains($e->getMessage(), 'Endpoint ID is not specified')
            ) {
                $passWithEndpoint = 'endpoint=' . $endpoint . '$' . $pass;

                $pdo = new PDO($dsn, $user, $passWithEndpoint, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                ]);
            } else {
                throw $e;
            }
        }

        $conn = new DBConnection($pdo);

        return $conn;
    } catch (Throwable $e) {
        $debug = envValue('APP_DEBUG', 'false');

        if ($debug === 'true') {
            die('Koneksi database gagal: ' . $e->getMessage());
        }

        die('Koneksi database gagal. Periksa konfigurasi DATABASE_URL atau DB_* environment.');
    }
}