// ============================================
// COOKERY STORE - CUSTOMER SCRIPT
// PHP + MySQL Connected
// ============================================

const API = 'api/';

let products = [];
let categories = [];
let cart = [];
let currentUser = null;
let currentCategory = 'all';
let currentSort = 'default';
let maxPrice = 2500;
let searchQuery = '';
let siteSettings = {};

// ============================================
// API HELPER
// ============================================
async function apiCall(endpoint, method = 'GET', data = null) {
    const options = {
        method: method,
        headers: { 'Content-Type': 'application/json' }
    };
    if (data) options.body = JSON.stringify(data);
    try {
        const response = await fetch(API + endpoint, options);
        if (!response.ok) {
            console.error('HTTP Error:', response.status);
            return { success: false, message: 'Server error' };
        }
        return await response.json();
    } catch (err) {
        console.error('API Error:', err);
        return { success: false, message: 'Connection error' };
    }
}

// ============================================
// LOAD SITE SETTINGS
// ============================================
async function loadSettings() {
    const result = await apiCall('settings-get.php');
    if (result.success) {
        siteSettings = result.settings;
        applySettings();
    }
}

// ============================================
// APPLY SETTINGS
// ============================================
function applySettings() {
    const s = siteSettings;

    // Site Name (Header + Footer)
    if (s.site_name) {
        document.querySelectorAll('.logo-text h1, .footer-logo h3').forEach(el => {
            const name = s.site_name;
            const mid = Math.ceil(name.length / 2);
            el.innerHTML = name.substring(0, mid) + '<span>' + name.substring(mid) + '</span>';
        });
        document.title = s.site_name + ' - ' + (s.site_tagline || 'Online Store');
    }

    // Tagline
    if (s.site_tagline) {
        document.querySelectorAll('.logo-text p, .footer-logo p').forEach(el => {
            el.textContent = s.site_tagline;
        });
    }

    // Logo Image
    if (s.logo_image && s.logo_image.trim() !== '') {
        document.querySelectorAll('.logo-icon').forEach(el => {
            el.innerHTML = `<img src="${s.logo_image}" style="width:100%;height:100%;object-fit:cover;border-radius:inherit;">`;
        });
    }

    // Hero Title
    if (s.hero_title) {
        const heroTitle = document.getElementById('heroTitle') || document.querySelector('.hero-content h1');
        if (heroTitle) {
            const words = s.hero_title.split(' ');
            const last = words.pop();
            heroTitle.innerHTML = words.join(' ') + ' <span>' + last + '</span>';
        }
    }

    // Hero Subtitle
    if (s.hero_subtitle) {
        const heroSub = document.getElementById('heroSubtitle') || document.querySelector('.hero-content p');
        if (heroSub) heroSub.textContent = s.hero_subtitle;
    }

    // Hero Image
    if (s.hero_image) {
        const heroImg = document.getElementById('heroImage') || document.querySelector('.hero-image img');
        if (heroImg) heroImg.src = s.hero_image;
    }

    // Contact Phone
    if (s.contact_phone) {
        document.querySelectorAll('.top-right a').forEach(el => {
            if (el.innerHTML.includes('fa-phone')) {
                el.innerHTML = '<i class="fas fa-phone"></i> ' + s.contact_phone;
            }
        });
        document.querySelectorAll('.footer-col p').forEach(el => {
            if (el.innerHTML.includes('fa-phone')) {
                el.innerHTML = '<i class="fas fa-phone"></i> ' + s.contact_phone;
            }
        });
    }

    // Contact Email
    if (s.contact_email) {
        document.querySelectorAll('.top-left span').forEach(el => {
            if (el.innerHTML.includes('fa-envelope')) {
                el.innerHTML = '<i class="fas fa-envelope"></i> ' + s.contact_email;
            }
        });
        document.querySelectorAll('.footer-col p').forEach(el => {
            if (el.innerHTML.includes('fa-envelope')) {
                el.innerHTML = '<i class="fas fa-envelope"></i> ' + s.contact_email;
            }
        });
        document.querySelectorAll('.contact-item p').forEach(el => {
            if (el.textContent.includes('@')) el.textContent = s.contact_email;
        });
    }

    // Contact Address
    if (s.contact_address) {
        document.querySelectorAll('.footer-col p').forEach(el => {
            if (el.innerHTML.includes('fa-map-marker-alt')) {
                el.innerHTML = '<i class="fas fa-map-marker-alt"></i> ' + s.contact_address;
            }
        });
    }

    // Footer About
    if (s.footer_about) {
        const footerAbout = document.querySelector('.footer-about');
        if (footerAbout) footerAbout.textContent = s.footer_about;
    }
}

