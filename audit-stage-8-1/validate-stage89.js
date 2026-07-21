const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const DEFAULT_PASSWORD = 'Admin@12345';
const NEW_PASSWORD = 'Vestra@2024!Secure';

const screenshotsDir = path.join(__dirname, 'screenshots-stage89');
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
        await page.waitForURL(url => url.pathname.includes('/admin') && !url.pathname.includes('/admin/login'), { timeout: 60000, waitUntil: 'load' });
    }

    if (page.url().includes('force-password-change')) {
        await page.waitForSelector('input[type="password"]', { timeout: 10000 });
        const inputs = await page.locator('input[type="password"]').all();
        if (inputs.length >= 3) {
            await inputs[0].fill(DEFAULT_PASSWORD);
            await inputs[1].fill(NEW_PASSWORD);
            await inputs[2].fill(NEW_PASSWORD);
            await page.click('button:has-text("Change Password")');
            await page.waitForURL(url => url.pathname.includes('/admin') && !url.pathname.includes('force-password-change') && !url.pathname.includes('/admin/login'), { timeout: 60000, waitUntil: 'load' });
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

async function visitAndCapture(page, slug, name, fullPage = true) {
    await page.goto(`${BASE_URL}/admin/${slug}`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2500);
    await capture(page, name, fullPage);
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

    // Settings dashboard
    await visitAndCapture(page, 'settings-dashboard', 'stage89_settings_dashboard_desktop');

    // Group edit pages
    const groups = [
        'settings/general/edit',
        'settings/business/edit',
        'settings/commerce/edit',
        'settings/orders/edit',
        'settings/payments/edit',
        'settings/inventory/edit',
        'settings/notifications/edit',
        'settings/email/edit',
        'settings/localization/edit',
        'settings/security/edit',
        'settings/integrations/edit',
    ];

    for (const group of groups) {
        const name = 'stage89_' + group.replace(/\//g, '_') + '_desktop';
        await visitAndCapture(page, group, name);
    }

    // System information
    await visitAndCapture(page, 'system-information', 'stage89_system_information_desktop');

    // All settings list
    await visitAndCapture(page, 'settings', 'stage89_settings_list_desktop');

    // Test save on general settings
    await page.goto(`${BASE_URL}/admin/settings/general/edit`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    const appNameInput = page.locator('input[id*="app_name"], input[name*="app_name"]').first();
    await appNameInput.fill('VESTRA Admin Test');
    await page.click('button:has-text("Save changes")');
    await page.waitForTimeout(2000);
    await capture(page, 'stage89_settings_general_save_desktop', true);

    // Revert
    await appNameInput.fill('VESTRA Admin');
    await page.click('button:has-text("Save changes")');
    await page.waitForTimeout(2000);

    // Tablet views
    await page.setViewportSize({ width: 1024, height: 768 });
    for (const slug of ['settings-dashboard', 'settings/general/edit', 'system-information']) {
        await page.goto(`${BASE_URL}/admin/${slug}`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        await capture(page, `stage89_${slug.replace(/\//g, '_')}_tablet`, true);
    }

    // Mobile views
    await page.setViewportSize({ width: 390, height: 844 });
    for (const slug of ['settings-dashboard', 'settings/general/edit']) {
        await page.goto(`${BASE_URL}/admin/${slug}`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(2000);
        await closeMobileSidebar(page);
        await capture(page, `stage89_${slug.replace(/\//g, '_')}_mobile`, true);
    }

    await browser.close();

    fs.writeFileSync(path.join(__dirname, 'stage89-validation.json'), JSON.stringify({ consoleErrors, pageErrors }, null, 2));

    console.log('Done. Console errors:', consoleErrors.length, 'Page errors:', pageErrors.length);

    if (consoleErrors.length > 0 || pageErrors.length > 0) {
        process.exit(1);
    }
})();
