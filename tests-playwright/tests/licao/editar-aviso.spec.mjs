import { test, expect } from '@playwright/test';
import { login } from '../../helpers/auth.mjs';
import {
  acessarMuralAvisos,
  abrirPrimeiroAviso,
} from '../../helpers/mural/mural.mjs';

test('editar aviso existente', async ({ page }) => {
  await login(page);

  // Aguarda redirecionamento para área administrativa
  await page.waitForURL(/administrator/, { timeout: 25000 });
  await page.waitForLoadState('networkidle');

  // Navegação até o aviso
  await acessarMuralAvisos(page);
  await abrirPrimeiroAviso(page);

  const timestamp = Date.now();
  const tituloAtualizado = `Aviso editado ${timestamp}`;
  const conteudoAtualizado = `Conteúdo editado automaticamente em ${new Date().toLocaleString()}`;

  // Campo título
  const inputTitulo = page.locator('#jform_title');
  await inputTitulo.waitFor({ state: 'visible' });
  await inputTitulo.fill(tituloAtualizado);

  // Editor TinyMCE
  const frameEditor = page.frameLocator('iframe.tox-edit-area__iframe');
  const corpoEditor = frameEditor.locator('body');

  await corpoEditor.waitFor({ state: 'visible' });

  // Limpa conteúdo anterior
  await corpoEditor.click();
  await corpoEditor.press('Control+A');
  await corpoEditor.press('Delete');

  // Insere novo conteúdo
  await corpoEditor.fill(conteudoAtualizado);

  // Salvar
  const botaoSalvar = page.locator('#toolbar-save button');
  await botaoSalvar.waitFor({ state: 'visible' });
  await botaoSalvar.click();

  // Validação final
  await page.waitForURL(/view=announcements/, { timeout: 10000 });

  const alertaSucesso = page.locator(
    '.alert-success, .message-success, .alert-message, .alert'
  );

  await expect(alertaSucesso).toBeVisible({ timeout: 15000 });
});
