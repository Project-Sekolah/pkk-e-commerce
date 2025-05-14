<?php
/*

class User extends Controller
{
    private $db;
    private $user;

    public function __construct($db)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->db = $db;
        $this->user = new User_modal();
    }

    // =========================
    // Register
    // =========================
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // CSRF check
            if ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['flash'] = "Token CSRF tidak valid.";
                header("Location: index.php?url=user/register");
                exit();
            }

            $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'buyer';

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $_SESSION['flash'] = "Email tidak valid.";
                header("Location: index.php?url=user/register");
                exit();
            }

            if (strlen($password) < 8 || !preg_match('/\d/', $password)) {
                $_SESSION['flash'] = "Password harus minimal 8 karakter dan mengandung angka.";
                header("Location: index.php?url=user/register");
                exit();
            }

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $this->user->setUsername($username);
            $this->user->setEmail($email);
            $this->user->setPassword($hashedPassword);
            $this->user->setRole($role);

            try {
                if ($this->user->register($this->db)) {
                    $_SESSION['flash'] = "Registrasi berhasil! Silakan login.";
                    header("Location: index.php?url=user/login");
                    exit();
                } else {
                    $_SESSION['flash'] = "Registrasi gagal.";
                }
            } catch (Exception $e) {
                $_SESSION['flash'] = "Error: " . $e->getMessage();
            }
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->view('register');
    }

    // =========================
    // Login
    // =========================
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['flash'] = "Token CSRF tidak valid.";
                header("Location: index.php?url=user/login");
                exit();
            }

            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            $this->user->setEmail($email);
            $this->user->setPassword($password);

            try {
                $user = $this->user->login($this->db);
                if ($user !== null) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];

                    $_SESSION['flash'] = "Login berhasil. Selamat datang, {$user['username']}!";
                    $redirect = $user['role'] === 'admin' ? 'admin/dashboard' : 'user/dashboard';
                    header("Location: index.php?url=$redirect");
                    exit();
                } else {
                    $_SESSION['flash'] = "Email atau password salah!";
                    header("Location: index.php?url=user/login");
                    exit();
                }
            } catch (Exception $e) {
                $_SESSION['flash'] = "Error: " . $e->getMessage();
                header("Location: index.php?url=user/login");
                exit();
            }
        }

        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        $this->view('login');
    }

    // =========================
    // Update Profile
    // =========================
    public function update()
    {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?url=user/login");
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
                $_SESSION['flash'] = "Token CSRF tidak valid.";
                header("Location: index.php?url=user/update");
                exit();
            }

            $uploadDir = $_ENV['UPLOAD_DIR'] ?? 'assets/img/';
            $maxSize = $_ENV['MAX_FILE_SIZE'] ?? (5 * 1024 * 1024);

            $full_name = htmlspecialchars(trim($_POST['full_name'] ?? ''));
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
            $image = $_FILES['image'] ?? null;

            $this->user->setId($_SESSION['user_id']);
            $this->user->setFullName($full_name);
            $this->user->setEmail($email);

            if ($image && $image['error'] === 0) {
                $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
                if (!in_array($image['type'], $allowedTypes)) {
                    $_SESSION['flash'] = "Hanya file gambar (JPEG, PNG, GIF) yang diperbolehkan.";
                    header("Location: index.php?url=user/update");
                    exit();
                }

                if ($image['size'] > $maxSize) {
                    $_SESSION['flash'] = "Ukuran file gambar terlalu besar.";
                    header("Location: index.php?url=user/update");
                    exit();
                }

                $fileName = uniqid('user_', true) . '.' . pathinfo($image['name'], PATHINFO_EXTENSION);
                $targetFile = $uploadDir . $fileName;

                if (move_uploaded_file($image['tmp_name'], $targetFile)) {
                    $this->user->setImage($targetFile);
                } else {
                    $_SESSION['flash'] = "Upload gambar gagal.";
                    header("Location: index.php?url=user/update");
                    exit();
                }
            }

            try {
                if ($this->user->update($this->db)) {
                    $_SESSION['flash'] = "Profil berhasil diperbarui.";
                    header("Location: index.php?url=user/profile");
                    exit();
                } else {
                    $_SESSION['flash'] = "Gagal memperbarui profil.";
                }
            } catch (Exception $e) {
                $_SESSION['flash'] = "Error: " . $e->getMessage();
            }
        }

        $this->user->setId($_SESSION['user_id']);
        $data['user'] = $this->getUserData();
        $this->view('update_profile', $data);
    }

    private function getUserData()
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function logout()
    {
        $_SESSION['flash'] = "Anda telah logout.";
        session_unset();
        session_destroy();
        header("Location: index.php?url=user/login");
        exit();
    }
}

*/