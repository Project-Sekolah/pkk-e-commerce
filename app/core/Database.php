<?php

define('BASEURL', 'https://pkk-e-commerce-production.up.railway.app');

class Database
{
    private $dbh;
    private $stmt;

    public function __construct()
    {
        $host = DB_HOST;
        $port = DB_PORT;
        $dbname = DB_NAME;
        $user = DB_USER;
        $pass = DB_PASS;

        try {
            // Koneksi awal tanpa memilih database
            $dsnNoDb = "mysql:host=$host;port=$port;charset=utf8mb4";
            $pdo = new PDO($dsnNoDb, $user, $pass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Buat database jika belum ada
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // Koneksi ke database yang sudah dipastikan ada
            $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";

            // Menetapkan opsi PDO
            $options = [
                PDO::ATTR_PERSISTENT => true,      // Koneksi persisten
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Mengatur mode error untuk melempar pengecualian
            ];
            
            // Membuat koneksi dengan opsi yang ditentukan
            $this->dbh = new PDO($dsn, $user, $pass, $options);

            // Membuat tabel jika belum ada
            $this->createTables();
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }

    private function createTables()
    {
        // Ambil query untuk membuat tabel dari schema.sql
        $sql = file_get_contents(__DIR__ . '/schema.sql');

        try {
            // Eksekusi query untuk membuat tabel
            $this->dbh->exec($sql);
        } catch (PDOException $e) {
            die("Gagal membuat tabel: " . $e->getMessage());
        }
    }

    public function getConnection()
    {
        return $this->dbh;
    }

    public function query($query)
    {
        $this->stmt = $this->dbh->prepare($query);
    }

    public function bind($param, $value, $type = null)
    {
        if (is_null($type)) {
            switch (true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }

        $this->stmt->bindValue($param, $value, $type);
    }

    public function execute()
    {
        return $this->stmt->execute();
    }

    public function resultSet()
    {
        $this->execute();
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function single()
    {
        $this->execute();
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }
}