// ============================================
// LOAD PRODUCTS
// ============================================
async function loadProducts() {
    const result = await apiCall('products.php');
    if (result.success) {
        products = result.products.map(p => ({
            id: p.id,
            name: p.name,
            category: p.category,
            category_id: p.category_id,
            price: p.price,
            oldPrice: p.oldPrice,
            image: p.image,
            badge: p.badge,
            stock: p.stock
        }));
        renderGrids();
    }
}

// ============================================
// LOAD CATEGORIES
// ============================================
async function loadCategories() {
    const result = await apiCall('categories.php');
    if (result.success) categories = result.categories;
}

// ============================================
// CHECK SESSION
// ============================================
async function checkSession() {
    const result = await apiCall('session.php');
    if (result.logged_in) {
        currentUser = result.user;
        updateUserUI();
        await loadCart();
        await updateWishlistCount();
    }
}

// ============================================
// UPDATE USER UI
// ============================================
function updateUserUI() {
    const topbar = document.querySelector('.top-right');
    if (!topbar) return;

    if (currentUser) {
        topbar.innerHTML = `
            <a onclick="showPage('my-orders')"><i class="fas fa-box"></i> My Orders</a>
            <a><i class="fas fa-user"></i> ${currentUser.name}</a>
            <span>|</span>
            <a onclick="handleLogout()"><i class="fas fa-sign-out-alt"></i> Logout</a>
        `;
    } else {
        topbar.innerHTML = `
            <a><i class="fas fa-map-marker-alt"></i> Store Locator</a>
            <a><i class="fas fa-phone"></i> ${siteSettings.contact_phone || '+92 300 1234567'}</a>
            <a onclick="showPage('login')">Login</a>
            <span>|</span>
            <a onclick="showPage('register')">Register</a>
        `;
    }
}

// ============================================
// PRODUCT CARD
// ============================================
function productCardHTML(product) {
    const badgeHTML = product.badge
        ? `<span class="product-badge ${product.badge === 'Sale' ? 'sale' : product.badge === 'New' ? '' : 'hot'}">${product.badge}</span>`
        : '';
    const oldPriceHTML = product.oldPrice ? `<small>Rs. ${product.oldPrice.toFixed(0)}</small>` : '';
    const cartItem = cart.find(c => c.id === product.id);
    const qty = cartItem ? cartItem.qty : 0;

    const cartControl = qty > 0
        ? `<div class="cart-control">
             <button class="qty-btn" onclick="event.stopPropagation(); decreaseQty(${product.id})"><i class="fas fa-minus"></i></button>
             <span class="qty-value">${qty}</span>
             <button class="qty-btn" onclick="event.stopPropagation(); increaseQty(${product.id})"><i class="fas fa-plus"></i></button>
           </div>`
        : `<div class="cart-control">
             <button class="btn-add" onclick="event.stopPropagation(); addToCartById(${product.id})">
               <i class="fas fa-cart-plus"></i> Add
             </button>
           </div>`;

    return `
        <div class="product-card" onclick="openProduct(${product.id})">
            <div class="product-image">
                <img src="${product.image}" alt="${product.name}">
                ${badgeHTML}
                <button class="wishlist-btn" onclick="event.stopPropagation(); toggleWishlist(${product.id}, event)">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            <div class="product-info">
                <p class="product-category">${product.category || ''}</p>
                <h4>${product.name}</h4>
                <div class="product-bottom">
                    <span class="product-price">Rs. ${product.price.toFixed(0)}${oldPriceHTML}</span>
                    ${cartControl}
                </div>
            </div>
        </div>
    `;
}

