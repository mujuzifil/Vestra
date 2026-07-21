const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const BASE_URL = 'http://127.0.0.1:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const DEFAULT_PASSWORD = 'Admin@12345';
const NEW_PASSWORD = 'Vestra@2024!Secure';

const screenshotsDir = path.join(__dirname, 'screenshots-stage85');
if (!fs.existsSync(screenshotsDir)) fs.mkdirSync(screenshotsDir, { recursive: true });

const consoleErrors = [];
const pageErrors = [];

async function capture(page, name) {
    await page.screenshot({ path: path.join(screenshotsDir, `${name}.png`), timeout: 60000 });
}

function resetAdminUser() {
    const scriptPath = path.join(__dirname, 'reset-admin-user.sh');
    try {
        execSync(`bash "${scriptPath}"`, { stdio: 'inherit' });
        console.log('Reset admin user for password-change flow.');
    } catch (e) {
        console.warn('Could not reset admin user via docker; continuing anyway.', e.message);
    }
}

(async () => {
    resetAdminUser();

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

    // Login
    await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[type="email"]', ADMIN_EMAIL);
    await page.fill('input[type="password"]', DEFAULT_PASSWORD);
    await Promise.all([
        page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 15000 }).catch(() => null),
        page.click('button:has-text("Sign in")'),
    ]);
    await page.waitForTimeout(3000);

    const passwordInputs = await page.locator('input[type="password"]').all();
    const isForcePasswordChange = passwordInputs.length >= 3;

    if (isForcePasswordChange || page.url().includes('force-password-change')) {
        await capture(page, 'stage85_force-password-change');
        if (passwordInputs.length >= 3) {
            await passwordInputs[0].fill(DEFAULT_PASSWORD);
            await passwordInputs[1].fill(NEW_PASSWORD);
            await passwordInputs[2].fill(NEW_PASSWORD);
        }
        await Promise.all([
            page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => null),
            page.click('button:has-text("Change Password")'),
        ]);
        await page.waitForTimeout(5000);
        console.log('URL after password change:', page.url());
    }

    // Dashboard with shell features
    const currentUrl = page.url();
    if (! currentUrl.includes('/admin') || currentUrl.includes('force-password-change')) {
        console.log('Navigating to /admin explicitly...');
        await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(3000);
    }
    console.log('Dashboard URL:', page.url());
    await capture(page, 'stage85_dashboard');

    // Open notification panel
    await page.click('.fi-notification-trigger');
    await page.waitForTimeout(1000);
    await capture(page, 'stage85_notification-panel');
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // Open profile menu
    await page.click('.fi-user-menu-trigger');
    await page.waitForTimeout(1000);
    await capture(page, 'stage85_profile-menu');
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // Open command palette via global search trigger (Ctrl+K is wired via x-mousetrap)
    await page.click('button:has-text("Search orders, products, customers")');
    await page.waitForTimeout(1500);
    await capture(page, 'stage85_command-palette');
    await page.keyboard.press('Escape');
    await page.waitForTimeout(500);

    // Test pages
    const routes = [
        '/admin/products',
        '/admin/orders',
        '/admin/customers',
        '/admin/settings',
        '/admin/users',
        '/admin/roles',
    ];

    const results = [];
    for (const route of routes) {
        try {
            const response = await page.goto(`${BASE_URL}${route}`, { waitUntil: 'domcontentloaded' });
            await page.waitForTimeout(2000);
            const name = route.replace(/\//g, '_').replace(/^_/, '');
            await capture(page, `stage85_${name}`);
            results.push({ route, status: response?.status(), url: page.url() });
        } catch (e) {
            results.push({ route, error: e.message });
        }
    }

    // Mobile viewport
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    await capture(page, 'stage85_dashboard_mobile');

    await browser.close();

    fs.writeFileSync(path.join(__dirname, 'stage85-validation.json'), JSON.stringify({ consoleErrors, pageErrors, results }, null, 2));

    console.log('Done. Console errors:', consoleErrors.length, 'Page errors:', pageErrors.length);
    console.log('Results:', JSON.stringify(results, null, 2));
})();
