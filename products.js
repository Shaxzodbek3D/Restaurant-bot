let cart = [];

function loadCart() {
    try {
        const stored = JSON.parse(localStorage.getItem("cart"));
        cart = Array.isArray(stored) ? stored : [];
    } catch {
        cart = [];
    }
    updateCartCount();
}

function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
}

function updateCartCount() {
    const count = cart.reduce((sum, item) => sum + item.qty, 0);
    document.getElementById("cart-count").innerText = count;
}

function updateQty(productId, delta) {
    const found = cart.find(item => String(item.id) === String(productId));
    if (found) {
        found.qty += delta;
        if (found.qty <= 0) {
            cart = cart.filter(item => String(item.id) !== String(productId));
        }
        saveCart();
        updateCartCount();
        renderProducts(window.filteredProducts);
    }
}

function addToCart(product) {
    const found = cart.find(item => String(item.id) === String(product.id));
    if (found) {
        found.qty++;
    } else {
        cart.push({ ...product, qty: 1 });
    }
    saveCart();
    updateCartCount();
    renderProducts(window.filteredProducts);
}

function renderCategoryButtons(categories) {
    const container = document.getElementById("category-buttons");
    if (!container) return;
    container.innerHTML = "";
    const allBtn = document.createElement("button");
    allBtn.className = "btn btn-outline-danger active";
    allBtn.textContent = "Barchasi";
    allBtn.onclick = () => filterProducts("all");
    container.appendChild(allBtn);
    categories.forEach(cat => {
        const btn = document.createElement("button");
        btn.className = "btn btn-outline-danger text-capitalize";
        btn.textContent = cat;
        btn.onclick = () => filterProducts(cat);
        container.appendChild(btn);
    });
}

function renderProducts(list) {
    window.filteredProducts = list;
    const container = document.getElementById("product-list");
    container.innerHTML = "";
    if (!list || list.length === 0) {
        container.innerHTML = '<p class="text-center text-white-50">Hech qanday mahsulot topilmadi.</p>';
        return;
    }
    list.forEach(product => {
        const col = document.createElement("div");
        col.className = "col-6 col-md-4 mb-3";
        const card = document.createElement("div");
        card.className = "card product-card h-100 text-center";
        const img = document.createElement("img");
        img.src = product.image;
        img.className = "card-img-top product-image";
        img.alt = product.name;
        img.style.cursor = "pointer";
        img.addEventListener('click', () => showProductInfo(product));
        const cardBody = document.createElement("div");
        cardBody.className = "card-body d-flex flex-column p-2";
        const title = document.createElement("h5");
        title.className = "card-title product-name";
        title.textContent = product.name;
        const price = document.createElement("p");
        price.className = "card-text text-gold product-price mt-auto";
        price.textContent = `${Number(product.price).toLocaleString()} so'm`;
        const buttonsContainer = document.createElement("div");
        buttonsContainer.className = "d-flex justify-content-center align-items-center gap-2 mt-2 flex-wrap";
        
        const itemInCart = cart.find(item => String(item.id) === String(product.id));

        if (itemInCart) {
            const qtyControl = document.createElement("div");
            qtyControl.className = "qty-control d-flex align-items-center bg-gold text-dark rounded-pill";
            const decreaseBtn = document.createElement("button");
            decreaseBtn.className = "btn btn-qty";
            decreaseBtn.innerHTML = "−";
            decreaseBtn.addEventListener('click', () => updateQty(product.id, -1));
            const qtyValue = document.createElement("span");
            qtyValue.className = "qty-value";
            qtyValue.textContent = itemInCart.qty;
            const increaseBtn = document.createElement("button");
            increaseBtn.className = "btn btn-qty";
            increaseBtn.innerHTML = "+";
            increaseBtn.addEventListener('click', () => updateQty(product.id, 1));
            qtyControl.append(decreaseBtn, qtyValue, increaseBtn);
            buttonsContainer.appendChild(qtyControl);
        } else {
            const addToCartBtn = document.createElement("button");
            addToCartBtn.className = "btn btn-gold rounded-pill btn-add-cart";
            addToCartBtn.innerHTML = `<i class="bi bi-cart-plus"></i> Savatchaga`;
            addToCartBtn.addEventListener('click', () => addToCart(product));
            buttonsContainer.appendChild(addToCartBtn);
        }
        cardBody.append(title, price, buttonsContainer);
        card.append(img, cardBody);
        col.appendChild(card);
        container.appendChild(col);
    });
}

