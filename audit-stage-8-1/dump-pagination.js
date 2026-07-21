const { chromium } = require('playwright');
const fs = require('fs');

const BASE_URL = 'http://localhost:8000';
const ADMIN_EMAIL = 'admin@vestra.com';
const DEFAULT_PASSWORD = 'Vestra@2024!Secure';

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();

    page.setDefaultNavigationTimeout(120000);

    await page.goto(`${BASE_URL}/admin/login`, { waitUntil: 'domcontentloaded' });
    await page.fill('input[type="email"]', ADMIN_EMAIL);
    await page.fill('input[type="password"]', DEFAULT_PASSWORD);
    await page.click('button:has-text("Sign in")');
    await page.waitForTimeout(5000);

    await page.goto(`${BASE_URL}/admin/users`, { waitUntil: 'domcontentloaded' });
    await page.waitForTimeout(3000);

    const html = await page.content();
    fs.writeFileSync('users-page.html', html);
    console.log('HTML dumped to users-page.html');

    await browser.close();
})();
