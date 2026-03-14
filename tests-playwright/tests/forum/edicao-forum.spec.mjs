import { test, expect } from '@playwright/test';
import dotenv from 'dotenv';

dotenv.config();

test('Edita pergunta no fórum como aluno', async ({ page }) => {
  const {
    LMS_ALUNO_URL,
    LMS_ALUNO_USER,
    LMS_ALUNO_PASS
  } = process.env;

  //  LOGIN
  await page.goto(LMS_ALUNO_URL);
  await page.waitForLoadState('networkidle');

  await page.locator('#offcanvas-toggler').click();
  await page.locator('li.item-108 .menu-toggler').click();
  await page.locator('li.item-109 a[href*="login"]').click();

  await expect(page).toHaveURL(/pages\/login/);

  await page.fill('#username', LMS_ALUNO_USER);
  await page.fill('#password', LMS_ALUNO_PASS);
  await page.locator('button:has-text("Entrar")').click();

  await expect(page).toHaveURL(/view=profile/);

  //  Ir direto para o fórum do curso
  await page.goto('http://localhost/guidewaylms/pt/courses?view=forum&course_id=1');
  await page.waitForLoadState('networkidle');

  const tituloRandom = `Teste Forum ${Date.now()}`;

  //  Preencher título
  await page.fill('#title', tituloRandom);

  //  Preencher TinyMCE (iframe)
  const frame = page.frameLocator('#body_ifr');
  await frame.locator('body').fill('Mensagem automática de teste.');

  //  Publicar
  await page.click('button:has-text("Publicar Pergunta")');

  //  Validar se apareceu na lista
  await expect(page.locator(`text=${tituloRandom}`)).toBeVisible();
});