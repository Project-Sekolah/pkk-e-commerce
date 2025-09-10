<?php

class User_model
{
  private $table = "users";
  private $db;

  public function __construct()
  {
    $this->db = new Database();
  }

  // =======================
  // USER ACCOUNT MANAGEMENT
  // =======================

  public function register($username, $full_name, $email, $passwordHash, $phone_number)
  {
    $defaultImage = "assets/img/default.jpg";
    $sql = "INSERT INTO $this->table (id, username, full_name, email, password, phone_number, image, role)
        VALUES (UUID(), :username, :full_name, :email, :password, :phone_number, :image, 'buyer')";

    $this->db->query($sql);
    $this->db->bind(":username", $username);
    $this->db->bind(":full_name", $full_name);
    $this->db->bind(":email", $email);
    $this->db->bind(":password", $passwordHash);
    $this->db->bind(":phone_number", $phone_number);
    $this->db->bind(":image", $defaultImage);

    return $this->db->execute();
  }

  public function getUserByEmail($email)
  {
    $sql = "SELECT * FROM $this->table WHERE email = :email";

    $this->db->query($sql);
    $this->db->bind(":email", $email);

    return $this->db->single();
  }

  public function getUserById($id)
  {
    $this->db->query("SELECT * FROM $this->table WHERE id = :id");
    $this->db->bind(":id", $id);
    return $this->db->single();
  }

  public function updateProfile(
    $id,
    $username,
    $full_name,
    $email,
    $phone_number,
    $image = null
  ) {
    $sql = "UPDATE $this->table SET username = :username, full_name = :full_name, email = :email, phone_number = :phone_number";

    if ($image !== null) {
      $sql .= ", image = :image";
    }

    $sql .= " WHERE id = :id";

    $this->db->query($sql);
    $this->db->bind(":username", $username);
    $this->db->bind(":full_name", $full_name);
    $this->db->bind(":email", $email);
    $this->db->bind(":phone_number", $phone_number);
    if ($image !== null) {
      $this->db->bind(":image", $image);
    }
    $this->db->bind(":id", $id);
    return $this->db->execute();
  }

  public function getUserByUsername($username)
  {
    $sql = "SELECT * FROM $this->table WHERE username = :username";

    $this->db->query($sql);
    $this->db->bind(":username", $username);

    return $this->db->single();
  }

  public function updateRole($userId, $newRole)
  {
    $query = "UPDATE users SET role = :role WHERE id = :id";
    $this->db->query($query);
    $this->db->bind('role', $newRole);
    $this->db->bind('id', $userId);

    return $this->db->execute();
  }

  // ========================
  // USER ADDRESS MANAGEMENT
  // ========================

  public function getAddressesByUserId($userId)
  {
    $sql =
      "SELECT * FROM user_addresses WHERE user_id = :user_id AND deleted_at IS NULL";
    $this->db->query($sql);
    $this->db->bind(":user_id", $userId);
    return $this->db->resultSet();
  }

  public function addUserAddress(
    $userId,
    $label,
    $address_line_1,
    $address_line_2,
    $city,
    $postal_code,
    $country,
    $is_default
  ) {
    if ($is_default) {
      $this->unsetOtherDefaultAddresses($userId);
    }

    $sql = "INSERT INTO user_addresses (id, user_id, label, address_line_1, address_line_2, city, postal_code, country, is_default)
        VALUES (UUID(), :user_id, :label, :address_line_1, :address_line_2, :city, :postal_code, :country, :is_default)";

    $this->db->query($sql);
    $this->db->bind(":user_id", $userId);
    $this->db->bind(":label", $label);
    $this->db->bind(":address_line_1", $address_line_1);
    $this->db->bind(":address_line_2", $address_line_2);
    $this->db->bind(":city", $city);
    $this->db->bind(":postal_code", $postal_code);
    $this->db->bind(":country", $country);
    $this->db->bind(":is_default", $is_default);

    return $this->db->execute();
  }

  public function updateUserAddress(
    $id,
    $userId,
    $label,
    $address_line_1,
    $address_line_2,
    $city,
    $postal_code,
    $country,
    $is_default
  ) {
    if ($is_default) {
      $this->unsetOtherDefaultAddresses($userId);
    }

    // Only update fields that are not null
    $fields = [];
    $params = [":id" => $id, ":user_id" => $userId];
    if ($label !== null) {
      $fields[] = "label = :label";
      $params[":label"] = $label;
    }
    if ($address_line_1 !== null) {
      $fields[] = "address_line_1 = :address_line_1";
      $params[":address_line_1"] = $address_line_1;
    }
    if ($address_line_2 !== null) {
      $fields[] = "address_line_2 = :address_line_2";
      $params[":address_line_2"] = $address_line_2;
    }
    if ($city !== null) {
      $fields[] = "city = :city";
      $params[":city"] = $city;
    }
    if ($postal_code !== null) {
      $fields[] = "postal_code = :postal_code";
      $params[":postal_code"] = $postal_code;
    }
    if ($country !== null) {
      $fields[] = "country = :country";
      $params[":country"] = $country;
    }
    $fields[] = "is_default = :is_default";
    $params[":is_default"] = $is_default;
    $fields[] = "updated_at = CURRENT_TIMESTAMP()";
    $sql = "UPDATE user_addresses SET " . implode(", ", $fields) . " WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL";
    $this->db->query($sql);
    foreach ($params as $key => $val) {
      $this->db->bind($key, $val);
    }
    return $this->db->execute();
  }

  public function deleteUserAddress($addressId, $userId)
  {
    $sql =
      "UPDATE user_addresses SET deleted_at = CURRENT_TIMESTAMP WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL";
    $this->db->query($sql);
    $this->db->bind(":id", $addressId);
    $this->db->bind(":user_id", $userId);
    return $this->db->execute();
  }

  public function unsetOtherDefaultAddresses($userId)
  {
    $this->db->query(
      "UPDATE user_addresses SET is_default = 0 WHERE user_id = :user_id"
    );
    $this->db->bind(":user_id", $userId);
    return $this->db->execute();
  }

  // =======================
  // DATA CLEANUP
  // =======================

  public function cleanupSoftDeletedAddresses()
  {
    $sql =
      "DELETE FROM user_addresses WHERE deleted_at IS NOT NULL AND deleted_at < NOW() - INTERVAL 7 DAY";
    $this->db->query($sql);
    return $this->db->execute();
  }
}
