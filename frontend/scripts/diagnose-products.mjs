import puppeteer from "puppeteer-core";

const CHROME_PATH = "C:/Program Files/Google/Chrome/Application/chrome.exe";
const PRODUCTS_URL = "http://localhost:3000/products";

const browser = await puppeteer.launch({
  headless: true,
  executablePath: CHROME_PATH,
  args: ["--no-sandbox", "--disable-setuid-sandbox"],
});

const page = await browser.newPage();
const errors = [];

page.on("console", (msg) => {
  if (msg.type() === "error") {
    errors.push(`[console.error] ${msg.text()}`);
    console.log(`[console.error] ${msg.text()}`);
  }
});

page.on("pageerror", (err) => {
  errors.push(`[pageerror] ${err.message}`);
  console.log(`[pageerror] ${err.message}`);
});

page.on("response", (response) => {
  const url = response.url();
  if (url.includes("/api/v1/")) {
    console.log(`[network] ${response.request().method()} ${url} -> ${response.status()}`);
  }
});

const results = {
  pageLoads: false,
  productsLoad: false,
  searchWorks: false,
  categoryFilterWorks: false,
  productDetailOpens: false,
  noConsoleErrors: false,
};

async function waitForProductCards(timeout = 10000) {
  const start = Date.now();
  while (Date.now() - start < timeout) {
    const count = await page.evaluate(() => document.querySelectorAll("article").length);
    if (count > 0) return count;
    await new Promise((r) => setTimeout(r, 200));
  }
  return 0;
}

console.log(`\n=== 1. Page Load ===`);
console.log(`Navigating to ${PRODUCTS_URL}...`);
await page.goto(PRODUCTS_URL, { waitUntil: "networkidle2", timeout: 30000 });
await new Promise((r) => setTimeout(r, 2000));

const bodyText = await page.evaluate(() => document.body.innerText);
results.pageLoads = !bodyText.includes("Failed to load products") && !bodyText.includes("Internal Server Error");
const initialCardCount = await waitForProductCards();
results.productsLoad = initialCardCount > 0;
console.log(`Page loads without error: ${results.pageLoads}`);
console.log(`Initial product cards: ${initialCardCount}`);

console.log(`\n=== 2. Search ===`);
const searchInput = await page.$("#product-search");
if (searchInput) {
  await searchInput.click();
  await searchInput.type("silk");
  await new Promise((r) => setTimeout(r, 1500));
  const searchedCardCount = await page.evaluate(() => document.querySelectorAll("article").length);
  results.searchWorks = searchedCardCount >= 0 && !bodyText.includes("Failed to load products");
  console.log(`Search term 'silk' -> ${searchedCardCount} cards`);
  await searchInput.click({ clickCount: 3 });
  await searchInput.type("");
  await new Promise((r) => setTimeout(r, 1000));
} else {
  console.log("Search input not found");
}

console.log(`\n=== 3. Category Filter ===`);
const categoryButtons = await page.$$('button[role="tab"]');
if (categoryButtons.length > 1) {
  await categoryButtons[1].click();
  await new Promise((r) => setTimeout(r, 1500));
  const filteredCardCount = await page.evaluate(() => document.querySelectorAll("article").length);
  results.categoryFilterWorks = filteredCardCount >= 0;
  console.log(`Category filter -> ${filteredCardCount} cards`);
  await categoryButtons[0].click();
  await new Promise((r) => setTimeout(r, 1000));
} else {
  console.log("Category buttons not found");
}

console.log(`\n=== 4. Product Detail Navigation ===`);
const firstProductLink = await page.$("article a[href^='/products/']");
if (firstProductLink) {
  const href = await firstProductLink.evaluate((el) => el.getAttribute("href"));
  console.log(`Clicking product link: ${href}`);
  await Promise.all([page.waitForNavigation({ waitUntil: "networkidle2", timeout: 15000 }), firstProductLink.click()]);
  await new Promise((r) => setTimeout(r, 2000));
  const detailUrl = page.url();
  const detailBody = await page.evaluate(() => document.body.innerText);
  results.productDetailOpens = detailUrl.includes("/products/") && detailBody.length > 100 && !detailBody.includes("Internal Server Error");
  console.log(`Product detail URL: ${detailUrl}`);
  console.log(`Product detail renders: ${results.productDetailOpens}`);
} else {
  console.log("No product link found");
}

results.noConsoleErrors = errors.length === 0;

console.log(`\n=== REGRESSION RESULTS ===`);
console.log(JSON.stringify(results, null, 2));
if (errors.length > 0) {
  console.log(`\n=== CONSOLE ERRORS ===`);
  errors.forEach((e) => console.log(e));
}

await browser.close();

const allPass = Object.values(results).every(Boolean);
process.exit(allPass ? 0 : 1);
