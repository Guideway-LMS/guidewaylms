import { test, expect } from '@playwright/test';
import { login } from '../../helpers/auth.mjs';
import {
  acessarMuralAvisos
} from '../../helpers/mural/mural.mjs';

test('criar novo aviso - preencher e salvar', async ({ page }) => {
  // ==============================
  // Login
  // ==============================
  await login(page);

  await page.waitForURL(/administrator/, { timeout: 25000 });
  await page.waitForLoadState('networkidle');

  await acessarMuralAvisos(page);
  // ==============================
  // Novo Aviso
  // ==============================
  const botaoNovo = page.locator('#toolbar-new');
  await expect(botaoNovo).toBeVisible({ timeout: 15000 });
  await botaoNovo.click();

  await page.waitForURL(/layout=edit|task=announcement\.add/, {
    timeout: 15000,
  });

  // ==============================
  // Título
  // ==============================
  const titulo = `Aviso automático ${Date.now()}`;
  const inputTitulo = page.locator('input[name="jform[title]"]');

  await expect(inputTitulo).toBeVisible({ timeout: 15000 });
  await inputTitulo.fill(titulo);

  // ==============================
  // Conteúdo (TinyMCE)
  // ==============================
  const editorFrame = page.frameLocator('iframe.tox-edit-area__iframe');
  const editorBody = editorFrame.locator('body');

  await editorBody.waitFor({ state: 'visible', timeout: 15000 });
  await editorBody.fill(
    'Este é um aviso criado automaticamente pelo Playwright.'
  );

  // ==============================
  // Curso
  // ==============================
  const cursoSelect = page.locator('#jform_course_id');
  await expect(cursoSelect).toBeVisible({ timeout: 15000 });
  await cursoSelect.selectOption('4');

  // ==============================
  // Salvar
  // ==============================
  const botaoSalvar = page.locator('#toolbar-save');
  await expect(botaoSalvar).toBeVisible({ timeout: 15000 });
  await botaoSalvar.click();

  // ==============================
  // Validação
  // ==============================
  const mensagemSucesso = page.locator(
    '.alert-success, .message-success, .alert-message, .alert'
  );

  await expect(mensagemSucesso).toBeVisible({ timeout: 15000 });
});
