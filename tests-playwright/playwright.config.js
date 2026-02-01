// playwright.config.js
const { defineConfig, devices } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',

  // ⏱️ Configurações globais
  timeout: 60 * 1000,

  use: {
    baseURL: 'http://localhost/guidewaylms',
    ignoreHTTPSErrors: true,

    // 🖥️ Execução visível
    headless: true,

    viewport: { width: 1280, height: 720 },

    // ⏱️ Timeouts
    actionTimeout: 30 * 1000,
    navigationTimeout: 60 * 1000,

    // 📸 Evidências
    screenshot: 'only-on-failure',

    video: 'on',

    // 🧵 Guarda trace quando falhar (abre no report)
    trace: 'on',
  },

  // 📊 Report HTML
  reporter: [
    ['html', { outputFolder: 'playwright-report', open: 'never' }],
  ],

  projects: [
    // 🟦 Chromium / Chrome
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
        browserName: 'chromium',
        launchOptions: {
          args: ['--ignore-certificate-errors'],
        },
      },
    },
    {
      name: 'chrome',
      use: {
        ...devices['Desktop Chrome'],
        browserName: 'chromium',
      channel: 'chrome',
    },
  },

    // 🟧 Firefox
    {
      name: 'firefox',
      use: {
        ...devices['Desktop Firefox'],
        browserName: 'firefox',
          channel: 'firefox',
      },
    },

    // 🟩 Microsoft Edge
    {
      name: 'msedge',
      use: {
        ...devices['Desktop Edge'],
        browserName: 'chromium',
        channel: 'msedge',
      },
    },
  ],
});
