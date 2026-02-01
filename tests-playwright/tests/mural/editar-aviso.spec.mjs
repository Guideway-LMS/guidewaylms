import { test, expect } from '@playwright/test';
import { login } from '../../helpers/auth.mjs';
import {
  acessarMuralAvisos,
  abrirPrimeiroAviso,
} from '../../helpers/mural/mural.mjs';

test('editar aviso existente', async ({ page }) => {
    await login(page);

  await page.waitForURL(/administrator/, { timeout: 25000 });
  await page.waitForLoadState('networkidle');

 

  await acessarMuralAvisos(page);
  await abrirPrimeiroAviso(page);

  const novoTitulo = `Aviso editado ${Date.now()}`;

  const campoTitulo = page.locator('#jform_title');
  await campoTitulo.waitFor({ state: 'visible' });
  await campoTitulo.fill(novoTitulo);

  // TinyMCE
  const editorFrame = page.frameLocator('iframe.tox-edit-area__iframe');
  const editorBody = editorFrame.locator('body');

  await editorBody.waitFor({ state: 'visible' });
  await editorBody.click();
  await editorBody.press('Control+A');
  await editorBody.press('Backspace');

  await editorBody.fill(
    `Conteúdo editado automaticamente em ${new Date().toLocaleString()}`
  );

  await page.waitForSelector('#toolbar-save button', { state: 'visible' });
  await page.click('#toolbar-save button');

  await page.waitForURL(/view=announcements/, { timeout: 10000 });
  
  const mensagemSucesso = page.locator(
    '.alert-success, .message-success, .alert-message, .alert'
  );

  await expect(mensagemSucesso).toBeVisible({ timeout: 15000 });
});
