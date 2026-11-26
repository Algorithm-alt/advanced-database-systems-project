<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connect(array $config): PDO
    {
        $trust = !empty($config['trust_certificate']) ? '1' : '0';
        $encrypt = !empty($config['encrypt']) ? '1' : '0';
        $dsn = sprintf(
            'sqlsrv:Server=%s;Database=%s;TrustServerCertificate=%s;Encrypt=%s',
            $config['host'],
            $config['database'],
            $trust,
            $encrypt
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        $username = (string)($config['username'] ?? '');
        $password = (string)($config['password'] ?? '');

        self::$pdo = new PDO(
            $dsn,
            $username !== '' ? $username : null,
            $password !== '' ? $password : null,
            $options
        );

        return self::$pdo;
    }

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            throw new RuntimeException('Database connection is not available.');
        }
        return self::$pdo;
    }

    public static function fetchAll(string $sql, array $params = []): array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public static function execute(string $sql, array $params = []): int
    {
        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        return $statement->rowCount();
    }

    public static function call(string $procedure, array $params = [], array $outputs = []): array
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.[A-Za-z_][A-Za-z0-9_]*)?$/', $procedure)) {
            throw new RuntimeException('Invalid procedure name.');
        }

        $declarations = [];
        $assignments = [];
        $selection = [];

        foreach ($outputs as $name => $type) {
            self::assertIdentifier((string)$name);
            if (!preg_match('/^[A-Za-z][A-Za-z0-9() ]*$/', (string)$type)) {
                throw new RuntimeException('Invalid output type.');
            }
            $declarations[] = 'DECLARE @' . $name . ' ' . $type . ';';
            $assignments[] = '@' . $name . ' = @' . $name . ' OUTPUT';
            $selection[] = '@' . $name . ' AS ' . $name;
        }

        foreach ($params as $name => $value) {
            self::assertIdentifier((string)$name);
            $assignments[] = '@' . $name . ' = :' . $name;
        }

        $sql = 'SET NOCOUNT ON; '
            . implode(' ', $declarations)
            . ' EXEC ' . $procedure
            . ($assignments !== [] ? ' ' . implode(', ', $assignments) : '')
            . ';';

        if ($selection !== []) {
            $sql .= ' SELECT ' . implode(', ', $selection) . ';';
        }

        $statement = self::pdo()->prepare($sql);
        $statement->execute($params);
        if ($statement->columnCount() === 0) {
            return [];
        }
        $row = $statement->fetch();
        return $row === false ? [] : $row;
    }

    private static function assertIdentifier(string $name): void
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) {
            throw new RuntimeException('Invalid parameter name.');
        }
    }
}
