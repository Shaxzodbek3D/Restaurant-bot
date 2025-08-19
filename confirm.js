document.addEventListener("DOMContentLoaded", function () {
  const tgUser = JSON.parse(localStorage.getItem("tg_user")) || {};
  const cart = JSON.parse(localStorage.getItem("cart")) || [];

  const userId = tgUser.user_id;
  if (!userId || isNaN(userId)) {
    console.error("❌ Foydalanuvchi aniqlanmadi yoki noto‘g‘ri.");
    return;
  }

  // Form va elementlar
  const fullNameEl = document.querySelector("input[name='full_name']");
  const phoneEl = document.querySelector("input[name='phone']");
  const addressEl = document.getElementById("address");
  const paymentMethodEl = document.querySelector("select[name='payment_method']");
  const orderTypeEl = document.querySelector("select[name='order_type']");
  const form = document.querySelector("form");

  // Mahsulotlar va umumiy summa uchun hidden inputlar
  const hiddenItemsInput = document.createElement("input");
  hiddenItemsInput.type = "hidden";
  hiddenItemsInput.name = "items";
  hiddenItemsInput.value = JSON.stringify(cart);

  const hiddenTotalInput = document.createElement("input");
  hiddenTotalInput.type = "hidden";
  hiddenTotalInput.name = "total";
  hiddenTotalInput.value = cart.reduce((sum, item) => sum + item.price * item.qty, 0);

  const hiddenUserId = document.createElement("input");
  hiddenUserId.type = "hidden";
  hiddenUserId.name = "user_id";
  hiddenUserId.value = userId;

  form.appendChild(hiddenItemsInput);
  form.appendChild(hiddenTotalInput);
  form.appendChild(hiddenUserId);

  // Foydalanuvchi ma'lumotlarini olish
  fetch(`get_user.php?user_id=${userId}`)
    .then(res => res.json())
    .then(data => {
      if (data.error) return;
      fullNameEl.value = data.full_name || "";
      phoneEl.value = data.phone || "";
      if (data.payment_method === "Click") {
        paymentMethodEl.value = "Click";
      } else if (data.payment_method === "Oldindan") {
        paymentMethodEl.value = "Oldindan";
      } else {
        paymentMethodEl.value = "Naqd";
      }
    })
    .catch(err => {
      console.error("❌ Foydalanuvchi ma'lumotlarini yuklashda xatolik:", err);
    });

  // Form yuborilishidan oldin yangi qiymatlarni yangilash
  form.addEventListener("submit", () => {
    hiddenItemsInput.value = JSON.stringify(cart);
    hiddenTotalInput.value = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
    hiddenUserId.value = userId;
  });
});