// ============================================
// FILTER + SORT
// ============================================
function getFilteredProducts() {
    let list = [...products];
    if (currentCategory !== 'all') list = list.filter(p => p.category === currentCategory);
    if (searchQuery) {
        const q = searchQuery.toLowerCase();
        list = list.filter(p => p.name.toLowerCase().includes(q) || (p.category && p.category.toLowerCase().includes(q)));
    }
    list = list.filter(p => p.price <= maxPrice);
    if (currentSort === 'low-high') list.sort((a, b) => a.price - b.price);
    else if (currentSort === 'high-low') list.sort((a, b) => b.price - a.price);
    else if (currentSort === 'newest') list.sort((a, b) => b.id - a.id);
    else if (currentSort === 'popular') list.sort((a, b) => (b.badge ? 1 : 0) - (a.badge ? 1 : 0));
    return list;
}

function renderShopGrid() {
    const shopEl = document.getElementById('shopGrid');
    if (!shopEl) return;
    const list = getFilteredProducts();
    if (list.length === 0) {
        shopEl.innerHTML = `<div class="no-results"><i class="fas fa-search"></i><p>No products found</p><span>Try changing filters</span></div>`;
    } else {
        shopEl.innerHTML = list.map(productCardHTML).join('');
    }
    const countEl = document.getElementById('productCount');
    if (countEl) countEl.textContent = list.length;
}

function renderGrids() {
    const featuredEl = document.getElementById('featuredGrid');
    const bestEl = document.getElementById('bestSellers');
    const relatedEl = document.getElementById('relatedGrid');
    if (featuredEl) featuredEl.innerHTML = products.slice(0, 6).map(productCardHTML).join('');
    if (bestEl) bestEl.innerHTML = products.slice(6, 12).map(productCardHTML).join('');
    if (relatedEl) relatedEl.innerHTML = products.slice(0, 3).map(productCardHTML).join('');
    renderShopGrid();
}

// ============================================
// CART FUNCTIONS
// ============================================
async function addToCartById(id, qty = 1) {
    if (!currentUser) {
        showToast('Please login first');
        setTimeout(() => showPage('login'), 800);
        return;
    }
    const result = await apiCall('cart-add.php', 'POST', { product_id: id, quantity: qty });
    if (result.success) {
        const product = products.find(p => p.id === id);
        showToast(`${product ? product.name : 'Item'} added!`);
        await loadCart();
        renderGrids();
    } else {
        showToast(result.message || 'Failed');
    }
}

async function increaseQty(id) {
    const result = await apiCall('cart-update.php', 'POST', { product_id: id, action: 'increase' });
    if (result.success) { await loadCart(); renderGrids(); }
}

async function decreaseQty(id) {
    const result = await apiCall('cart-update.php', 'POST', { product_id: id, action: 'decrease' });
    if (result.success) { await loadCart(); renderGrids(); }
}

async function removeFromCart(productId) {
    const result = await apiCall('cart-remove.php', 'POST', { product_id: productId });
    if (result.success) { showToast('Item removed'); await loadCart(); renderGrids(); }
}

async function loadCart() {
    const result = await apiCall('cart-get.php');
    if (result.success && result.logged_in) {
        cart = result.items.map(i => ({
            id: i.product_id, cart_id: i.cart_id, name: i.name,
            price: i.price, image: i.image, qty: i.quantity
        }));
        updateCartUI(result);
    } else {
        cart = [];
        updateCartUI({ total_items: 0, subtotal: 0, delivery_fee: 0, total: 0 });
    }
}

