const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://localhost:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const DEFAULT_PASSWORD = 'Admin@12345';
const NEW_PASSWORD = 'Vestra@2024!Secure';

const screenshotsDir = path.join(__dirname, 'screenshots-stage84');
if (!fs.existsSync(screenshotsDir)) fs.mkdirSync(screenshotsDir, { recursive: true });

const consoleErrors = [];
const pageErrors = [];

async function capture(page, name) {
    await page.screenshot({ path: path.join(screenshotsDir, `${name}.png`), fullPage: false, timeout: 60000 });
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    page.on('console', msg => {
        if (msg.type() === 'error') {
            consoleErrors.push({ page: page.url(), text: msg.text() });
        }
    });

    page.on('pageerror', err => {
        pageErrors.push({ page: page.url(), error: err.message });
    });

    page.setDefaultNavigationTimeout(120000);
    page.setDefaultTimeout(60000);

    // Login
    await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForSelector('input[type="email"]', { timeout: 10000 });
    await page.fill('input[type="email"]', ADMIN_EMAIL);
    await page.fill('input[type="password"]', DEFAULT_PASSWORD);
    await page.click('button:has-text("Sign in")');
    await page.waitForTimeout(3000);
    await capture(page, 'after-login');

    // Handle force password change if needed
    if (page.url().includes('force-password-change')) {
        await page.waitForSelector('input[type="password"]', { timeout: 10000 });
        const passwordInputs = await page.locator('input[type="password"]').all();
        if (passwordInputs.length >= 3) {
            await passwordInputs[0].fill(DEFAULT_PASSWORD);
            await passwordInputs[1].fill(NEW_PASSWORD);
            await passwordInputs[2].fill(NEW_PASSWORD);
        }
        await page.click('button:has-text("Change Password")');
        await page.waitForTimeout(3000);
        await capture(page, 'after-password-change');
    }

    // Dashboard
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded', timeout: 60000 });
    await page.waitForTimeout(2000);
    await capture(page, 'dashboard');

    const routes = [
        '/admin/products',
        '/admin/products/create',
        '/admin/categories',
        '/admin/orders',
        '/admin/customers',
        '/admin/reviews',
        '/admin/contact-messages',
        '/admin/customer-feedbacks',
        '/admin/distributor-requests',
        '/admin/users',
        '/admin/roles',
        '/admin/permissions',
        '/admin/settings',
    ];

    for (const route of routes) {
        await page.goto(`${BASE_URL}${route}`, { waitUntil: 'domcontentloaded', timeout: 60000 });
        await page.waitForTimeout(2000);
        const name = route.replace(/\//g, '_').replace(/^_/, '');
        await capture(page, name);
    }

    await browser.close();

    const report = {
        consoleErrors,
        pageErrors,
        screenshots: fs.readdirSync(screenshotsDir).filter(f => f.startsWith('stage84_') || !f.startsWith('stage84_')),
    };

    fs.writeFileSync(path.join(__dirname, 'stage84-validation.json'), JSON.stringify(report, null, 2));
    console.log('Validation complete.');
    console.log('Console errors:', consoleErrors.length);
    console.log('Page errors:', pageErrors.length);
})();
