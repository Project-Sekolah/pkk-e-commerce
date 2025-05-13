<?php
namespace config;

use PDO;
use PDOException;

class Database
{
  private $connection;

  public function __construct()
  {
    $host = getenv("DB_HOST");
    $port = getenv("DB_PORT");
    $dbname = getenv("DB_NAME");
    $user = getenv("DB_USER");
    $pass = getenv("DB_PASS");

    try {
      // Connect without selecting the DB first
      $dsnNoDb = "mysql:host=$host;port=$port;charset=utf8mb4";
      $pdo = new PDO($dsnNoDb, $user, $pass);
      $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Create database if it doesn't exist
      $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

      // Now connect to the database
      $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
      $this->connection = new PDO($dsn, $user, $pass);
      $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

      // Create tables
      $this->createTables();
    } catch (PDOException $e) {
      die("Database error: " . $e->getMessage());
    }
  }

  private function createTables()
  {
    $sql = file_get_contents(__DIR__ . '/schema.sql'); // Recommended way if SQL gets too big

    try {
      $this->connection->exec($sql);
    } catch (PDOException $e) {
      die("Gagal buat tabel: " . $e->getMessage());
    }
  }

  public function getConnection()
  {
    return $this->connection;
  }
}