function showProductInfo(product) {
    document.getElementById("modalProductTitle").innerText = product.name;
    document.getElementById("modalProductImage").src = product.image;
    document.getElementById("modalProductDesc").innerText = product.description;
    const modal = new bootstrap.Modal(document.getElementById('productInfoModal'));
    modal.show();
}

function filterProducts(category) {
    const buttons = document.querySelectorAll("#category-buttons .btn");
    buttons.forEach(btn => btn.classList.remove("active"));
    const activeBtn = [...buttons].find(btn => btn.textContent.toLowerCase() === category.toLowerCase());
    if (activeBtn) activeBtn.classList.add("active");
    const filtered = category.toLowerCase() === "all" ? window.products : window.products.filter(p => p.category && p.category.toLowerCase() === category.toLowerCase());
    renderProducts(filtered);
}

function clearCart() {
    // Agar savat bo'sh bo'lsa, hech narsa qilmaymiz
    if (cart.length === 0) {
        Swal.fire({
            title: 'Savatcha boʻsh',
            text: 'Savatchangizda hozircha mahsulot yoʻq.',
            icon: 'info',
            confirmButtonColor: '#d4af37'
        });
        return;
    }

    // Foydalanuvchidan tasdiqlashni so'raymiz
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
        // Agar foydalanuvchi "Ha" tugmasini bossa
        if (result.isConfirmed) {
            // 1. Asosiy 'cart' massivini bo'shatamiz
            cart = [];
            
            // 2. O'zgarishni brauzer xotirasiga saqlaymiz
            saveCart();
            
            // 3. Yuqoridagi hisoblagichni yangilaymiz
            updateCartCount();

            // Muvaffaqiyatli o'chirilgani haqida xabar
            Swal.fire(
                'Oʻchirildi!',
                'Savatchangiz muvaffaqiyatli tozalandi.',
                'success'
            );

            // 4. Sahifani qayta yuklaymiz (yoki DOMni o'zgartiramiz)
            // Eng osoni - sahifani qayta yuklash. Shunda "Savatcha bo'sh" degan yozuv chiqadi.
            setTimeout(() => {
                location.reload();
            }, 1500); // 1.5 soniyadan keyin
        }
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const loaderWrapper = document.getElementById("loader-wrapper");
    const productList = document.getElementById("product-list");
    loadCart();
    fetch("get_products.php")
        .then(res => {
            if (!res.ok) { throw new Error('Serverdan javob noto‘g‘ri: ' + res.status); }
            return res.json();
        })
        .then(data => {
            if (loaderWrapper) { loaderWrapper.style.display = "none"; }
            if (productList) { productList.style.visibility = "visible"; }
            window.products = data;
            filterProducts("all");
        })
        .catch(error => {
            if (loaderWrapper) { loaderWrapper.style.display = "none"; }
            if (productList) { productList.style.visibility = "visible"; }
            console.error("Mahsulotlarni yuklashda xato:", error);
            productList.innerHTML = '<p class="text-center text-danger">Xatolik: Mahsulotlarni yuklab bo‘lmadi.</p>';
        });
    fetch("get_categories.php")
        .then(res => res.json())
        .then(data => {
            if (data && !data.error) { renderCategoryButtons(data); }
        })
        .catch(error => console.error("Kategoriyalarni yuklashda xato:", error));
});