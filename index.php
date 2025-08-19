<!DOCTYPE html>
<html lang="uz">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 <title>Chinor restorani</title>
 <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
 <link rel="stylesheet" href="style.css?v=<?= time(); ?>">
 <link href="https://fonts.googleapis.com/css2?family=Rubik:wght@400;500;700&display=swap" rel="stylesheet">
 <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">
 <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
 <script src="https://telegram.org/js/telegram-web-app.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
 <script>
 // Telegram WebApp boshlanishi
 
 const tg = window.Telegram.WebApp;
 tg.expand();

 // Telegram foydalanuvchini localStorage ga yozish (yoki yangilash)
 const telegramUser = tg.initDataUnsafe?.user;
 if (telegramUser && telegramUser.id && telegramUser.first_name) {
 const existing = localStorage.getItem("tg_user");
     if (!existing) { // Agar localStorage da hali mavjud bo'lmasa, saqlash
const tg_user = {
user_id: telegramUser.id,
full_name: telegramUser.first_name + (telegramUser.last_name ? ' ' + telegramUser.last_name : ''),
phone: "" // telefon alohida olinadi yoki edit_profile.php da so‘raladi
};
localStorage.setItem("tg_user", JSON.stringify(tg_user));
} else {
            console.log("Foydalanuvchi ma'lumoti localStorage da mavjud:", JSON.parse(existing));
        }
}

 tg.MainButton.setParams({ text: "Buyurtmani ko‘rish", color: "#dc3545" });
 tg.MainButton.onClick(() => {
const tgUser = JSON.parse(localStorage.getItem("tg_user")) || {};
const params = new URLSearchParams({
 user_id: tgUser.user_id,
name: encodeURIComponent(tgUser.full_name || ""),
phone: tgUser.phone || ""
});
window.location.href = `cart.php?${params.toString()}`;
});

function goToCart() {
const tgUser = JSON.parse(localStorage.getItem("tg_user")) || {};


const params = new URLSearchParams({
user_id: tgUser.user_id,
name: encodeURIComponent(tgUser.full_name),
phone: tgUser.phone
});

 window.location.href = `cart.php?${params.toString()}`;
}

// URL parametrlarini olish va localStorage ga yozish (har doim yozadi)
const urlParamsFromUrl = new URLSearchParams(window.location.search);
const userIdFromUrl = urlParamsFromUrl.get("user_id");
const fullNameFromUrl = urlParamsFromUrl.get("name");
const phoneFromUrl = urlParamsFromUrl.get("phone");

if (userIdFromUrl && fullNameFromUrl && phoneFromUrl) {
const tg_user_from_url = {
user_id: userIdFromUrl,
full_name: decodeURIComponent(fullNameFromUrl),
phone: phoneFromUrl
};
localStorage.setItem("tg_user", JSON.stringify(tg_user_from_url));
}
</script>

<header class="main-header">
  <div class="d-flex align-items-center gap-2">
    <img src="logo.png" alt="Chinor" style="max-width: 50px;" />
    <img src="logoT.png" alt="Chinor" style="max-width: 80px;" />
  </div>
  <div class="d-flex align-items-center gap-2">
  <button class="btn btn-outline-danger position-relative" onclick="goToCart()">
    <i class="fas fa-shopping-cart fa-lg text-warning"></i>
    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-gold-mini text-dark" id="cart-count">0</span>
  </button>
  <button class="profile-button" id="openProfileModal" title="Profilim">👤</button>
</div>
  
  
</header>


 <main class="content mt-header">
