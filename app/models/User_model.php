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

  public function register($username, $full_name, $email, $passwordHash)
  {
    $defaultImage = "assets/img/default.jpg";
    $sql = "INSERT INTO $this->table (id, username, full_name, email, password, role)
                VALUES (UUID(), :username, :full_name, :email, :password, 'buyer')";

    $this->db->query($sql);
    $this->db->bind(":username", $username);
    $this->db->bind(":full_name", $full_name);
    $this->db->bind(":email", $email);
    $this->db->bind(":password", $passwordHash);
     $this->db->bind(':image', $defaultImage);

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
    $image = null
  ) {
    $sql = "UPDATE $this->table SET username = :username, full_name = :full_name, email = :email";

    if ($image !== null) {
      $sql .= ", image = :image";
    }

    $sql .= " WHERE id = :id";

    $this->db->query($sql);
    $this->db->bind(":username", $username);
    $this->db->bind(":full_name", $full_name);
    $this->db->bind(":email", $email);
    if ($image !== null) {
      $this->db->bind(":image", $image);
    }
    $this->db->bind(":id", $id);
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
    $phone_number,
    $is_default
  ) {
    if ($is_default) {
      $this->unsetOtherDefaultAddresses($userId);
    }

    $sql = "INSERT INTO user_addresses (id, user_id, label, address_line_1, address_line_2, city, postal_code, country, phone_number, is_default)
                VALUES (UUID(), :user_id, :label, :address_line_1, :address_line_2, :city, :postal_code, :country, :phone_number, :is_default)";

    $this->db->query($sql);
    $this->db->bind(":user_id", $userId);
    $this->db->bind(":label", $label);
    $this->db->bind(":address_line_1", $address_line_1);
    $this->db->bind(":address_line_2", $address_line_2);
    $this->db->bind(":city", $city);
    $this->db->bind(":postal_code", $postal_code);
    $this->db->bind(":country", $country);
    $this->db->bind(":phone_number", $phone_number);
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
    $phone_number,
    $is_default
  ) {
    if ($is_default) {
      $this->unsetOtherDefaultAddresses($userId);
    }

    $sql = "UPDATE user_addresses SET 
                    label = :label, 
                    address_line_1 = :address_line_1,
                    address_line_2 = :address_line_2,
                    city = :city,
                    postal_code = :postal_code,
                    country = :country,
                    phone_number = :phone_number,
                    is_default = :is_default,
                    updated_at = CURRENT_TIMESTAMP()
                WHERE id = :id AND user_id = :user_id AND deleted_at IS NULL";

    $this->db->query($sql);
    $this->db->bind(":label", $label);
    $this->db->bind(":address_line_1", $address_line_1);
    $this->db->bind(":address_line_2", $address_line_2);
    $this->db->bind(":city", $city);
    $this->db->bind(":postal_code", $postal_code);
    $this->db->bind(":country", $country);
    $this->db->bind(":phone_number", $phone_number);
    $this->db->bind(":is_default", $is_default);
    $this->db->bind(":id", $id);
    $this->db->bind(":user_id", $userId);

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

  private function unsetOtherDefaultAddresses($userId)
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
