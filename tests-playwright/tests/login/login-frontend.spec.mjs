import { test, expect } from '@playwright/test';
import dotenv from 'dotenv';

dotenv.config();

test('login válido - aluno', async ({ page }) => {
  const {
    LMS_ALUNO_URL,
    LMS_ALUNO_USER,
    LMS_ALUNO_PASS
  } = process.env;

  await page.goto(LMS_ALUNO_URL);
  await page.waitForLoadState('networkidle');

  await page.locator('#offcanvas-toggler').click();
  await page.locator('li.item-108 .menu-toggler').click();
  await page.locator('li.item-109 a[href*="login"]').click();

  await expect(page).toHaveURL(/pages\/login/);

  await page.fill('#username', LMS_ALUNO_USER);
  await page.fill('#password', LMS_ALUNO_PASS);

  await page.locator('button:has-text("Entrar")').click();

  // ✅ Validação correta para seu cenário
  await expect(page).toHaveURL(/view=profile/);
});