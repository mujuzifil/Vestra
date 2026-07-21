const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const DEFAULT_PASSWORD = 'Admin@12345';
const NEW_PASSWORD = 'Vestra@2024!Secure';

const screenshotsDir = path.join(__dirname, 'screenshots-stage874');
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
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded' });

    if (page.url().includes('/admin/login')) {
        await page.fill('input[type="email"]', ADMIN_EMAIL);
        await page.fill('input[type="password"]', DEFAULT_PASSWORD);
        await page.click('button:has-text("Sign in")');
        await page.waitForURL(url => url.pathname.includes('/admin') && !url.pathname.includes('/admin/login'), { timeout: 30000, waitUntil: 'domcontentloaded' });
    }

    if (page.url().includes('force-password-change')) {
        await page.waitForSelector('input[type="password"]', { timeout: 10000 });
        const inputs = await page.locator('input[type="password"]').all();
        if (inputs.length >= 3) {
            await inputs[0].fill(DEFAULT_PASSWORD);
            await inputs[1].fill(NEW_PASSWORD);
            await inputs[2].fill(NEW_PASSWORD);
            await page.click('button:has-text("Change Password")');
            await page.waitForURL(url => url.pathname.includes('/admin') && !url.pathname.includes('force-password-change') && !url.pathname.includes('/admin/login'), { timeout: 30000, waitUntil: 'domcontentloaded' });
        }
    }

    const currentUrl = page.url();
    if (!currentUrl.includes('/admin') || currentUrl.includes('force-password-change') || currentUrl.includes('/admin/login')) {
        throw new Error(`Login did not reach admin panel. Current URL: ${currentUrl}`);
    }

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

async function captureListAndDetail(page, module, filterParam, filterName) {
    // List
    await page.goto(`${BASE_URL}/admin/${module}`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);
    await capture(page, `stage874_${module}_list_desktop`, true);

    // Capture first record href
    const firstHref = await page.locator('td a[href*="/admin/' + module + '/"]').first().getAttribute('href').catch(() => null);

    // Filtered list
    await page.goto(`${BASE_URL}/admin/${module}?${filterParam}`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);
    await capture(page, `stage874_${module}_filtered_desktop`, true);

    // Detail
    if (firstHref) {
        const detailUrl = firstHref.startsWith('http') ? firstHref : `${BASE_URL}${firstHref}`;
        await page.goto(detailUrl, { waitUntil: 'domcontentloaded' });
        await page.waitForURL(url => url.pathname.match(new RegExp(`/admin/${module}/\\d+$`)) !== null, { timeout: 15000, waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(3000);
        await capture(page, `stage874_${module}_detail_desktop`, true);
    }
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

    // Reviews
    await captureListAndDetail(page, 'reviews', 'tableFilters[status][value]=pending', 'Pending');

    // Customer Feedback
    await captureListAndDetail(page, 'customer-feedbacks', 'tableFilters[unread][isActive]=1', 'Unread');

    // Contact Messages
    await captureListAndDetail(page, 'contact-messages', 'tableFilters[unread][isActive]=1', 'Unread');

    // Tablet views
    await page.setViewportSize({ width: 1024, height: 768 });
    for (const module of ['reviews', 'customer-feedbacks', 'contact-messages']) {
        await page.goto(`${BASE_URL}/admin/${module}`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        await capture(page, `stage874_${module}_list_tablet`, true);
    }

    // Mobile views
    await page.setViewportSize({ width: 390, height: 844 });
    for (const module of ['reviews', 'customer-feedbacks', 'contact-messages']) {
        await page.goto(`${BASE_URL}/admin/${module}`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        await closeMobileSidebar(page);
        await capture(page, `stage874_${module}_list_mobile`, true);
    }

    await browser.close();

    fs.writeFileSync(path.join(__dirname, 'stage874-validation.json'), JSON.stringify({ consoleErrors, pageErrors }, null, 2));

    console.log('Done. Console errors:', consoleErrors.length, 'Page errors:', pageErrors.length);
})();
