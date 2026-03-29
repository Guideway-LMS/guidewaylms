import { test, expect } from '@playwright/test';
import { login } from '../../helpers/auth.mjs';
import {
  acessarArquitetoIA
} from '../../helpers/arquiteto/arquiteto-ia.mjs';

test('criar conteúdo com arquiteto IA', async ({ page }) => {
  // ==============================
  // Login
  // ==============================
  await login(page);

  await page.waitForURL(/administrator/, { timeout: 25000 });
  await page.waitForLoadState('networkidle');

  await acessarArquitetoIA(page);
  // ==============================
  // Novo conteúdo IA
  // ==============================
// YouTube link
const youtubeInput = page.locator('#ai_youtube_link');
await youtubeInput.waitFor({ state: 'visible', timeout: 10000 });
await youtubeInput.fill('https://youtube.com/watch?v=example');

// Tópico do curso
const topicInput = page.locator('#ai_topic');
await topicInput.waitFor({ state: 'visible', timeout: 10000 });
await topicInput.fill('Banco de dados');

// Público-alvo
const audienceInput = page.locator('#ai_audience');
await audienceInput.waitFor({ state: 'visible', timeout: 10000 });
await audienceInput.fill('Alunos iniciados em tecnologia');

// Objetivos
const objectivesInput = page.locator('#ai_objectives');
await objectivesInput.waitFor({ state: 'visible', timeout: 10000 });
await objectivesInput.fill('Aprender conceitos de banco de dados, modelagem e consultas SQL');

// Botão gerar estrutura
const gerarBtn = page.locator('#splms-ai-generate-btn');
await gerarBtn.waitFor({ state: 'visible', timeout: 10000 });
await gerarBtn.click();


});
