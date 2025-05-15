<?php
// Controller: User.php

class User extends Controller {

    public function register() {
        // Redirect to home if already logged in
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: ' . BASEURL . '/');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $username = $_POST['username'];
            $full_name = $_POST['full_name'];
            $email = $_POST['email'];
            $password = $_POST['password'];
            $confirm_password = $_POST['confirm_password'];

            // Validate user inputs here
            if (empty($username) || empty($full_name) || empty($email) || empty($password) || empty($confirm_password)) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'All fields are required.'];
                header('Location: ' . BASEURL . '/');
                exit;
            }

            // Check if passwords match
            if ($password !== $confirm_password) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Passwords do not match.'];
                header('Location: ' . BASEURL . '/');
                exit;
            }

            $passwordHash = password_hash($password, PASSWORD_BCRYPT);

            // Instantiate the User model
            $userModel = $this->model('User_model');
            $userModel->register($username, $full_name, $email, $passwordHash);

            // Set success alert message and redirect to login page
            $_SESSION['alert'] = ['type' => 'success', 'message' => 'Registration successful. Please login.'];
            header('Location: ' . BASEURL . '/');
            exit;
        }
    }

    public function login() {
        // Redirect to home if already logged in
        if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
            header('Location: ' . BASEURL . '/');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'];
            $password = $_POST['password'];

            // Validate user inputs here
            if (empty($email) || empty($password)) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Both fields are required.'];
                header('Location: ' . BASEURL . '/');
                exit;
            }

            // Instantiate the User model
            $userModel = $this->model('User_model');
            $user = $userModel->getUserByEmail($email);

            if ($user && password_verify($password, $user['password'])) {
                // Store user info in session after successful login
                $_SESSION['user'] = $user;
                $_SESSION['logged_in'] = true;

                // Set success alert message and redirect to home
                $_SESSION['alert'] = ['type' => 'success', 'message' => 'Login successful.'];
                header('Location: ' . BASEURL . '/');
                exit;
            } else {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Invalid credentials.'];
                header('Location: ' . BASEURL . '/');
                exit;
            }
        }
    }

    public function logout() {
        // Destroy session to log the user out
        session_destroy();

        // Set success alert message for logout
        $_SESSION['alert'] = ['type' => 'success', 'message' => 'You have been logged out successfully.'];
        header('Location: ' . BASEURL . '/');
        exit;
    }

    public function profile() {
    
        $data['judul'] = 'Profile';
        $userModel = $this->model('User_model');
        $data['user'] = $userModel->getUserById($_SESSION['user']['id']);
        $data['addresses'] = $userModel->getAddressesByUserId($_SESSION['user']['id']);

        $this->render(['user/profile'], $data);


    }

    public function updateProfile() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASEURL . '/');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $username = $_POST['username'];
        $full_name = $_POST['full_name'];
        $email = $_POST['email'];

        $userModel = $this->model('User_model');
        $user = $userModel->getUserById($userId); // ambil data user lama (termasuk image path)

        // Handle file upload untuk foto baru
        $imagePath = $user['image']; // default: pakai gambar lama

        if (!empty($_FILES['image']['name'])) {
    try {

        // Upload gambar ke Cloudinary
        $uploadedImage = $this->cloudinary->uploadApi()->upload(
            $_FILES['image']['tmp_name'],
            [
                'folder' => 'profile_pics/',
                'public_id' => uniqid() . '_' . pathinfo($_FILES['image']['name'], PATHINFO_FILENAME),
                'overwrite' => true,
                'resource_type' => 'image'
            ]
        );

        $imagePath = $uploadedImage['secure_url'];
    } catch (Exception $e) {
        $_SESSION['alert'] = ['type' => 'error', 'message' => 'Gagal mengunggah foto ke Cloudinary: ' . $e->getMessage()];
        header('Location: ' . BASEURL . '/user/profile');
        exit;
    }
}


        // Update data ke database
        $userModel->updateProfile($userId, $username, $full_name, $email, $imagePath);

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Profile updated successfully.'];
        header('Location: ' . BASEURL . '/user/profile');
        exit;
}


    public function updateAddress() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASEURL . '/');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $addressId = $_POST['address_id'];
        $label = $_POST['label'];
        $address_line_1 = $_POST['address_line_1'];
        $address_line_2 = $_POST['address_line_2'];
        $city = $_POST['city'];
        $postal_code = $_POST['postal_code'];
        $country = $_POST['country'];
        $phone_number = $_POST['phone_number'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        // Update the address using the User model
        $userModel = $this->model('User_model');
        $userModel->updateUserAddress($addressId, $userId, $label, $address_line_1, $address_line_2, $city, $postal_code, $country, $phone_number, $is_default);

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Address updated successfully.'];
        header('Location: ' . BASEURL . '/user/profile');
        exit;
    }

    public function addAddress() {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASEURL . '/');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $label = $_POST['label'];
        $address_line_1 = $_POST['address_line_1'];
        $address_line_2 = $_POST['address_line_2'];
        $city = $_POST['city'];
        $postal_code = $_POST['postal_code'];
        $country = $_POST['country'];
        $phone_number = $_POST['phone_number'];
        $is_default = isset($_POST['is_default']) ? 1 : 0;

        // Add new address using the User model
        $userModel = $this->model('User_model');
        $userModel->addUserAddress($userId, $label, $address_line_1, $address_line_2, $city, $postal_code, $country, $phone_number, $is_default);

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Address added successfully.'];
        header('Location: ' . BASEURL . '/user/profile');
        exit;
    }

    public function deleteAddress($id) {
        if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
            header('Location: ' . BASEURL . '/');
            exit;
        }

        $userId = $_SESSION['user']['id'];
        $userModel = $this->model('User_model');
        $userModel->deleteUserAddress($id, $userId);

        $_SESSION['alert'] = ['type' => 'success', 'message' => 'Address deleted successfully.'];
        header('Location: ' . BASEURL . '/user/profile');
        exit;
    }
}
