const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const DEFAULT_PASSWORD = 'Admin@12345';
const NEW_PASSWORD = 'Vestra@2024!Secure';

const screenshotsDir = path.join(__dirname, 'screenshots-stage871');
if (!fs.existsSync(screenshotsDir)) fs.mkdirSync(screenshotsDir, { recursive: true });

const consoleErrors = [];
const pageErrors = [];

async function capture(page, name, fullPage = false) {
    await page.screenshot({ path: path.join(screenshotsDir, `${name}.png`), fullPage, timeout: 60000 });
}

async function resetAdminUser() {
    const { execSync } = require('child_process');
    const scriptPath = path.join(__dirname, 'reset-admin-user.sh');
    try {
        execSync(`bash "${scriptPath}"`, { stdio: 'inherit' });
    } catch (e) {
        console.warn('Could not reset admin user; continuing anyway.', e.message);
    }
}

async function login(page) {
    // Try the main admin entry point first; Filament will redirect to login if unauthenticated.
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded' });

    // If we are on the login page, fill credentials.
    if (page.url().includes('/admin/login')) {
        await page.fill('input[type="email"]', ADMIN_EMAIL);
        await page.fill('input[type="password"]', DEFAULT_PASSWORD);
        await page.click('button:has-text("Sign in")');
        await page.waitForURL(url => url.pathname.includes('/admin') && !url.pathname.includes('/admin/login'), { timeout: 30000 });
    }

    // Handle forced password change if it still appears.
    if (page.url().includes('force-password-change')) {
        await page.waitForSelector('input[type="password"]', { timeout: 10000 });
        const inputs = await page.locator('input[type="password"]').all();
        if (inputs.length >= 3) {
            await inputs[0].fill(DEFAULT_PASSWORD);
            await inputs[1].fill(NEW_PASSWORD);
            await inputs[2].fill(NEW_PASSWORD);
            await page.click('button:has-text("Change Password")');
            await page.waitForURL(url => url.pathname.includes('/admin') && !url.pathname.includes('force-password-change') && !url.pathname.includes('/admin/login'), { timeout: 30000 });
        }
    }

    // Ensure we landed inside the admin panel.
    const currentUrl = page.url();
    if (!currentUrl.includes('/admin') || currentUrl.includes('force-password-change') || currentUrl.includes('/admin/login')) {
        throw new Error(`Login did not reach admin panel. Current URL: ${currentUrl}`);
    }

    // Wait for the sidebar/navigation to confirm the page is fully rendered.
    await page.waitForSelector('nav, [data-testid="sidebar"], .fi-sidebar', { timeout: 15000 }).catch(() => null);
    await page.waitForTimeout(1500);
}

async function closeMobileSidebar(page) {
    await page.evaluate(() => {
        if (window.Alpine && Alpine.store('sidebar') && Alpine.store('sidebar').close) {
            Alpine.store('sidebar').close();
        }
    }).catch(() => null);
    await page.waitForTimeout(500);
}

(async () => {
    await resetAdminUser();

    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    page.on('console', msg => {
        if (msg.type() === 'error') consoleErrors.push({ url: page.url(), text: msg.text() });
    });
    page.on('pageerror', err => {
        pageErrors.push({ url: page.url(), error: err.message });
    });

    page.setDefaultNavigationTimeout(120000);
    page.setDefaultTimeout(60000);

    await login(page);

    // Products list
    await page.goto(`${BASE_URL}/admin/products`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);
    await capture(page, 'stage871_products_list_desktop', true);

    // Products list with filters open
    let filtersOpened = false;

    // Try common selectors first.
    const filterSelectors = [
        'button[aria-label="Filters" i]',
        'button:has(.fi-icon svg[data-icon*="funnel"])',
        'button:has(svg[data-icon*="funnel"])',
        'button:has(.fi-ta-filters-trigger)',
        '.fi-ta-filters-trigger',
        'button:has-text("Filters")',
        '[data-testid="filters-trigger"]',
    ];
    for (const selector of filterSelectors) {
        const candidate = page.locator(selector).first();
        if (await candidate.isVisible().catch(() => false)) {
            await candidate.click();
            await page.waitForTimeout(1500);
            filtersOpened = true;
            break;
        }
    }

    // Fallback: locate the funnel icon via its SVG path and click its parent button.
    if (!filtersOpened) {
        filtersOpened = await page.evaluate(() => {
            const svgs = Array.from(document.querySelectorAll('svg'));
            const funnel = svgs.find(s => s.innerHTML.includes('M3 4a1'));
            if (funnel) {
                const btn = funnel.closest('button');
                if (btn) { btn.click(); return true; }
            }
            return false;
        });
        if (filtersOpened) await page.waitForTimeout(1500);
    }

    // Final fallback: try URL query string.
    if (!filtersOpened) {
        await page.goto(`${BASE_URL}/admin/products?tableFiltersOpen=true`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(3000);
    }

    await capture(page, 'stage871_products_filters_desktop', true);

    // Filtered view evidence
    await page.goto(`${BASE_URL}/admin/products?tableFilters[status][value]=active`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);
    await capture(page, 'stage871_products_filtered_desktop', true);

    // Create product
    await page.goto(`${BASE_URL}/admin/products/create`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);
    await capture(page, 'stage871_product_create_desktop', true);

    // Edit first product
    await page.goto(`${BASE_URL}/admin/products`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    const firstEdit = page.locator('a[href*="/admin/products/"][href$="/edit"]').first();
    if (await firstEdit.isVisible().catch(() => false)) {
        await firstEdit.click();
        await page.waitForTimeout(3000);
        await capture(page, 'stage871_product_edit_desktop', true);
    }

    // Categories list
    await page.goto(`${BASE_URL}/admin/categories`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);
    await capture(page, 'stage871_categories_list_desktop', true);

    // Create category
    await page.goto(`${BASE_URL}/admin/categories/create`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);
    await capture(page, 'stage871_category_create_desktop', true);

    // Edit first category
    await page.goto(`${BASE_URL}/admin/categories`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    const firstCatEdit = page.locator('a[href*="/admin/categories/"][href$="/edit"]').first();
    if (await firstCatEdit.isVisible().catch(() => false)) {
        await firstCatEdit.click();
        await page.waitForTimeout(3000);
        await capture(page, 'stage871_category_edit_desktop', true);
    }

    // Tablet viewport
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.goto(`${BASE_URL}/admin/products`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    await capture(page, 'stage871_products_list_tablet', true);

    // Mobile viewport
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(`${BASE_URL}/admin/products`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    await closeMobileSidebar(page);
    await capture(page, 'stage871_products_list_mobile', true);

    await browser.close();

    fs.writeFileSync(path.join(__dirname, 'stage871-validation.json'), JSON.stringify({ consoleErrors, pageErrors }, null, 2));

    console.log('Done. Console errors:', consoleErrors.length, 'Page errors:', pageErrors.length);
})();
