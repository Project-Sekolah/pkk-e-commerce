<?php

class Category_model {
    private $table = "categories";
    private $db;

    public function __construct(){
        $this->db = new Database;
    }

    // ==========================
    // GETTER FUNCTIONS
    // ==========================

    // Mendapatkan semua kategori
    public function getAllCategories() {
        $this->db->query("SELECT * FROM " . $this->table);
        return $this->db->resultSet();
    }

    // Mendapatkan kategori berdasarkan ID
    public function getCategoryById($id) {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    // Mendapatkan kategori berdasarkan slug
    public function getCategoryBySlug($slug) {
        $this->db->query("SELECT * FROM " . $this->table . " WHERE slug = :slug");
        $this->db->bind(':slug', $slug);
        return $this->db->single();
    }

    // ==========================
    // CRUD FUNCTIONS
    // ==========================

    // Menambah kategori baru
    public function addCategory($name, $slug) {
        $this->db->query("INSERT INTO " . $this->table . " (name, slug) VALUES (:name, :slug)");
        $this->db->bind(':name', $name);
        $this->db->bind(':slug', $slug);
        return $this->db->execute();
    }

    // Mengupdate kategori
    public function updateCategory($id, $name, $slug) {
        $this->db->query("UPDATE " . $this->table . " SET name = :name, slug = :slug WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':name', $name);
        $this->db->bind(':slug', $slug);
        return $this->db->execute();
    }

    // Menghapus kategori
    public function deleteCategory($id) {
        $this->db->query("DELETE FROM " . $this->table . " WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
