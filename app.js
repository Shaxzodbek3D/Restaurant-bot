
window.products = [];
window.cart = JSON.parse(localStorage.getItem("cart")) || [];

function decreaseQty(product) {
  const found = window.cart.find(item => item.id === product.id);
  if (found && found.qty > 1) {
    found.qty--;
  } else {
    window.cart = window.cart.filter(item => item.id !== product.id);
  }
  saveCart();
  updateCartCount();
  renderProducts(window.products);
}

window.onload = () => {
  loadCart();

  const loader = document.getElementById("loader");
  const productList = document.getElementById("product-list");
  const loaderWrapper = document.getElementById("loader-wrapper");
  
if (loaderWrapper) loaderWrapper.style.display = "none";


  fetch("get_products.php")
    .then(res => res.json())
    .then(data => {
      window.products = data;
      renderProducts(data);
      if (loader) loader.style.display = "none";
      if (productList) productList.style.display = "flex";
    });

  fetch("get_categories.php")
    .then(res => res.json())
    .then(categories => {
      if (!Array.isArray(categories)) {
        console.error("Kategoriya JSON formatda emas:", categories);
        return;
      }

      const container = document.getElementById("category-buttons");
      container.innerHTML = `<button class="btn btn-outline-danger active" onclick="filterProducts('all')">Barchasi</button>`;
      categories.forEach(cat => {
        const btn = document.createElement("button");
        btn.className = "btn btn-outline-danger text-capitalize";
        btn.textContent = cat;
        btn.onclick = () => filterProducts(cat);
        container.appendChild(btn);
      });
    });
};

