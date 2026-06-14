import { existsSync } from 'node:fs';

import { defineConfig, devices } from '@playwright/test';

const chromiumExecutablePath = process.env.PLAYWRIGHT_CHROMIUM_EXECUTABLE_PATH ?? '/usr/bin/chromium';
const launchOptions = existsSync(chromiumExecutablePath)
    ? { executablePath: chromiumExecutablePath }
    : {};

export default defineConfig({
    testDir: './tests/browser',
    timeout: 30_000,
    expect: {
        timeout: 5_000,
    },
    use: {
        baseURL: process.env.PLAYWRIGHT_BASE_URL ?? process.env.DDEV_PRIMARY_URL ?? 'https://sympress-demo.dev',
        ignoreHTTPSErrors: true,
        trace: 'on-first-retry',
    },
    projects: [
        {
            name: 'chromium',
            use: {
                ...devices['Desktop Chrome'],
                launchOptions,
            },
        },
    ],
});
