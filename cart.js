// cart.js (to‘liq yangilangan)
window.cart = JSON.parse(localStorage.getItem("cart")) || [];

function loadCart() {
  try {
    const stored = JSON.parse(localStorage.getItem("cart"));
    window.cart = Array.isArray(stored) ? stored : [];
  } catch {
    window.cart = [];
  }
  renderCart();
}

function saveCart() {
  localStorage.setItem("cart", JSON.stringify(window.cart));
}

function renderCart() {
  const container = document.getElementById("cart-items");
  const totalEl = document.getElementById("total-price");
  container.innerHTML = "";

  let total = 0;

  if (!window.cart || window.cart.length === 0) {
    container.innerHTML = `
      <div class="text-center py-5">
        <img src="https://img.freepik.com/premium-psd/empty-cart-shopping-3d-style-empty-state_431668-1937.jpg" alt="Bo'sh savat" style="width: 150px;">
        <h5 class="mt-3 text-muted">Savatchangiz hozircha bo'sh...</h5>
      </div>
    `;
    totalEl.textContent = "0 so'm";
    return;
  }

  window.cart.forEach(item => {
    const itemTotal = item.price * item.qty;
    total += itemTotal;

    const div = document.createElement("div");
    div.className = "card mb-3";
    div.innerHTML = `
      <div class="row g-0 align-items-center">
        <div class="col-4">
          <img src="${item.image}" class="img-fluid rounded-start" alt="${item.name}">
        </div>
        <div class="col-8">
          <div class="card-body">
            <h5 class="card-title">${item.name}</h5>
            <p class="card-text">
              ${item.price.toLocaleString()} so'm x ${item.qty} = <strong>${itemTotal.toLocaleString()} so'm</strong>
            </p>
            <div class="d-flex justify-content-start align-items-center gap-2">
              <button class="btn btn-sm btn-outline-danger" onclick="decreaseQty(${item.id})">−</button>
              <strong>${item.qty}</strong>
              <button class="btn btn-sm btn-outline-success" onclick='addToCart(${JSON.stringify(item)})'>+</button>
            </div>
          </div>
        </div>
      </div>
    `;
    container.appendChild(div);
  });

  totalEl.textContent = total.toLocaleString() + " so'm";
}

function decreaseQty(id) {
  const found = window.cart.find(item => item.id === id);
  if (found && found.qty > 1) {
    found.qty--;
  } else {
    window.cart = window.cart.filter(item => item.id !== id);
  }
  saveCart();
  renderCart();
}

window.onload = loadCart;
