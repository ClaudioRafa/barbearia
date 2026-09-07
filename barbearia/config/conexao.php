<?php
/**
 * Conexão com o banco de dados via PDO.
 * Ajuste as constantes abaixo conforme seu ambiente XAMPP.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'barbearia');
define('DB_USER', 'root');
define('DB_PASS', ''); // senha padrão do XAMPP é vazia

function conectarBanco(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4';
            $pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            die('Erro ao conectar ao banco de dados. Verifique se o MySQL está rodando no XAMPP e se o banco "barbearia" foi importado. Detalhes: ' . htmlspecialchars($e->getMessage()));
        }
    }

    return $pdo;
}
