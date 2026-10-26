// ============================================
// PRODUCTS DATA (40 items)
// ============================================
const products = [
    { id:1, name:'Fresh Red Apples (1kg)', category:'Fruits & Vegetables', price:280, oldPrice:390, image:'https://images.unsplash.com/photo-1568702846914-96b305d2aaeb?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:2, name:'Organic Bananas (1kg)', category:'Fruits & Vegetables', price:150, oldPrice:null, image:'https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?w=700&auto=format&fit=crop', badge:null },
    { id:3, name:'Fresh Whole Milk 1L', category:'Dairy & Eggs', price:220, oldPrice:null, image:'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=700&auto=format&fit=crop', badge:'New' },
    { id:4, name:'Farm Fresh Eggs (12 pcs)', category:'Dairy & Eggs', price:340, oldPrice:420, image:'https://images.unsplash.com/photo-1582722872445-44dc5f7e3c8f?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:5, name:'Whole Wheat Bread', category:'Bakery', price:180, oldPrice:null, image:'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=700&auto=format&fit=crop', badge:null },
    { id:6, name:'Fresh Tomatoes (500g)', category:'Fruits & Vegetables', price:120, oldPrice:null, image:'https://images.unsplash.com/photo-1592924357228-91a4daadcfea?w=700&auto=format&fit=crop', badge:null },
    { id:7, name:'Chicken Breast (1kg)', category:'Meat & Seafood', price:950, oldPrice:1200, image:'https://images.unsplash.com/photo-1604503468506-a8da13d82791?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:8, name:'Fresh Orange Juice 1L', category:'Beverages', price:290, oldPrice:null, image:'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=700&auto=format&fit=crop', badge:null },
    { id:9, name:'Cheddar Cheese Block', category:'Dairy & Eggs', price:580, oldPrice:null, image:'https://images.unsplash.com/photo-1486297678162-eb2a19b0a32d?w=700&auto=format&fit=crop', badge:'New' },
    { id:10, name:'Fresh Broccoli (500g)', category:'Fruits & Vegetables', price:220, oldPrice:null, image:'https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?w=700&auto=format&fit=crop', badge:null },
    { id:11, name:'Chocolate Chip Cookies', category:'Snacks', price:320, oldPrice:420, image:'https://images.unsplash.com/photo-1499636136210-6f4ee915583e?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:12, name:'Basmati Rice 5kg', category:'Pantry Staples', price:1490, oldPrice:null, image:'https://images.unsplash.com/photo-1586201375761-83865001e31c?w=700&auto=format&fit=crop', badge:null },
    { id:13, name:'Fresh Strawberries (500g)', category:'Fruits & Vegetables', price:480, oldPrice:650, image:'https://images.unsplash.com/photo-1464965911861-746a04b4bca6?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:14, name:'Greek Yogurt 500g', category:'Dairy & Eggs', price:390, oldPrice:null, image:'https://images.unsplash.com/photo-1488477181946-6428a0291777?w=700&auto=format&fit=crop', badge:null },
    { id:15, name:'Fresh Carrots (1kg)', category:'Fruits & Vegetables', price:130, oldPrice:null, image:'https://images.unsplash.com/photo-1598170845058-32b9d6a5da37?w=700&auto=format&fit=crop', badge:'New' },
    { id:16, name:'Ground Coffee 250g', category:'Beverages', price:890, oldPrice:null, image:'https://images.unsplash.com/photo-1447933601403-0c6688de566e?w=700&auto=format&fit=crop', badge:null },
    { id:17, name:'Fresh Salmon Fillet', category:'Meat & Seafood', price:1890, oldPrice:2300, image:'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:18, name:'Avocados (Pack of 3)', category:'Fruits & Vegetables', price:490, oldPrice:null, image:'https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?w=700&auto=format&fit=crop', badge:null },
    { id:19, name:'Potato Chips Family Pack', category:'Snacks', price:280, oldPrice:null, image:'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=700&auto=format&fit=crop', badge:null },
    { id:20, name:'Extra Virgin Olive Oil 1L', category:'Pantry Staples', price:1190, oldPrice:1450, image:'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:21, name:'Fresh Spinach Bunch', category:'Fruits & Vegetables', price:90, oldPrice:null, image:'https://images.unsplash.com/photo-1576045057995-568f588f82fb?w=700&auto=format&fit=crop', badge:null },
    { id:22, name:'Almond Milk 1L', category:'Dairy & Eggs', price:490, oldPrice:640, image:'https://images.unsplash.com/photo-1600788886242-5c96aabe3757?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:23, name:'Fresh Blueberries (250g)', category:'Fruits & Vegetables', price:690, oldPrice:null, image:'https://images.unsplash.com/photo-1498557850523-fd3d118b962e?w=700&auto=format&fit=crop', badge:'New' },
    { id:24, name:'Butter Croissants (4 pcs)', category:'Bakery', price:450, oldPrice:null, image:'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=700&auto=format&fit=crop', badge:null },
    { id:25, name:'Green Tea Bags (100 pcs)', category:'Beverages', price:590, oldPrice:790, image:'https://images.unsplash.com/photo-1597481499750-3e6b22637e12?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:26, name:'Fresh Bell Peppers (3 pcs)', category:'Fruits & Vegetables', price:320, oldPrice:null, image:'https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?w=700&auto=format&fit=crop', badge:null },
    { id:27, name:'Mozzarella Cheese 250g', category:'Dairy & Eggs', price:520, oldPrice:null, image:'https://images.unsplash.com/photo-1628088062854-d1870b4553da?w=700&auto=format&fit=crop', badge:'New' },
    { id:28, name:'Dark Chocolate Bar', category:'Snacks', price:280, oldPrice:390, image:'https://images.unsplash.com/photo-1548907040-4baa42d10919?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:29, name:'Honey Jar 500g', category:'Pantry Staples', price:850, oldPrice:null, image:'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=700&auto=format&fit=crop', badge:null },
    { id:30, name:'Fresh Mangoes (1kg)', category:'Fruits & Vegetables', price:550, oldPrice:790, image:'https://images.unsplash.com/photo-1553279768-865429fa0078?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:31, name:'Fresh Cucumber (500g)', category:'Fruits & Vegetables', price:110, oldPrice:null, image:'https://images.unsplash.com/photo-1449300079323-02e209d9d3a6?w=700&auto=format&fit=crop', badge:null },
    { id:32, name:'Brown Bread Loaf', category:'Bakery', price:250, oldPrice:320, image:'https://images.unsplash.com/photo-1598373182133-52452f7691ef?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:33, name:'Fresh Grapes (500g)', category:'Fruits & Vegetables', price:380, oldPrice:null, image:'https://images.unsplash.com/photo-1537640538966-79f369143f8f?w=700&auto=format&fit=crop', badge:null },
    { id:34, name:'Organic Peanut Butter', category:'Pantry Staples', price:780, oldPrice:null, image:'https://images.unsplash.com/photo-1621607512214-68297480165e?w=700&auto=format&fit=crop', badge:'New' },
    { id:35, name:'Ice Cream Vanilla 1L', category:'Frozen Foods', price:620, oldPrice:850, image:'https://images.unsplash.com/photo-1497034825429-c343d7c6a68f?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:36, name:'Fresh Lettuce Head', category:'Fruits & Vegetables', price:140, oldPrice:null, image:'https://images.unsplash.com/photo-1622206151226-18ca2c9ab4a1?w=700&auto=format&fit=crop', badge:null },
    { id:37, name:'Sparkling Water (6 pack)', category:'Beverages', price:490, oldPrice:null, image:'https://images.unsplash.com/photo-1523362628745-0c100150b504?w=700&auto=format&fit=crop', badge:null },
    { id:38, name:'Tortilla Chips', category:'Snacks', price:320, oldPrice:420, image:'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=700&auto=format&fit=crop', badge:'Sale' },
    { id:39, name:'Fresh Pineapple', category:'Fruits & Vegetables', price:580, oldPrice:null, image:'https://images.unsplash.com/photo-1550258987-190a2d41a8ba?w=700&auto=format&fit=crop', badge:'New' },
    { id:40, name:'Roasted Almonds 250g', category:'Snacks', price:890, oldPrice:null, image:'https://images.unsplash.com/photo-1508061253366-f7da158b6d46?w=700&auto=format&fit=crop', badge:null }
];

// ============================================
// STATE
// ============================================
let cart = [];
let currentCategory = 'all';
let currentSort = 'default';
let maxPrice = 2500;
let searchQuery = '';

// ============================================
// PRODUCT CARD HTML
// ============================================
function productCardHTML(product) {
    const badgeHTML = product.badge
        ? `<span class="product-badge ${product.badge === 'Sale' ? 'sale' : product.badge === 'New' ? '' : 'hot'}">${product.badge}</span>`
        : '';

    const oldPriceHTML = product.oldPrice
        ? `<small>Rs. ${product.oldPrice.toFixed(0)}</small>`
        : '';

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
                <button class="wishlist-btn" onclick="event.stopPropagation(); toggleWishlist(event)">
                    <i class="far fa-heart"></i>
                </button>
            </div>
            <div class="product-info">
                <p class="product-category">${product.category}</p>
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
// FILTER + SORT LOGIC
// ============================================
function getFilteredProducts() {
    let list = [...products];

    // Category filter
    if (currentCategory !== 'all') {
        list = list.filter(p => p.category === currentCategory);
    }

    // Search filter
    if (searchQuery && searchQuery.length > 0) {
        const q = searchQuery.toLowerCase();
        list = list.filter(p =>
            p.name.toLowerCase().includes(q) ||
            p.category.toLowerCase().includes(q)
        );
    }

    // Price filter
    list = list.filter(p => p.price <= maxPrice);

    // Sort
    if (currentSort === 'low-high') {
        list.sort((a, b) => a.price - b.price);
    } else if (currentSort === 'high-low') {
        list.sort((a, b) => b.price - a.price);
    } else if (currentSort === 'newest') {
        list.sort((a, b) => b.id - a.id);
    } else if (currentSort === 'popular') {
        list.sort((a, b) => (b.badge ? 1 : 0) - (a.badge ? 1 : 0));
    }

    return list;
}

// ============================================
// RENDER SHOP GRID
// ============================================
function renderShopGrid() {
    const shopEl = document.getElementById('shopGrid');
    if (!shopEl) return;

    const list = getFilteredProducts();

    if (list.length === 0) {
        shopEl.innerHTML = `
            <div class="no-results">
                <i class="fas fa-search"></i>
                <p>No products found</p>
                <span>Try changing filters or search term</span>
            </div>
        `;
    } else {
        shopEl.innerHTML = list.map(productCardHTML).join('');
    }

    const countEl = document.getElementById('productCount');
    if (countEl) countEl.textContent = list.length;
}

// ============================================
// RENDER ALL GRIDS
// ============================================
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
// LIVE SEARCH (typing pe)
// ============================================
function liveSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;

    searchQuery = input.value.trim();

    // Agar search box me kuch likha hai to shop page kholo
    const shopPage = document.getElementById('page-shop');
    const homePage = document.getElementById('page-home');

    if (searchQuery.length > 0) {
        if (shopPage && !shopPage.classList.contains('active')) {
            // Direct shop activate (without scroll)
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

// ============================================
// SEARCH (button / enter)
// ============================================
function performSearch() {
    const input = document.getElementById('searchInput');
    if (!input) return;

    searchQuery = input.value.trim();

    // Category reset
    currentCategory = 'all';
    document.querySelectorAll('#categoryFilter a').forEach(a => {
        a.classList.remove('active');
        if (a.dataset.cat === 'all') a.classList.add('active');
    });

    showPage('shop');

    const titleEl = document.getElementById('shopTitle');
    const subEl = document.getElementById('shopSubtitle');

    if (searchQuery.length > 0) {
        if (titleEl) titleEl.textContent = `Search: "${searchQuery}"`;
        if (subEl) subEl.textContent = `Showing results for "${searchQuery}"`;
    } else {
        if (titleEl) titleEl.textContent = 'All Products';
        if (subEl) subEl.textContent = 'Browse our fresh grocery collection';
    }

    renderShopGrid();
}

// ============================================
// CATEGORY FILTER (sidebar)
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

// Sidebar filter click
document.addEventListener('click', function(e) {
    const catLink = e.target.closest('#categoryFilter a');
    if (!catLink) return;

    e.preventDefault();
    filterByCategory(catLink.dataset.cat);
});

// ============================================
// SORT
// ============================================
function sortProducts(value) {
    currentSort = value;
    renderShopGrid();
}

// ============================================
// PRICE RANGE
// ============================================
document.addEventListener('input', function(e) {
    if (e.target && e.target.id === 'priceRange') {
        maxPrice = parseInt(e.target.value);
        const label = document.getElementById('priceLabel');
        if (label) label.textContent = `Rs. 0 - Rs. ${maxPrice}`;
        renderShopGrid();
    }
});

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

    // Close mobile menu
    const navLinks = document.querySelector('.nav-links');
    if (navLinks) navLinks.classList.remove('mobile-open');

    // Reset menu icon
    const icon = document.getElementById('menuIcon');
    if (icon) icon.textContent = '☰';
}

// ============================================
// MOBILE MENU TOGGLE
// ============================================
function toggleMenu() {
    const navLinks = document.querySelector('.nav-links');
    const icon = document.getElementById('menuIcon');

    if (!navLinks || !icon) return;

    navLinks.classList.toggle('mobile-open');

    if (navLinks.classList.contains('mobile-open')) {
        icon.textContent = '✕';
    } else {
        icon.textContent = '☰';
    }
}

// ============================================
// BROWSE CATEGORIES DROPDOWN
// ============================================
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
// GO TO CATEGORY
// ============================================
function goToCategory(event, categoryName) {
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const dropdown = document.getElementById('categoriesDropdown');
    if (dropdown) dropdown.classList.remove('show');

    // Reset search
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

// ============================================
// OPEN PRODUCT DETAIL
// ============================================
function openProduct(id) {
    const product = products.find(p => p.id === id);
    if (!product) return;

    document.getElementById('mainProductImage').src = product.image;
    document.getElementById('detailCategory').textContent = product.category;
    document.getElementById('detailName').textContent = product.name;
    document.getElementById('detailPrice').textContent = 'Rs. ' + product.price.toFixed(0);
    document.getElementById('breadcrumbProduct').textContent = product.name;

    window.currentProductId = id;
    showPage('product');
}

// ============================================
// CART FUNCTIONS
// ============================================
function addToCartById(id, qty = 1) {
    const product = products.find(p => p.id === id);
    if (!product) return;

    const existing = cart.find(item => item.id === id);
    if (existing) {
        existing.qty += qty;
    } else {
        cart.push({ ...product, qty });
    }

    renderGrids();
    updateCart();
    showToast(`${product.name} added to cart!`);
}

function increaseQty(id) {
    const item = cart.find(c => c.id === id);
    if (item) {
        item.qty++;
        renderGrids();
        updateCart();
    }
}

function decreaseQty(id) {
    const item = cart.find(c => c.id === id);
    if (item) {
        item.qty--;
        if (item.qty <= 0) {
            cart = cart.filter(c => c.id !== id);
        }
        renderGrids();
        updateCart();
    }
}

function addToCartFromDetail() {
    addToCartById(window.currentProductId, 1);
}

function removeFromCart(index) {
    cart.splice(index, 1);
    renderGrids();
    updateCart();
    showToast('Item removed from cart');
}

// ============================================
// UPDATE CART UI
// ============================================
function updateCart() {
    const totalItems = cart.reduce((sum, item) => sum + item.qty, 0);
    const subtotal = cart.reduce((sum, item) => sum + (item.price * item.qty), 0);
    const deliveryFee = cart.length > 0 ? 150 : 0;
    const total = subtotal + deliveryFee;

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

    cartList.innerHTML = cart.map((item, index) => `
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
            <button class="cart-item-remove" onclick="removeFromCart(${index})">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    `).join('');

    if (subtotalEl) subtotalEl.textContent = 'Rs. ' + subtotal.toFixed(0);
    if (feeEl) feeEl.textContent = 'Rs. ' + deliveryFee.toFixed(0);
    if (totalEl) totalEl.textContent = 'Rs. ' + total.toFixed(0);
}

// ============================================
// WISHLIST
// ============================================
function toggleWishlist(event) {
    const btn = event.currentTarget;
    const icon = btn.querySelector('i');
    if (icon.classList.contains('far')) {
        icon.classList.remove('far');
        icon.classList.add('fas');
        showToast('Added to wishlist!');
    } else {
        icon.classList.remove('fas');
        icon.classList.add('far');
        showToast('Removed from wishlist');
    }
}

// ============================================
// CHECKOUT
// ============================================
function checkout() {
    if (cart.length === 0) {
        showToast('Your cart is empty!');
        return;
    }
    alert('Checkout will work after backend is connected!');
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
function handleUserLogin(e) {
    e.preventDefault();
    alert('Login will work after backend is connected!');
}

function handleUserRegister(e) {
    e.preventDefault();
    alert('Registration will work after backend is connected!');
}

function submitContact(e) {
    e.preventDefault();
    alert('Thank you! We will get back to you soon.');
    e.target.reset();
}

function subscribeNews(e) {
    e.preventDefault();
    alert('Subscribed successfully! Check your email for 10% off code.');
    e.target.reset();
}

// ============================================
// INIT
// ============================================
document.addEventListener('DOMContentLoaded', function() {
    renderGrids();
    updateCart();
    showPage('home');
});