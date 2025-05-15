<?php

class Category_model {
    private $table = "categories";  // Nama tabel kategori
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    // Mendapatkan semua kategori
    public function getAllCategories() {
        $this->db->query("SELECT * FROM " . $this->table);
        return $this->db->resultSet(); // Mengambil semua kategori
    }

    // Mendapatkan kategori berdasarkan ID
    public function getCategoryById($id) {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single(); // Mengambil satu kategori
    }

    public function getCategoryBySlug($slug) {
    $this->db->query("SELECT * FROM " . $this->table . " WHERE slug = :slug");
    $this->db->bind(':slug', $slug);
    return $this->db->single();
}


    // Menambah kategori baru
    public function addCategory($name, $slug) {
        $this->db->query("INSERT INTO " . $this->table . " (name, slug) VALUES (:name, :slug)");
        $this->db->bind(':name', $name);
        $this->db->bind(':slug', $slug);
        return $this->db->execute(); // Menjalankan query untuk menambah kategori
    }

    // Mengupdate kategori
    public function updateCategory($id, $name, $slug) {
        $this->db->query("UPDATE " . $this->table . " SET name = :name, slug = :slug WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $name);
        $this->db->bind(':slug', $slug);
        return $this->db->execute(); // Menjalankan query untuk update kategori
    }

    // Menghapus kategori
    public function deleteCategory($id) {
        $this->db->query("DELETE FROM " . $this->table . " WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute(); // Menjalankan query untuk menghapus kategori
    }
}
?>
