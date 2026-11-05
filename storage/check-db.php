<?php

$env = file_get_contents(__DIR__ . '/../.env');
preg_match('/^DB_PASSWORD=(.*)$/m', $env, $matches);
$password = trim($matches[1] ?? '', "\" \r\n");

try {
    $pdo = new PDO(
        'mysql:host=2.25.212.18;port=5200;dbname=default;connect_timeout=8',
        'mysql',
        $password,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION],
    );
    $name = $pdo->query('select database()')->fetchColumn();
    echo "connected {$name}\n";
} catch (Throwable $exception) {
    echo 'FAIL ' . $exception->getMessage() . "\n";
    exit(1);
}