function updateCartUI(data) {
    const totalItems = data.total_items || 0;
    const subtotal = data.subtotal || 0;
    const deliveryFee = data.delivery_fee || 0;
    const total = data.total || 0;

    document.querySelectorAll('.cart-count').forEach(el => el.textContent = totalItems);
    const headerTotal = document.getElementById('cartTotalHeader');
    if (headerTotal) headerTotal.textContent = 'Rs. ' + subtotal.toFixed(0);

    const cartList = document.getElementById('cartList');
    const cartEmpty = document.getElementById('cartEmpty');
    const subtotalEl = document.getElementById('cartSubtotal');
    const totalEl = document.getElementById('cartTotal');
    const feeEl = document.getElementById('deliveryFee');
    if (!cartList) return;

    if (cart.length === 0) {
        if (cartEmpty) cartEmpty.style.display = 'block';
        cartList.innerHTML = '';
        if (subtotalEl) subtotalEl.textContent = 'Rs. 0';
        if (totalEl) totalEl.textContent = 'Rs. 0';
        if (feeEl) feeEl.textContent = 'Rs. 0';
        return;
    }
    if (cartEmpty) cartEmpty.style.display = 'none';

    cartList.innerHTML = cart.map((item) => `
        <div class="cart-item">
            <img src="${item.image}" alt="${item.name}">
            <div class="cart-item-info">
                <h4>${item.name}</h4>
                <p>Rs. ${item.price.toFixed(0)} × ${item.qty} = Rs. ${(item.price * item.qty).toFixed(0)}</p>
            </div>
            <div class="cart-control">
                <button class="qty-btn" onclick="decreaseQty(${item.id})"><i class="fas fa-minus"></i></button>
                <span class="qty-value">${item.qty}</span>
                <button class="qty-btn" onclick="increaseQty(${item.id})"><i class="fas fa-plus"></i></button>
            </div>
            <button class="cart-item-remove" onclick="removeFromCart(${item.id})"><i class="fas fa-trash"></i></button>
        </div>
    `).join('');

    if (subtotalEl) subtotalEl.textContent = 'Rs. ' + subtotal.toFixed(0);
    if (feeEl) feeEl.textContent = 'Rs. ' + deliveryFee.toFixed(0);
    if (totalEl) totalEl.textContent = 'Rs. ' + total.toFixed(0);
}

function addToCartFromDetail() {
    addToCartById(window.currentProductId, 1);
}

// ============================================
// WISHLIST
// ============================================
async function toggleWishlist(productId, event) {
    if (event) event.stopPropagation();

    if (!currentUser) {
        showToast('Please login to use wishlist');
        setTimeout(() => showPage('login'), 800);
        return;
    }

    const btn = event ? event.currentTarget : null;
    const icon = btn ? btn.querySelector('i') : null;
    const isCurrentlyWished = icon && icon.classList.contains('fas');

    if (isCurrentlyWished) {
        const result = await apiCall('wishlist-remove.php', 'POST', { product_id: productId });
        if (result.success) {
            if (icon) { icon.classList.remove('fas'); icon.classList.add('far'); }
            showToast('Removed from wishlist');
        }
    } else {
        const result = await apiCall('wishlist-add.php', 'POST', { product_id: productId });
        if (result.success) {
            if (icon) { icon.classList.remove('far'); icon.classList.add('fas'); }
            showToast('Added to wishlist!');
        } else {
            showToast(result.message || 'Failed');
        }
    }

    // Header count update karo
    await updateWishlistCount();
}
async function loadWishlist() {
    // Update header count FIRST
    await updateWishlistCount();

    const container = document.getElementById('wishlistList');
    const emptyState = document.getElementById('wishlistEmpty');

    if (!container) return;

    if (!currentUser) {
        if (container) container.innerHTML = '';
        if (emptyState) emptyState.innerHTML = `<i class="fas fa-user"></i><p>Please login</p><button class="btn btn-primary" onclick="showPage('login')" style="margin-top:20px;">Login</button>`;
        return;
    }

    const result = await apiCall('wishlist-get.php');

    if (!result.success || !result.items || result.items.length === 0) {
        if (container) container.innerHTML = '';
        if (emptyState) {
            emptyState.style.display = 'block';
            emptyState.innerHTML = `<i class="fas fa-heart"></i><p>Your wishlist is empty</p><span>Save your favorite products here</span><button class="btn btn-primary" onclick="showPage('shop')" style="margin-top:20px;">Browse Products</button>`;
        }
        return;
    }

    if (emptyState) emptyState.style.display = 'none';
    container.innerHTML = `<div class="products-grid">${result.items.map(p => productCardHTML(p)).join('')}</div>`;
}

