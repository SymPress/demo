import { expect, test } from '@playwright/test';

// CI exports the password printed by the documented reset command; runs without it skip.
const password = (process.env.WP_ADMIN_PASSWORD ?? '').trim();
const username = process.env.WP_ADMIN_USERNAME ?? 'admin';

test('admin logs in and sees the seeded SymPress Demo dashboard', async ({ page }) => {
    test.skip(password === '', 'Set WP_ADMIN_PASSWORD to run the admin dashboard test.');

    await page.goto('/wp-login.php');
    await page.locator('#user_login').fill(username);
    await page.locator('#user_pass').fill(password);
    await page.locator('#wp-submit').click();
    await expect(page.locator('#wpadminbar')).toBeVisible();

    // Resolve against the post-login admin URL, which may live below a core subdirectory.
    await page.goto(new URL('admin.php?page=sympress-demo', page.url()).href);
    await expect(page.getByRole('heading', { level: 1, name: 'SymPress Demo' })).toBeVisible();

    const publishedNotes = page.locator('.sympress-demo-admin__stats section', { hasText: 'Published notes' });
    await expect(publishedNotes.locator('strong')).toHaveText('18');

    const kernel = page.locator('.sympress-demo-admin__components tbody tr', { hasText: 'sympress/kernel' });
    await expect(kernel.locator('[data-component-status]')).toHaveAttribute('data-component-status', 'available');
});