<div class="category-slider sticky-categories" id="category-buttons">
</div>
<div id="loader-wrapper">
<div class="loader-inner">
<img src="logo.png" alt="Chinor" class="loader-logo" />
<div class="spinner-border text-danger" role="status"></div>
</div>
</div>
<div id="loader" class="text-center py-5">
<div class="spinner-border text-danger" role="status">
<span class="visually-hidden">Yuklanmoqda...</span>
</div>
</div>
<div class="row" id="product-list"></div>
</main>
<div class="modal fade" id="profileModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content profile-modal shadow-lg border-0 rounded-4">
      <div class="modal-header border-0">
        <h5 class="modal-title text-gold fw-bold">
          <i class="bi bi-person-circle me-2"></i> Foydalanuvchi ma’lumotlari
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Yopish"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <p class="mb-1 text-gold"><i class="bi bi-person-fill me-1"></i> Ism:</p>
          <div class="modal-info-box" id="modalUserName">Noma'lum</div>
        </div>
        <div class="mb-3">
          <p class="mb-1 text-gold"><i class="bi bi-telephone-fill me-1"></i> Telefon:</p>
          <div class="modal-info-box" id="modalUserPhone">Noma'lum</div>
        </div>
        <div class="mb-3">
          <p class="mb-1 text-gold"><i class="bi bi-geo-alt-fill me-1"></i> Manzil:</p>
          <div class="modal-info-box" id="modalUserAddress">Noma'lum</div>
        </div>
        <hr class="text-secondary my-3" />
        <h6 class="text-gold mb-2"><i class="bi bi-clock-history me-1"></i> Oxirgi buyurtma:</h6>
        <ul id="last-orders" class="list-group list-group-flush small text-white-50"></ul>
      </div>
      <div class="modal-footer border-0 pt-0">
        <button id="editProfileBtn" class="btn btn-warning w-100 fw-bold">
          ✏️ Tahrirlash
        </button>
      </div>
    </div>
  </div>