// ============================================
// UPDATE WISHLIST COUNT (Header)
// ============================================
async function updateWishlistCount() {
    // Header me wishlist count element dhoondo
    const wishlistAction = document.querySelector('.action-btn:first-child');
    if (!wishlistAction) return;

    // Agar login nahi, to 0
    if (!currentUser) {
        const strong = wishlistAction.querySelector('.action-text strong');
        if (strong) strong.textContent = '0 items';
        return;
    }

    const result = await apiCall('wishlist-get.php');
    const count = (result.success && result.items) ? result.items.length : 0;

    const strong = wishlistAction.querySelector('.action-text strong');
    if (strong) strong.textContent = count + ' items';
}
// ============================================
// PAGE NAVIGATION
// ============================================
function showPage(pageName) {
    document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
    const page = document.getElementById('page-' + pageName);
    if (page) {
        page.classList.add('active');
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
    document.querySelectorAll('.nav-link').forEach(link => {
        link.classList.remove('active');
        if (link.dataset.page === pageName) link.classList.add('active');
    });
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) navLinks.classList.remove('mobile-open');
    const icon = document.getElementById('menuIcon');
    if (icon) icon.textContent = '☰';

    if (pageName === 'my-orders') loadMyOrders();
    if (pageName === 'wishlist') loadWishlist();
}

function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    const icon = document.getElementById('menuIcon');
    if (!navLinks || !icon) return;
    navLinks.classList.toggle('mobile-open');
    icon.textContent = navLinks.classList.contains('mobile-open') ? '✕' : '☰';
}

function openProduct(id) {
    const product = products.find(p => p.id === id);
    if (!product) return;
    document.getElementById('mainProductImage').src = product.image;
    document.getElementById('detailCategory').textContent = product.category || '';
    document.getElementById('detailName').textContent = product.name;
    document.getElementById('detailPrice').textContent = 'Rs. ' + product.price.toFixed(0);
    document.getElementById('breadcrumbProduct').textContent = product.name;
    window.currentProductId = id;
    showPage('product');
}

// ============================================
// SEARCH
// ============================================
function liveSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;
    searchQuery = input.value.trim();

    const shopPage = document.getElementById('page-shop');
    if (searchQuery.length > 0) {
        if (shopPage && !shopPage.classList.contains('active')) {
            document.querySelectorAll('.page').forEach(p => p.classList.remove('active'));
            shopPage.classList.add('active');
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
                if (link.dataset.page === 'shop') link.classList.add('active');
            });
        }
        const titleEl = document.getElementById('shopTitle');
        if (titleEl) titleEl.textContent = `Search: "${searchQuery}"`;
        const subEl = document.getElementById('shopSubtitle');
        if (subEl) subEl.textContent = `Showing results for "${searchQuery}"`;
    } else {
        const titleEl = document.getElementById('shopTitle');
        if (titleEl) titleEl.textContent = currentCategory === 'all' ? 'All Products' : currentCategory;
        const subEl = document.getElementById('shopSubtitle');
        if (subEl) subEl.textContent = 'Browse our fresh grocery collection';
    }
    renderShopGrid();
}

function performSearch() {
    liveSearch();
    showPage('shop');
}

// ============================================
// CATEGORY
// ============================================
function filterByCategory(cat) {
    currentCategory = cat;
    searchQuery = '';
    const input = document.getElementById('searchInput');
    if (input) input.value = '';
    document.querySelectorAll('#categoryFilter a').forEach(a => {
        a.classList.remove('active');
        if (a.dataset.cat === cat) a.classList.add('active');
    });
    const titleEl = document.getElementById('shopTitle');
    if (titleEl) titleEl.textContent = cat === 'all' ? 'All Products' : cat;
    const subEl = document.getElementById('shopSubtitle');
    if (subEl) subEl.textContent = 'Browse our fresh grocery collection';
    renderShopGrid();
}

