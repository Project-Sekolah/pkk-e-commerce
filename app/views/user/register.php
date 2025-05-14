
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- View: register.php -->
<form action="<?= BASEURL; ?>/user/register" method="POST" style='margin-top : 100px'>
    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" required>
    </div>
    <div>
        <label for="full_name">Full Name</label>
        <input type="text" name="full_name" id="full_name" required>
    </div>
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <div>
        <label for="confirm_password">Confirm Password</label>
        <input type="password" name="confirm_password" id="confirm_password" required>
    </div>
    <button type="submit">Register</button>
</form>

