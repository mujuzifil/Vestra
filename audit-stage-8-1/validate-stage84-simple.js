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

(async () => {
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

    await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[type="email"]', ADMIN_EMAIL);
    await page.fill('input[type="password"]', DEFAULT_PASSWORD);
    await page.click('button:has-text("Sign in")');
    await page.waitForTimeout(5000);
    await page.screenshot({ path: path.join(screenshotsDir, 'stage84_after-login.png'), timeout: 60000 });

    if (page.url().includes('force-password-change')) {
        const passwordInputs = await page.locator('input[type="password"]').all();
        if (passwordInputs.length >= 3) {
            await passwordInputs[0].fill(DEFAULT_PASSWORD);
            await passwordInputs[1].fill(NEW_PASSWORD);
            await passwordInputs[2].fill(NEW_PASSWORD);
        }
        await page.click('button:has-text("Change Password")');
        await page.waitForTimeout(5000);
        await page.screenshot({ path: path.join(screenshotsDir, 'stage84_after-password-change.png'), timeout: 60000 });
    }

    const routes = [
        '/admin',
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
            const name = route === '/admin' ? 'dashboard' : route.replace(/\//g, '_').replace(/^_/, '');
            await page.screenshot({ path: path.join(screenshotsDir, `stage84_${name}.png`), timeout: 60000 });
            results.push({ route, status: response?.status(), url: page.url() });
        } catch (e) {
            results.push({ route, error: e.message });
        }
    }

    await browser.close();

    fs.writeFileSync(path.join(__dirname, 'stage84-validation-simple.json'), JSON.stringify({ consoleErrors, pageErrors, results }, null, 2));

    console.log('Done. Console errors:', consoleErrors.length, 'Page errors:', pageErrors.length);
    console.log('Results:', JSON.stringify(results, null, 2));
})();
