import dotenv from 'dotenv';
dotenv.config({ path: new URL('../.env', import.meta.url).pathname });

export async function login(page) {
  await page.setViewportSize({ width: 1920, height: 1080 });

  await page.goto(process.env.LMS_URL);
  await page.fill('#mod-login-username', process.env.LMS_USER);
  await page.fill('#mod-login-password', process.env.LMS_PASS);
  await page.click('#btn-login-submit');
  await page.waitForLoadState('networkidle');
}

