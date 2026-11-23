<?php

function db(): PDO
{
    static $pdo = null;
    if ($pdo) return $pdo;
    $c = $GLOBALS['CONFIG'];
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', $c['db_host'], $c['db_name'], $c['db_charset']);
    $pdo = new PDO($dsn, $c['db_user'], $c['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
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
