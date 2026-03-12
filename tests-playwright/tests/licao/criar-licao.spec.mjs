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
// Aba Configuração de API
// ==============================
const abaConfiguracaoApi = page.getByRole('tab', { name: 'Configuração de API' });

await expect(abaConfiguracaoApi).toBeVisible({ timeout: 15000 });
await abaConfiguracaoApi.click();


// ==============================
// Campo Groq API Key
// ==============================
const groqApiKey = process.env.GROQ_API_KEY;

if (!groqApiKey) {
  throw new Error('GROQ_API_KEY não definida no .env');
}

const inputGroqApiKey = page.locator('#jform_groq_api_key');

await expect(inputGroqApiKey).toBeVisible({ timeout: 15000 });
await inputGroqApiKey.fill(groqApiKey);


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


 