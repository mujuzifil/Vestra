// Product data - all supplied assets
const products = [
    {
        id: 1,
        name: 'Heavy Duty Detergent',
        description: 'Powerful clean. Fresher. Brighter. 10x enzyme power for tough stains.',
        image: 'Heavy%20Duty%20Detergent.png'
    },
    {
        id: 2,
        name: 'Silk Care',
        description: 'Specially formulated for silk and other delicate fabrics.',
        image: 'Silk%20Care.png'
    },
    {
        id: 3,
        name: 'EcoSuit Cleaner',
        description: 'Cleans without fading or fabric damage. Perfect for suits.',
        image: 'EcoSuit%20Cleaner.png'
    },
    {
        id: 4,
        name: 'Pro Finish Garment Spray',
        description: 'Garment structuring spray for crisp, wrinkle-free finishes.',
        image: 'Pro%20Finish%20Garment%20spray.png'
    },
    {
        id: 5,
        name: 'Stain Pro',
        description: 'Removes tea, coffee, wine, fruit, grass, leaf & tree sap stains.',
        image: 'Stain%20Pro.png'
    },
    {
        id: 6,
        name: 'Wool & Delicate Fabric Wash',
        description: 'Gentle care for wool, cashmere, silk blends & delicates.',
        image: 'Wool%20&%20Delicate%20Fabric%20Wash.png'
    }
];

// DOM Elements
const navbar = document.getElementById('navbar');
const mobileMenuToggle = document.getElementById('mobileMenuToggle');
const navMenu = document.getElementById('navMenu');
const productsGrid = document.getElementById('productsGrid');
const cartCount = document.getElementById('cartCount');
const cartNotification = document.getElementById('cartNotification');

let cart = 0;

// Initialize
function init() {
    renderProducts();
    setupEventListeners();
    setupScrollEffects();
    setupRevealAnimations();
    injectCartBounceStyle();
    generateHeroAtmosphere();
}

// Render products dynamically
function renderProducts() {
    if (!productsGrid) return;

    productsGrid.innerHTML = products.map((product, index) => {
        const baseSrc = product.image;
        const srcset = `${baseSrc.replace('.png', '-400w.png')} 400w, ${baseSrc.replace('.png', '-800w.png')} 800w, ${baseSrc} 1200w`;
        return `
        <article class="product-card reveal" data-id="${product.id}" style="transition-delay: ${index * 80}ms">
            <div class="product-image">
                <img src="${baseSrc}" srcset="${srcset}" sizes="(max-width: 480px) 90vw, (max-width: 768px) 45vw, (max-width: 1200px) 30vw, 380px" alt="${product.name}" loading="lazy" width="300" height="400" decoding="async">
            </div>
            <div class="product-info">
                <h3>${product.name}</h3>
                <p>${product.description}</p>
                <div class="product-actions">
                    <a href="#" class="btn btn-view btn-small" role="button" onclick="event.preventDefault(); showProductDetails(${product.id})">View Details</a>
                    <button class="btn btn-cart btn-small" type="button" aria-label="Add ${product.name} to cart" onclick="addToCart(${product.id})">
                        <i class="fas fa-cart-plus" aria-hidden="true"></i> Add to Cart
                    </button>
                </div>
            </div>
        </article>
    `}).join('');
}

// Add to cart functionality
function addToCart(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    cart++;
    if (cartCount) {
        cartCount.textContent = cart;
        cartCount.classList.add('bounce');
        setTimeout(() => cartCount.classList.remove('bounce'), 300);
    }

    showCartNotification(product.name);
}

// Show cart notification
function showCartNotification(productName) {
    if (!cartNotification) return;

    cartNotification.querySelector('span').textContent = `${productName} added to cart`;
    cartNotification.classList.add('show');

    setTimeout(() => {
        cartNotification.classList.remove('show');
    }, 3000);
}

// Show product details
function showProductDetails(productId) {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    alert(`${product.name}\n\n${product.description}\n\nMore product information coming soon.`);
}

// Setup event listeners
function setupEventListeners() {
    if (mobileMenuToggle && navMenu) {
        mobileMenuToggle.addEventListener('click', toggleMobileMenu);

        navMenu.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', closeMobileMenu);
        });
    }

    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            closeMobileMenu();
        }
    });
}

function toggleMobileMenu() {
    const isOpen = navMenu.classList.toggle('active');
    mobileMenuToggle.classList.toggle('active', isOpen);
    mobileMenuToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    document.body.style.overflow = isOpen ? 'hidden' : '';
}

function closeMobileMenu() {
    navMenu.classList.remove('active');
    mobileMenuToggle.classList.remove('active');
    mobileMenuToggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
}

// Scroll effects
function setupScrollEffects() {
    window.addEventListener('scroll', () => {
        navbar.classList.toggle('scrolled', window.scrollY > 50);
        updateActiveNavLink();
    });

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;

            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                const offset = 90;
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

// Update active nav link based on scroll position
function updateActiveNavLink() {
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-menu a');

    let current = '';
    sections.forEach(section => {
        const sectionTop = section.offsetTop - 120;
        const sectionHeight = section.offsetHeight;
        if (window.scrollY >= sectionTop && window.scrollY < sectionTop + sectionHeight) {
            current = section.getAttribute('id');
        }
    });

    navLinks.forEach(link => {
        link.classList.remove('active');
        if (link.getAttribute('href') === `#${current}`) {
            link.classList.add('active');
        }
    });
}

// Reveal animations with Intersection Observer
function setupRevealAnimations() {
    const revealElements = document.querySelectorAll('.reveal');

    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('active');
                revealObserver.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.12,
        rootMargin: '0px 0px -60px 0px'
    });

    revealElements.forEach(el => revealObserver.observe(el));
}

// Inject cart count bounce animation
function injectCartBounceStyle() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes cartBounce {
            0%, 100% { transform: translate(25%, -25%) scale(1); }
            50% { transform: translate(25%, -25%) scale(1.35); }
        }
        .cart-count.bounce {
            animation: cartBounce 0.3s var(--transition-bounce);
        }
    `;
    document.head.appendChild(style);
}

// Generate floating particles and bubbles for hero atmosphere
function generateHeroAtmosphere() {
    const particlesContainer = document.getElementById('heroParticles');
    const bubblesContainer = document.getElementById('heroBubbles');
    if (!particlesContainer || !bubblesContainer) return;

    // Clear existing
    particlesContainer.innerHTML = '';
    bubblesContainer.innerHTML = '';

    // Particles
    for (let i = 0; i < 40; i++) {
        const particle = document.createElement('span');
        particle.className = 'hero-particle';
        particle.style.left = `${Math.random() * 100}%`;
        particle.style.top = `${Math.random() * 100}%`;
        particle.style.animationDelay = `${Math.random() * 12}s`;
        particle.style.animationDuration = `${10 + Math.random() * 8}s`;
        particle.style.opacity = `${0.2 + Math.random() * 0.5}`;
        particlesContainer.appendChild(particle);
    }

    // Bubbles
    for (let i = 0; i < 18; i++) {
        const bubble = document.createElement('span');
        bubble.className = 'hero-bubble';
        const size = 6 + Math.random() * 28;
        bubble.style.width = `${size}px`;
        bubble.style.height = `${size}px`;
        bubble.style.left = `${Math.random() * 100}%`;
        bubble.style.top = `${40 + Math.random() * 60}%`;
        bubble.style.animationDelay = `${Math.random() * 10}s`;
        bubble.style.animationDuration = `${8 + Math.random() * 8}s`;
        bubblesContainer.appendChild(bubble);
    }
}

// Run initialization when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
