import { test, expect } from '@playwright/test';
import { login } from '../../helpers/auth.mjs';
import { acessarLicao } from '../../helpers/licao/licao.mjs';
import { ensureMediaFixture } from '../../helpers/scripts/ensureMediaFixture.mjs';



test('criar nova lição - preencher e salvar', async ({ page }) => {
  // ==============================
  // Login
  // ==============================
  await login(page);

  await page.waitForURL(/administrator/, { timeout: 25000 });
  await page.waitForLoadState('networkidle');

  await acessarLicao(page);


// ==============================
// Botão Opções
// ==============================
const botaoOpcoes = page.locator('#toolbar-options');

await expect(botaoOpcoes).toBeVisible({ timeout: 15000 });
await botaoOpcoes.click();


// ==============================
// Botão Salvar (Apply)
// ==============================
const botaoSalvar = page.locator('#toolbar-apply button');

await expect(botaoSalvar).toBeVisible({ timeout: 15000 });
await botaoSalvar.click();

// ==============================
// Validação mensagem de sucesso
// ==============================
await expect(
  page.locator('#system-message-container')
).toContainText('Configuration saved');

// ==============================
// Botão Fechar
// ==============================
const botaoFechar = page.locator('button.button-cancel');

await expect(botaoFechar).toBeVisible({ timeout: 15000 });
await botaoFechar.click();

// ==============================
// Clique na Lição (4º TR / 4º TD)
// ==============================
const primeiroLinkEdicao = page.locator(
  'tbody tr td a[href*="task=lesson.edit"]'
).first();

await expect(primeiroLinkEdicao).toBeVisible({ timeout: 15000 });
await primeiroLinkEdicao.click();


// ==============================
// Clique em revisar texto
// ==============================
await page.click('button:has-text("Revisar")');

});


 