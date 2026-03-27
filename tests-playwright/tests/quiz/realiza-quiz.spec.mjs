import { test, expect } from '@playwright/test';
import dotenv from 'dotenv';

dotenv.config();

test('Abrir Week 01, acessar quiz e iniciar', async ({ page }) => {
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

  await expect(page).toHaveURL(/view=profile/);

  await page.goto(
    'http://localhost/guidewaylms/pt/courses/1-sketchbooking-explore-the-human-face#course-lessons'
  );

  await page.waitForLoadState('networkidle');

  const week01 = page.locator('#topicId0');
  await expect(week01).toBeVisible();

  const expanded = await week01.getAttribute('aria-expanded');
  if (expanded !== 'true') {
    await week01.click();
  }

  const topicBody = page.locator('#topicBody0');
  await expect(topicBody).toBeVisible();
  await page.waitForSelector('#topicBody0.show');

  const obrigatorio = page
    .locator('li:has(span.guideway-badge-required) a')
    .first();

  await obrigatorio.scrollIntoViewIfNeeded();
  await expect(obrigatorio).toBeVisible();
  await expect(obrigatorio).toBeEnabled();
  await obrigatorio.click();

  await expect(page).toHaveURL(/view=quizquestion/);

  const iniciarQuiz = page.locator('button.startQuiz');

  await iniciarQuiz.scrollIntoViewIfNeeded();
  await expect(iniciarQuiz).toBeVisible();
  await expect(iniciarQuiz).toBeEnabled();

  await iniciarQuiz.click();

  const timerWrapper = page.locator('.countdown-wrapper');
  await expect(timerWrapper).toBeVisible();

  const countdown = page.locator('#countdown');
  await expect(countdown).toBeVisible();
  await expect(countdown).not.toHaveText('');

  const proxima = page.locator('button.nextButton');

  await proxima.scrollIntoViewIfNeeded();
  await expect(proxima).toBeVisible();
  await expect(proxima).toBeEnabled();
});