<?php
/*
class User_modal
{
    private $id;
    private $username;
    private $full_name;
    private $email;
    private $password;
    private $image;
    private $role;
    private $created_at;
    private $updated_at;

    // Getters and Setters
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }
    public function getFullName() { return $this->full_name; }
    public function getEmail() { return $this->email; }
    public function getPassword() { return $this->password; }
    public function getImage() { return $this->image; }
    public function getRole() { return $this->role; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    public function setId($id) { $this->id = $id; }
    public function setUsername($username) { $this->username = $username; }
    public function setFullName($full_name) { $this->full_name = $full_name; }
    public function setEmail($email) { $this->email = $email; }
    public function setPassword($password) { $this->password = $password; }
    public function setImage($image) { $this->image = $image; }
    public function setRole($role) { $this->role = $role; }
    public function setCreatedAt($created_at) { $this->created_at = $created_at; }
    public function setUpdatedAt($updated_at) { $this->updated_at = $updated_at; }

    // Registrasi
    public function register($db)
    {
        $stmt = $db->prepare("INSERT INTO users (username, email, password, role, created_at) VALUES (?, ?, ?, ?, ?)");
        $createdAt = date('Y-m-d H:i:s');
        $stmt->bind_param("sssss", $this->username, $this->email, $this->password, $this->role, $createdAt);

        if ($stmt->execute()) {
            return true;
        } else {
            throw new Exception("Error executing query: " . $stmt->error);
        }
    }

    // Login
    public function login($db)
    {
        $stmt = $db->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $this->email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($this->password, $user['password'])) {
                return $user;
            }
        }
        return null;
    }

    public function update($db)
{
    $updatedAt = date('Y-m-d H:i:s');
    $columns = [];
    $params = [];
    $types = '';

    if (!empty($this->full_name)) {
        $columns[] = "full_name = ?";
        $params[] = $this->full_name;
        $types .= "s";
    }
    if (!empty($this->email)) {
        $columns[] = "email = ?";
        $params[] = $this->email;
        $types .= "s";
    }
    if (!empty($this->image)) {
        $columns[] = "image = ?";
        $params[] = $this->image;
        $types .= "s";
    }

    $columns[] = "updated_at = ?";
    $params[] = $updatedAt;
    $types .= "s";

    $sql = "UPDATE users SET " . implode(", ", $columns) . " WHERE id = ?";
    $params[] = $this->id;
    $types .= "i";

    $stmt = $db->prepare($sql);
    $stmt->bind_param($types, ...$params);

    if ($stmt->execute()) {
        return true;
    } else {
        throw new Exception("Error executing query: " . $stmt->error);
    }
}

}
*/

?>