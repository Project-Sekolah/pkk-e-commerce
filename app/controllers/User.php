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
                header('Location: ' . BASEURL . '/user/register');
                exit;
            }

            // Check if passwords match
            if ($password !== $confirm_password) {
                $_SESSION['alert'] = ['type' => 'error', 'message' => 'Passwords do not match.'];
                header('Location: ' . BASEURL . '/user/register');
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
                header('Location: ' . BASEURL . '/user/login');
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
                header('Location: ' . BASEURL . '/home');
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
}