document.addEventListener('click', function(e) {
    const catLink = e.target.closest('#categoryFilter a');
    if (!catLink) return;
    e.preventDefault();
    filterByCategory(catLink.dataset.cat);
});

function goToCategory(event, categoryName) {
    if (event) { event.stopPropagation(); event.preventDefault(); }
    const dropdown = document.getElementById('categoriesDropdown');
    if (dropdown) dropdown.classList.remove('show');
    searchQuery = '';
    const input = document.getElementById('searchInput');
    if (input) input.value = '';
    currentCategory = categoryName;
    document.querySelectorAll('#categoryFilter a').forEach(a => {
        a.classList.remove('active');
        if (a.dataset.cat === categoryName) a.classList.add('active');
    });
    showPage('shop');
    const titleEl = document.getElementById('shopTitle');
    if (titleEl) titleEl.textContent = categoryName;
    const subEl = document.getElementById('shopSubtitle');
    if (subEl) subEl.textContent = `Browse ${categoryName}`;
    renderShopGrid();
}

function sortProducts(value) {
    currentSort = value;
    renderShopGrid();
}

document.addEventListener('input', function(e) {
    if (e.target && e.target.id === 'priceRange') {
        maxPrice = parseInt(e.target.value);
        const label = document.getElementById('priceLabel');
        if (label) label.textContent = `Rs. 0 - Rs. ${maxPrice}`;
        renderShopGrid();
    }
});

function toggleCategories(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('categoriesDropdown');
    if (dropdown) dropdown.classList.toggle('show');
}

document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('categoriesDropdown');
    const navCat = document.querySelector('.nav-categories');
    if (dropdown && navCat && !navCat.contains(e.target)) {
        dropdown.classList.remove('show');
    }
});

// ============================================
// LOGIN / REGISTER / LOGOUT
// ============================================
async function handleUserLogin(event) {
    event.preventDefault();
    const form = event.target;
    const email = form.querySelector('input[type="email"]').value;
    const password = form.querySelector('input[type="password"]').value;
    const result = await apiCall('login.php', 'POST', { email, password });
    if (result.success) {
        currentUser = result.user;
        updateUserUI();
        showToast('Welcome back, ' + result.user.name + '!');
        await loadCart();
        await loadWishlist();
        renderGrids();
        showPage('home');
        form.reset();
    } else {
        showToast(result.message || 'Login failed');
    }
}

async function handleUserRegister(event) {
    event.preventDefault();
    const form = event.target;
    const inputs = form.querySelectorAll('input');
    const name = inputs[0].value;
    const email = inputs[1].value;
    const phone = inputs[2].value;
    const password = inputs[3].value;
    const result = await apiCall('register.php', 'POST', { name, email, phone, password });
    if (result.success) {
        currentUser = result.user;
        updateUserUI();
        showToast('Account created! Welcome ' + result.user.name);
        await loadCart();
        renderGrids();
        showPage('home');
        form.reset();
    } else {
        showToast(result.message || 'Registration failed');
    }
}

async function handleLogout() {
    if (!confirm('Logout karna chahte ho?')) return;
    await apiCall('logout.php', 'POST');
    currentUser = null;
    cart = [];
    updateUserUI();
    updateCartUI({ total_items: 0, subtotal: 0, delivery_fee: 0, total: 0 });
    renderGrids();
    showToast('Logged out');
    showPage('home');
}

