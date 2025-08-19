<!DOCTYPE html>
<html lang="uz">
<head>
  <meta charset="UTF-8">
  <title>Buyurtma qabul qilindi</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background: linear-gradient(145deg, #1a1a1a, #2a2a2a);
      color: #fff;
      font-family: 'Nunito', sans-serif;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      overflow: hidden;
    }
    .box {
      text-align: center;
      background: #1f1f1f;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 0 15px rgba(212, 175, 55, 0.4);
      max-width: 400px;
    }
    .checkmark {
      font-size: 80px;
      color: #d4af37;
      animation: bounce 0.6s ease-in-out;
    }
    h2 {
      color: #d4af37;
      margin-top: 20px;
    }
    @keyframes bounce {
      0%   { transform: scale(0.5); opacity: 0; }
      50%  { transform: scale(1.2); opacity: 1; }
      100% { transform: scale(1); }
    }
  </style>
</head>
<body>
  <div class="box">
    <div class="checkmark">✅</div>
    <h2>Buyurtma qabul qilindi!</h2>
    <p class="text-light">Sizning buyurtmangiz muvaffaqiyatli saqlandi.</p>
  </div>

  <script>
    // 🟡 Savatchani tozalash, foydalanuvchi ID saqlanadi
    const tgUser = localStorage.getItem("tg_user");
    localStorage.clear();
    if (tgUser) localStorage.setItem("tg_user", tgUser);

    // 🔁 3 soniyadan keyin bosh sahifaga qaytish
    setTimeout(() => {
      window.location.href = "index.php";
    }, 3000);
  </script>
</body>
</html>
