<?php

$DB_HOST = 'localhost';
$DB_NAME = 'makys_cafe';
$DB_USER = 'root';
$DB_PASS = '';

// Get a shared PDO instance (singleton)
function getPDO(): PDO
{
    static $pdo = null;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    global $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS;

    $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
    } catch (PDOException $e) {
        // Log the error and show a friendly message. Don't expose details in production.
        error_log('Database Connection Error: ' . $e->getMessage());
        http_response_code(500);
        die('A database connection error occurred.');
    }

    return $pdo;
}

// Helper: fetch a single row
function db_fetch(string $sql, array $params = [])
{
    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch();
}

// Helper: fetch all rows
function db_fetch_all(string $sql, array $params = []): array
{
    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

// Helper: execute (INSERT/UPDATE/DELETE) - returns number of affected rows
function db_execute(string $sql, array $params = []): int
{
    $stmt = getPDO()->prepare($sql);
    $stmt->execute($params);
    return $stmt->rowCount();
}

// Helper: last insert id
function db_last_insert_id(): string
{
    return getPDO()->lastInsertId();
}

// Transaction helpers
function db_begin_transaction(): bool
{
    return getPDO()->beginTransaction();
}

function db_commit(): bool
{
    return getPDO()->commit();
}

function db_rollback(): bool
{
    return getPDO()->rollBack();
}

// Backwards compatibility: create a mysqli `$conn` variable used by older API files.
// This file primarily exposes PDO helpers, but some legacy scripts expect `$conn`.
// We create a lightweight mysqli connection so those scripts keep working.
if (!isset($conn)) {
    $conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
    if ($conn->connect_error) {
        error_log('MySQLi Connection Error: ' . $conn->connect_error);
        // Do not expose details to users; scripts should handle connection errors gracefully.
    } else {
        $conn->set_charset('utf8mb4');
    }
}

/* Usage examples (always use prepared statements):

// Create user with secure password hashing
$password = 'secret123';
$hash = password_hash($password, PASSWORD_DEFAULT);
db_execute('INSERT INTO users (username, password_hash) VALUES (?, ?)', [$username, $hash]);

// Verify password on login
$user = db_fetch('SELECT * FROM users WHERE username = ?', [$username]);
if ($user && password_verify($password, $user['password_hash'])) {
    // Successful login
}

// Basic CRUD examples
$allProducts = db_fetch_all('SELECT * FROM products WHERE is_active = ?', [1]);
$singleProduct = db_fetch('SELECT * FROM products WHERE id = ?', [$id]);
db_execute('UPDATE products SET price = ? WHERE id = ?', [$price, $id]);
db_execute('DELETE FROM products WHERE id = ?', [$id]);

*/
