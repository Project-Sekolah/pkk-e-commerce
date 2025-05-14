<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- View: login.php -->
<form action="<?= BASEURL; ?>/user/login" method="POST">
    <div>
        <label for="email">Email</label>
        <input type="email" name="email" id="email" required>
    </div>
    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password" required>
    </div>
    <button type="submit">Login</button>
</form>

