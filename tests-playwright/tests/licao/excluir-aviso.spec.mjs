import { test, expect } from '@playwright/test';
import { login } from '../../helpers/auth.mjs';
import { acessarMuralAvisos } from '../../helpers/mural/mural.mjs';

test('excluir aviso existente', async ({ page }) => {
   await login(page);

  await page.waitForURL(/administrator/, { timeout: 25000 });
  await page.waitForLoadState('networkidle');

  await acessarMuralAvisos(page);
  

 

  // ==============================
  // Selecionar primeiro aviso na tabela
  // ==============================
  const primeiraLinha = page.locator('table tbody tr').first();

  // Marcar checkbox do primeiro aviso
  const checkbox = primeiraLinha.locator('input[type="checkbox"][name="cid[]"]');
  await checkbox.check();

  // Captura título para validação futura (opcional)
  const tituloAviso = await primeiraLinha.locator('td a').innerText();

  // ==============================
  // Clicar em "Apagar item"
  // ==============================
  const botaoExcluir = page.locator('#toolbar-delete button');
  await expect(botaoExcluir).toBeEnabled();
  await botaoExcluir.click();

  // ==============================
  // Confirmação do modal (Joomla)
  // ==============================
  const modalConfirmacao = page.locator('.joomla-dialog-container');
  await expect(modalConfirmacao).toBeVisible();

  const botaoSim = modalConfirmacao.locator('button[data-button-ok]');
  await botaoSim.click();


  
  const mensagemSucesso = page.locator(
    '.alert-success, .message-success, .alert-message, .alert'
  );

  await expect(mensagemSucesso).toBeVisible({ timeout: 15000 });
});
