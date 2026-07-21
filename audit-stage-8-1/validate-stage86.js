const { chromium } = require('playwright');
const fs = require('fs');
const path = require('path');

const BASE_URL = 'http://127.0.0.1:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const DEFAULT_PASSWORD = 'Admin@12345';
const NEW_PASSWORD = 'Vestra@2024!Secure';

const screenshotsDir = path.join(__dirname, 'screenshots-stage86');
if (!fs.existsSync(screenshotsDir)) fs.mkdirSync(screenshotsDir, { recursive: true });

const consoleErrors = [];
const pageErrors = [];

async function capture(page, name, fullPage = true) {
    await page.screenshot({ path: path.join(screenshotsDir, `${name}.png`), fullPage, timeout: 60000 });
}

function resetAdminUser() {
    const { execSync } = require('child_process');
    const scriptPath = path.join(__dirname, 'reset-admin-user.sh');
    try {
        execSync(`bash "${scriptPath}"`, { stdio: 'inherit' });
    } catch (e) {
        console.warn('Could not reset admin user; continuing anyway.', e.message);
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

    // Force password change if required
    if (page.url().includes('force-password-change')) {
        await page.waitForSelector('input[type="password"]', { timeout: 10000 });
        const passwordInputs = await page.locator('input[type="password"]').all();
        if (passwordInputs.length >= 3) {
            await passwordInputs[0].fill(DEFAULT_PASSWORD);
            await passwordInputs[1].fill(NEW_PASSWORD);
            await passwordInputs[2].fill(NEW_PASSWORD);
            await Promise.all([
                page.waitForNavigation({ waitUntil: 'domcontentloaded', timeout: 30000 }).catch(() => null),
                page.click('button:has-text("Change Password")'),
            ]);
            await page.waitForTimeout(5000);
        }
    }

    const currentUrl = page.url();
    if (! currentUrl.includes('/admin') || currentUrl.includes('force-password-change')) {
        await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded' });
        await page.waitForTimeout(3000);
    }

    // Dashboard desktop
    await capture(page, 'stage86_dashboard_desktop');

    const widgetHeadings = await page.locator('h3, .fi-section-header-heading').allInnerTexts();
    console.log('Widget headings:', widgetHeadings);

    // Tablet viewport
    await page.setViewportSize({ width: 1024, height: 768 });
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);
    await capture(page, 'stage86_dashboard_tablet');

    // Mobile viewport
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(`${BASE_URL}/admin`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(2000);

    // Close mobile sidebar overlay if open so dashboard content is visible
    await page.evaluate(() => {
        if (window.Alpine && Alpine.store('sidebar') && Alpine.store('sidebar').close) {
            Alpine.store('sidebar').close();
        }
    }).catch(() => null);
    await page.waitForTimeout(500);

    await capture(page, 'stage86_dashboard_mobile');

    await browser.close();

    fs.writeFileSync(path.join(__dirname, 'stage86-validation.json'), JSON.stringify({ consoleErrors, pageErrors, widgetHeadings }, null, 2));

    console.log('Done. Console errors:', consoleErrors.length, 'Page errors:', pageErrors.length);
})();
