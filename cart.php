<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Savatcha - Chinor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
    <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        const tg = window.Telegram.WebApp;
        tg.expand();
    </script>

    <header class="d-flex justify-content-between align-items-center px-3 py-2 fixed-top dark-header ">
        <div class="d-flex align-items-center gap-2">
           <img src="logo.png" alt="Chinor" style="max-width: 50px; margin-left: 10px;" />
           <img src="logoT.png" alt="Chinor" style="max-width: 100px; margin-left: 20px;" />
        </div>
        <form method="get" action="index.php" class="d-inline">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">
            <button type="submit" class="btn btn-back">
                Ortga
            </button>
        </form>
    </header>

    <main class="container mt-5 pt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="text-gold">Savatchangiz</h4>
            <button class="btn btn-sm btn-outline-danger" onclick="clearCart()">
                <i class="bi bi-trash3"></i> Tozalash
            </button>
        </div>

        <div id="cart-items" class="mb-4"></div>

        <div id="cart-footer" style="display: none;"> <div class="d-flex justify-content-between align-items-center">
                <h5><strong>Umumiy narx: <span id="total-price" class="text-gold">0 so'm</span></strong></h5>
            </div>
            <div class="text-center mt-4">
                <button id="confirm-button" class="btn btn-gold w-100" onclick="goToConfirm()" style="max-width: 400px; margin: 0 auto;">
                    Buyurtmani tasdiqlash
                </button>
            </div>
        </div>
    </main>

    <video autoplay muted loop playsinline id="background-video">
        <source src="bg.mp4" type="video/mp4" />
        Sizning brauzeringiz videoni qo‘llab-quvvatlamaydi.
    </video>

    <script>
        let cart = JSON.parse(localStorage.getItem("cart")) || [];
        
        function saveCart() {
            localStorage.setItem("cart", JSON.stringify(cart));
        }
        
        function clearCart() {
            if (cart.length === 0) {
                Swal.fire({ title: 'Savatcha boʻsh', icon: 'info', confirmButtonText: 'OK' });
                return;
            }

            Swal.fire({
                title: 'Ishonchingiz komilmi?',
                text: "Savatchadagi barcha mahsulotlar o'chiriladi!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Ha, oʻchirish!',
                cancelButtonText: 'Yo\'q, bekor qilish'
            }).then((result) => {
                if (result.isConfirmed) {
                    cart = [];
                    saveCart();
                    renderCart();
                    Swal.fire('Tozalandi!', 'Savatchangiz muvaffaqiyatli tozalandi.', 'success');
                }
            });
        }

        function renderCart() {
            const cartContainer = document.getElementById("cart-items");
            const totalPriceEl = document.getElementById("total-price");
            const cartFooter = document.getElementById("cart-footer");
            cartContainer.innerHTML = "";
            let total = 0;

            if (cart.length === 0) {
                cartContainer.innerHTML = `
                    <div class="text-center py-5">
                        <img src="savat.png" alt="Bo'sh savat" style="width: 150px; opacity: 0.7;">
                        <h5 class="mt-3 text-gold">Savatchangiz hozircha bo'sh...</h5>
                    </div>
                `;
                if (cartFooter) cartFooter.style.display = 'none';
                return;
            }
            
            if (cartFooter) cartFooter.style.display = 'block';

            cart.forEach(item => {
                const itemTotal = item.price * item.qty;
                total += itemTotal;
                const div = document.createElement("div");
                div.className = "card mb-3 bg-dark text-white";
                div.innerHTML = `
                    <div class="row g-0 align-items-center">
                        <div class="col-4">
                            <img src="${item.image}" class="img-fluid rounded-start" alt="${item.name}">
                        </div>
                        <div class="col-8">
                            <div class="card-body py-2 px-3">
                                <h6 class="card-title mb-1" style="font-size: 0.9rem;">${item.name}</h6>
                                <p class="card-text small">
                                    ${Number(item.price).toLocaleString()} x ${item.qty} = <strong>${itemTotal.toLocaleString()} so'm</strong>
                                </p>
                            </div>
                        </div>
                    </div>
                `;
                cartContainer.appendChild(div);
            });

            totalPriceEl.textContent = total.toLocaleString() + " so'm";
        }

        function goToConfirm() {
    if (cart.length === 0) {
        Swal.fire('Savatchangiz bo\'sh!', '', 'warning');
        return;
    }

    const tgUser = JSON.parse(localStorage.getItem("tg_user")) || {};
    
    // MUHIM: Faqat user_id borligini tekshiramiz. Ism va telefon bu sahifada shart emas.
    if (!tgUser.user_id) {
        alert("❌ Foydalanuvchi IDsi topilmadi. Iltimos, botni qayta ishga tushirib, qaytadan kirib ko‘ring.");
        return;
    }

    // Qolgan kod o'zgarishsiz ishlayveradi. Ism va telefon bo'sh bo'lsa, bo'sh holda jo'natiladi.
    const cartData = JSON.stringify(cart);
    let total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

    const form = document.createElement("form");
    form.method = "POST";
    form.action = "confirm.php";
    const fields = {
        user_id: tgUser.user_id,
        items: cartData,
        total: total
    };
    for (const key in fields) {
        const input = document.createElement("input");
        input.type = "hidden";
        input.name = key;
        input.value = fields[key];
        form.appendChild(input);
    }
    document.body.appendChild(form);
    form.submit();
}
        
        // Sahifa yuklanganda mavjud bo'lmagan loadCart() o'rniga renderCart() chaqiriladi
        renderCart();
    </script>
</body>
</html>