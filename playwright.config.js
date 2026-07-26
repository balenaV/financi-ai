import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './tests/E2E',
    fullyParallel: true,
    retries: process.env.CI ? 2 : 0,
    reporter: process.env.CI ? [['html', { open: 'never' }], ['list']] : 'list',
    use: {
        baseURL: process.env.PLAYWRIGHT_BASE_URL || 'http://127.0.0.1:8000',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
    },
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
        { name: 'mobile', use: { ...devices['Pixel 7'] } },
    ],
    webServer: process.env.CI ? undefined : {
        command: 'php artisan serve --host=127.0.0.1 --port=8000',
        url: 'http://127.0.0.1:8000/up',
        reuseExistingServer: true,
        timeout: 120_000,
    },
});
