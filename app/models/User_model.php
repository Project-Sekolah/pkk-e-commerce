<?php
// Model: User_model.php
class User_model {
    private $table = 'users';
    private $db;

    public function __construct() {
        $this->db = new Database;
    }

    public function register($username, $full_name, $email, $passwordHash) {
        $sql = "INSERT INTO $this->table (id, username, full_name, email, password, role)
                VALUES (UUID(), :username, :full_name, :email, :password, 'buyer')";

        $this->db->query($sql);
        $this->db->bind(':username', $username);
        $this->db->bind(':full_name', $full_name);
        $this->db->bind(':email', $email);
        $this->db->bind(':password', $passwordHash);

        return $this->db->execute();
    }

    public function getUserByEmail($email) {
        $sql = "SELECT * FROM $this->table WHERE email = :email";

        $this->db->query($sql);
        $this->db->bind(':email', $email);

        return $this->db->single();
    }
}