</div>
<video autoplay muted loop playsinline id="background-video">
<source src="bg.mp4" type="video/mp4" />
Sizning brauzeringiz videoni qo‘llab-quvvatlamaydi.
</video>
<script>
document.addEventListener("DOMContentLoaded", () => {
const openBtn = document.getElementById("openProfileModal");
const profileModalEl = document.getElementById("profileModal");
const profileModal = new bootstrap.Modal(profileModalEl);
const addressEl = document.getElementById("modalUserAddress");



// Profil modalini ochish tugmasi
openBtn.addEventListener("click", async () => {
    const tg_user_data = JSON.parse(localStorage.getItem("tg_user")) || {}; // O'zgaruvchi nomini o'zgartirdim

    // user_id ni integerga o'giramiz va bo'sh emasligini tekshiramiz
    const user_id_int = parseInt(tg_user_data.user_id);

    if (isNaN(user_id_int) || !user_id_int) { // Agar user_id raqam bo'lmasa yoki 0 bo'lsa
        alert("Foydalanuvchi ma'lumotlari noto'g'ri formatda. Iltimos, qayta kiring yoki profilni tahrirlang.");
        console.error("Noto'g'ri user_id formatda:", tg_user_data.user_id);
        return;
    }

    // Endi tg_user obyekti yangilangan integer ID bilan yaratiladi
    const tg_user = {
        user_id: user_id_int,
        full_name: tg_user_data.full_name || '',
        phone: tg_user_data.phone || ''
    };

    

    try {
        
        // Foydalanuvchi ma'lumotlarini yuklash (get_user.php)
        const res = await fetch(`get_user.php?user_id=${tg_user.user_id}`);
        const text = await res.text();
        let data;
        try {
            data = JSON.parse(text);
        } catch (e) {
            console.error("JSON parsing xatosi (get_user.php):", e, "Javob:", text);
            alert("❌ Serverdan foydalanuvchi ma'lumotlari noto'g'ri formatda keldi.");
            return;
        }

        if (data.error) {
            alert("❌ Ma'lumot yuklanmadi: " + data.error);
            return;
        }

        document.getElementById("modalUserName").textContent = data.full_name || "Noma'lum";
        document.getElementById("modalUserPhone").textContent = data.phone || "Noma'lum";
        // Agar data.address bo'lsa uni ishlat, bo'lmasa localStorage'dagini, bo'lmasa "Noma'lum"
        addressEl.textContent = data.address || localStorage.getItem("address") || "Noma'lum";

            const updatedTgUser = {
              user_id: tg_user.user_id, // ID ni o'zgartirmaymiz (chunki u allaqachon to'g'ri olingan)
              full_name: data.full_name || "", // get_user.php dan kelgan full_name
              phone: data.phone || "" // get_user.php dan kelgan phone
          };
          localStorage.setItem("tg_user", JSON.stringify(updatedTgUser));
         

        // Oxirgi buyurtmalar...
        const ordersList = document.getElementById("last-orders");
        ordersList.innerHTML = `<li class="text-muted">Yuklanmoqda...</li>`;

        const orderRes = await fetch(`get_orders.php?user_id=${tg_user.user_id}`);
        const orderText = await orderRes.text();
        let order;
        try {
            order = JSON.parse(orderText);
        } catch (e) {
            console.error("Order JSON parsing xatosi (get_orders.php):", e, "Javob:", orderText);
            ordersList.innerHTML = `<li class="text-muted">Buyurtma ma'lumotlari noto'g'ri formatda.</li>`;
            return;
        }
        
        ordersList.innerHTML = ""; // Yuklanmoqda yozuvini o'chirish

      

        if (order && order.items) { // Bu yerda tekshiruv bor
            const li = document.createElement("li");
            li.innerHTML = `
                <strong>${escapeHtml(order.items)}</strong> —
               <span class="text-warning">${escapeHtml(String(order.total_price))} so‘m</span><br> 
        <small class="text-muted">${escapeHtml(order.created_at)}</small> `;
            ordersList.appendChild(li);

            const moreBtn = document.createElement("button");
            moreBtn.className = "btn btn-outline-warning w-100 mt-2";
            moreBtn.textContent = "🧾 Barcha buyurtmalar";
            moreBtn.onclick = () => {
                window.location.href = `orders.php?user_id=${tg_user.user_id}`;
            };
            ordersList.appendChild(moreBtn);
        } else {
            ordersList.innerHTML = `<li class="text-muted">Buyurtma topilmadi</li>`;
        }

        document.getElementById("editProfileBtn").onclick = () => {
            window.location.href = `edit_profile.php?user_id=${tg_user.user_id}`;
        };

        profileModal.show(); // Modalni ko'rsatish
    } catch (error) {
        alert("❌ Xatolik: " + error.message);
        console.error("Profil modalini ochishda xato:", error);
    }
});

 // URLda ?show_profile=1 bo‘lsa, modalni avtomatik ochish
const urlParams = new URLSearchParams(window.location.search);
if (urlParams.get("show_profile") === "1") {
// openBtn.click() ni chaqirish modalni ochish logikasini ishga tushiradi
 openBtn.click();
}

 // Modal yopilganda DOM tozalash
profileModalEl.addEventListener('hidden.bs.modal', () => {
document.body.classList.remove('modal-open');
document.body.style.overflow = 'auto';
const backdrop = document.querySelector('.modal-backdrop');
if (backdrop) backdrop.remove();
});

// HTML maxsus belgilaridan himoya qilish funksiyasi
function escapeHtml(unsafe) {
return unsafe
.replace(/&/g, "&amp;")
.replace(/</g, "&lt;")
.replace(/>/g, "&gt;")
.replace(/"/g, "&quot;")
.replace(/'/g, "&#039;");
}
});
</script>


<div class="modal fade" id="productInfoModal" tabindex="-1" aria-labelledby="productInfoLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow-lg">
      <div class="modal-header bg-dark text-white border-0">
        <h5 class="modal-title" id="modalProductTitle">Mahsulot nomi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Yopish"></button>
      </div>
      <div class="modal-body text-center bg-black text-white">
        <img id="modalProductImage" src="" alt="Mahsulot rasmi" class="img-fluid mb-3 rounded" style="max-height: 200px;">
        <p id="modalProductDesc" class="text-muted small">Mahsulot haqida izoh bu yerda ko‘rsatiladi</p>
      </div>
    </div>
  </div>
</div>


<script src="products.js?v=<?= time(); ?>"></script>
<script src="app.js?v=<?= time(); ?>"></script>
</body>
</html>