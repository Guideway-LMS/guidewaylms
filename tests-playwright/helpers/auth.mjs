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

export async function loginAluno(page) {
  await page.setViewportSize({ width: 1920, height: 1080 });

  await page.goto(process.env.LMS_ALUNO_URL);
  await page.waitForLoadState('networkidle');

  await page.locator('#offcanvas-toggler').click();
  await page.locator('li.item-108 .menu-toggler').click();
  await page.locator('li.item-109 a[href*="login"]').click();

  await page.waitForURL(/pages\/login/);

  await page.fill('#username', process.env.LMS_ALUNO_USER);
  await page.fill('#password', process.env.LMS_ALUNO_PASS);

  await page.locator('button:has-text("Entrar")').click();

  await page.waitForURL(/view=profile/);
}