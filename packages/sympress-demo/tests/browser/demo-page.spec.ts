import { expect, test } from '@playwright/test';

const exceptionPattern = /Fatal error|Parse error|Stack trace:|There has been a critical error|Uncaught (?:ArgumentCountError|Error|Exception|TypeError)/i;

test('demo homepage renders without runtime exceptions', async ({ page }) => {
    const runtimeErrors: string[] = [];
    const serverErrors: string[] = [];

    page.on('console', (message) => {
        if (message.type() === 'error' && exceptionPattern.test(message.text())) {
            runtimeErrors.push(message.text());
        }
    });

    page.on('pageerror', (error) => {
        runtimeErrors.push(error.message);
    });

    page.on('response', (response) => {
        if (response.status() >= 500) {
            serverErrors.push(`${response.status()} ${response.url()}`);
        }
    });

    const response = await page.goto('/', { waitUntil: 'load' });
    const status = response?.status() ?? 0;

    expect(status, 'homepage should return a non-error HTTP status').toBeLessThan(500);
    await expect(page.locator('body')).toBeVisible();
    await expect(page).toHaveTitle(/.+/);
    await expect(page.locator('body')).toHaveClass(/wp-theme-sympress-starter/);
    const notes = page.locator('.sympress-demo-notes');
    await expect(notes).toBeVisible();
    await expect(notes.locator('.sympress-demo-notes__item')).toHaveCount(6);
    await expect(notes).toHaveAttribute('data-enhanced', 'true');

    const themeStyles = page.locator('link[rel="stylesheet"][href*="/themes/sympress-starter/build/"]');
    const inlineThemeStyles = page.locator('style[data-id="sympress-starter-app"]');
    expect(await themeStyles.count() + await inlineThemeStyles.count(), 'the active starter theme must load compiled styles').toBeGreaterThan(0);
    if (await inlineThemeStyles.count() > 0) {
        expect((await inlineThemeStyles.textContent())?.length, 'inline theme styles must contain the compiled stylesheet').toBeGreaterThan(100);
    }
    for (const href of await themeStyles.evaluateAll((links) => links.map((link) => (link as HTMLLinkElement).href))) {
        const asset = await page.request.get(href);
        expect(asset.status(), `theme asset should be available: ${href}`).toBe(200);
    }

    const html = await page.content();

    expect(html, 'homepage HTML should not contain PHP or WordPress fatal errors').not.toMatch(exceptionPattern);
    expect(runtimeErrors, 'browser console should not report runtime exceptions').toEqual([]);
    expect(serverErrors, 'browser request chain should not contain server errors').toEqual([]);
});
