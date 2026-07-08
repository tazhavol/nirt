<?php
// admin/index.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';

// اگر قبلاً لاگین کرده → برو داشبورد
if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username && $password) {
        $pdo  = getDB();
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            loginAdmin((int)$user['id'], $user['username'], $user['full_name']);
            header('Location: dashboard.php');
            exit;
        }
    }
    $error = 'نام کاربری یا رمز عبور اشتباه است.';
}
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ورود به پنل مدیریت - NIRT</title>
<style>
  @import url('https://cdn.jsdelivr.net/gh/rastikerdar/iranian-sans@latest/dist/font-face.css');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  body {
    font-family: 'IRANSans', sans-serif;
    background: #0a0a1a;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    background-image:
      radial-gradient(ellipse at 20% 50%, rgba(0,212,255,.08) 0%, transparent 60%),
      radial-gradient(ellipse at 80% 20%, rgba(139,0,255,.08) 0%, transparent 60%);
  }

  .login-box {
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(0,212,255,.2);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 48px 40px;
    width: 100%;
    max-width: 420px;
    box-shadow: 0 0 60px rgba(0,212,255,.1);
  }

  .login-logo {
    text-align: center;
    margin-bottom: 32px;
  }

  .login-logo h1 {
    font-size: 2.2rem;
    font-weight: 900;
    background: linear-gradient(135deg, #00d4ff, #8b00ff);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: 4px;
  }

  .login-logo p {
    color: rgba(255,255,255,.5);
    font-size: .85rem;
    margin-top: 6px;
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    color: rgba(255,255,255,.7);
    font-size: .85rem;
    margin-bottom: 8px;
  }

  input {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,.06);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 10px;
    color: #fff;
    font-family: inherit;
    font-size: .95rem;
    transition: border-color .2s, box-shadow .2s;
    outline: none;
  }

  input:focus {
    border-color: rgba(0,212,255,.5);
    box-shadow: 0 0 0 3px rgba(0,212,255,.1);
  }

  .btn-login {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #00d4ff, #8b00ff);
    border: none;
    border-radius: 10px;
    color: #fff;
    font-family: inherit;
    font-size: 1rem;
    font-weight: 700;
    cursor: pointer;
    margin-top: 8px;
    transition: opacity .2s, transform .1s;
  }

  .btn-login:hover  { opacity: .9; }
  .btn-login:active { transform: scale(.98); }

  .error-msg {
    background: rgba(255,59,59,.12);
    border: 1px solid rgba(255,59,59,.3);
    color: #ff6b6b;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: .85rem;
    margin-bottom: 20px;
    text-align: center;
  }
</style>
</head>
<body>

<div class="login-box">
  <div class="login-logo">
    <h1>NIRT</h1>
    <p>پنل مدیریت</p>
  </div>

<?php if ($error): ?>
  <div class="error-msg"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<form method="POST" autocomplete="off">
  <!-- نام کاربری -->
  <div class="form-group floating">
    <input type="text" id="username" name="username"
           class="form-control"
           placeholder=" "
           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
           required autofocus>
    <label for="username">نام کاربری</label>
  </div>

  <!-- رمز عبور -->
  <div class="form-group floating">
    <input type="password" id="password" name="password"
           class="form-control"
           placeholder=" "
           required>
    <label for="password">رمز عبور</label>
  </div>

  <button type="submit" class="btn-login">ورود به پنل</button>
</form>

</body>
</html>
