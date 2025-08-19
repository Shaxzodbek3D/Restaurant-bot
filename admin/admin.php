<?php
ini_set('session.gc_maxlifetime', 86400); // 24 soat (sekundda)
session_set_cookie_params(86400);
session_start();

// Oddiy login-parol (keyinchalik bazadan olishga o‘tkazamiz)
$admin_user = "admin";
$admin_pass = "12345";

// POST orqali yuborilgan bo‘lsa, tekshiramiz
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $login = $_POST["username"] ?? "";
    $parol = $_POST["password"] ?? "";

    if ($login === $admin_user && $parol === $admin_pass) {
        $_SESSION["admin"] = true;
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Login yoki parol noto‘g‘ri!";
    }
}

// Agar admin oldin login qilgan bo‘lsa, to‘g‘ridan-to‘g‘ri dashboardga yo‘naltiramiz
if (isset($_SESSION["admin"]) && $_SESSION["admin"] === true) {
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <title>Admin Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex justify-content-center align-items-center vh-100">
  <div class="p-4 bg-white rounded shadow" style="width: 360px;">
    <h4 class="text-center text-danger mb-3">Admin Panelga Kirish</h4>

    <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Login</label>
        <input type="text" name="username" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Parol</label>
        <input type="password" name="password" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-danger w-100">Kirish</button>
    </form>
  </div>
</body>
</html>