// ============================================
// CHECKOUT
// ============================================
async function checkout() {
    if (!currentUser) {
        showToast('Please login first');
        setTimeout(() => showPage('login'), 800);
        return;
    }
    if (cart.length === 0) {
        showToast('Your cart is empty!');
        return;
    }
    const name = prompt('Enter your name:', currentUser.name || '');
    if (!name) return;
    const phone = prompt('Enter phone number:', currentUser.phone || '');
    if (!phone) return;
    const address = prompt('Enter delivery address:', '');
    if (!address) return;
    const city = prompt('Enter your city:', currentUser.city || '');
    if (!city) return;

    const result = await apiCall('place-order.php', 'POST', {
        name, phone, email: currentUser.email, address, city, payment_method: 'COD'
    });

    if (result.success) {
        showToast('Order placed! #' + result.order_number);
        await loadCart();
        renderGrids();
        setTimeout(() => showPage('my-orders'), 1000);
    } else {
        showToast(result.message || 'Order failed');
    }
}

// ============================================
// MY ORDERS
// ============================================
async function loadMyOrders() {
    const container = document.getElementById('myOrdersList');
    if (!container) return;
    if (!currentUser) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-user"></i><p>Please login</p><button class="btn btn-primary" onclick="showPage('login')" style="margin-top:20px;">Login</button></div>`;
        return;
    }
    container.innerHTML = '<p style="text-align:center;padding:40px;">Loading...</p>';
    const result = await apiCall('my-orders.php');
    if (!result.success || !result.orders || result.orders.length === 0) {
        container.innerHTML = `<div class="empty-state"><i class="fas fa-box"></i><p>No orders yet</p><span>Start shopping</span><button class="btn btn-primary" onclick="showPage('shop')" style="margin-top:20px;">Shop Now</button></div>`;
        return;
    }
    const badges = { pending: 'warning', processing: 'info', shipped: 'info', completed: 'success', cancelled: 'cancel' };
    container.innerHTML = result.orders.map(order => `
        <div style="background:#fff;border-radius:16px;padding:24px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,0.05);border:1px solid #f1f5f9;">
            <div style="display:flex;justify-content:space-between;align-items:center;padding-bottom:16px;border-bottom:1px solid #f1f5f9;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
                <div>
                    <div style="font-size:18px;font-weight:800;color:#059669;">${order.order_number}</div>
                    <div style="font-size:13px;color:#64748b;margin-top:4px;"><i class="fas fa-calendar"></i> ${new Date(order.created_at).toLocaleDateString()}</div>
                </div>
                <span class="badge ${badges[order.status] || 'pending'}">${order.status.toUpperCase()}</span>
            </div>
            ${order.items.map(item => `
                <div style="display:flex;gap:12px;padding:10px;background:#f8fafc;border-radius:10px;margin-bottom:8px;align-items:center;">
                    <img src="${item.image}" style="width:50px;height:50px;object-fit:cover;border-radius:8px;">
                    <div style="flex:1;"><div style="font-weight:700;font-size:14px;">${item.name}</div><div style="font-size:12px;color:#64748b;">Rs. ${item.price} × ${item.quantity}</div></div>
                    <div style="font-weight:800;color:#059669;">Rs. ${item.total.toFixed(0)}</div>
                </div>
            `).join('')}
            <div style="display:flex;justify-content:space-between;align-items:center;padding-top:12px;border-top:1px solid #f1f5f9;">
                <div style="font-size:13px;color:#64748b;"><i class="fas fa-map-marker-alt"></i> ${order.address}</div>
                <div style="font-size:18px;font-weight:800;color:#1e293b;">Total: <span style="color:#059669;">Rs. ${order.total.toFixed(0)}</span></div>
            </div>
        </div>
    `).join('');
}

// ============================================
// TOAST
// ============================================
function showToast(message) {
    const toast = document.getElementById('toast');
    const msg = document.getElementById('toastMsg');
    if (!toast || !msg) return;
    msg.textContent = message;
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 2500);
}

// ============================================
// FORMS
// ============================================
function submitContact(e) {
    e.preventDefault();
    showToast('Thank you! We will contact you soon.');
    e.target.reset();
}

function subscribeNews(e) {
    e.preventDefault();
    showToast('Subscribed! Check your email.');
    e.target.reset();
}

// ============================================
// INIT
// ============================================
document.addEventListener('DOMContentLoaded', async function() {
    await loadSettings();
    await checkSession();
    await loadCategories();
    await loadProducts();
    await loadCart();
    showPage('home');
});