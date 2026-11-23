<?php

function db(): PDO
{
    static $pdo = null;
    if ($pdo) {
        return $pdo;
    }
    $c = $GLOBALS['CONFIG'];
    $pass = (string) ($c['db_pass'] ?? '');
    if ($pass === '' || str_contains($pass, 'YOUR_DB_PASSWORD')) {
        throw new RuntimeException(
            'Edit app/config.php and set db_pass to your real MySQL password from hPanel → Databases → MySQL.'
        );
    }
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=%s',
        $c['db_host'],
        $c['db_name'],
        $c['db_charset'] ?? 'utf8mb4'
    );
    try {
        $pdo = new PDO($dsn, $c['db_user'], $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        throw new RuntimeException(
            'Database connection failed. Check db_name, db_user, db_pass in app/config.php. Details: ' . $e->getMessage()
        );
    }
    return $pdo;
}

function db_all(string $sql, array $p = []): array
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st->fetchAll();
}

function db_one(string $sql, array $p = []): ?array
{
    $st = db()->prepare($sql);
    $st->execute($p);
    $row = $st->fetch();
    return $row ?: null;
}

function db_exec(string $sql, array $p = []): int
{
    $st = db()->prepare($sql);
    $st->execute($p);
    return $st->rowCount();
}

function db_insert(string $sql, array $p = []): string
{
    db()->prepare($sql)->execute($p);
    return db()->lastInsertId();
}
