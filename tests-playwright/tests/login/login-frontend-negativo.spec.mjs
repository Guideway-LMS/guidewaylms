import { test, expect } from '@playwright/test';

test('login inválido - senha incorreta', async ({ page }) => {

  // ==============================
  // Acessar site
  // ==============================
  await page.goto('http://localhost/guidewaylms/pt/');
  await page.waitForLoadState('networkidle');

  // ==============================
  // Abrir menu
  // ==============================
  await page.locator('#offcanvas-toggler').click();

  // Expandir Pages
  await page.locator('li.item-108 .menu-toggler').click();

  // Clicar em Login
  await page.locator('li.item-109 a[href*="login"]').click();

  // Validar URL login
  await expect(page).toHaveURL(/pages\/login/);

  // ==============================
  // Preencher dados inválidos
  // ==============================
  await page.fill('#username', 'Felipe Teste');
  await page.fill('#password', 'senhaErrada123');

  // Clicar em Entrar
  await page.locator('button:has-text("Entrar")').click();

  // ==============================
  // Validações negativas
  // ==============================

  // 1️⃣ Não deve ir para profile
  await expect(page).not.toHaveURL(/view=profile/);

  // 2️⃣ Deve continuar na página de login
  await expect(page).toHaveURL(/pages\/login/);

  // 3️⃣ Validar mensagem de erro do sistema
  await expect(
    page.locator('#system-message-container')
  ).toBeVisible({ timeout: 10000 });

});