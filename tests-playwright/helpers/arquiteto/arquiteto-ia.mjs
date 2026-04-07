import { expect } from '@playwright/test';

export async function acessarArquitetoIA(page) {
  const componentesMenu = page.locator('a[aria-label="Componentes"]');
  await componentesMenu.waitFor({ state: 'visible', timeout: 10000 });
  await componentesMenu.click();

  
  const spLmsMenu = page.locator('a[aria-label="SP LMS"]');
  await spLmsMenu.waitFor({ state: 'visible', timeout: 40000 });
  await spLmsMenu.scrollIntoViewIfNeeded();

  const expanded = await spLmsMenu.getAttribute('aria-expanded');
  if (expanded !== 'true') {
    await spLmsMenu.click();
    await page.waitForTimeout(300);
  
  }

  const cursosLink = page.locator('a[aria-label="Cursos"]');
await cursosLink.waitFor({ state: 'visible', timeout: 10000 });
await cursosLink.click();

const cursoLink = page.locator('a[href*="course.edit&id=1"]');
await cursoLink.waitFor({ state: 'visible', timeout: 10000 });
await cursoLink.click();


const arquitetoBtn = page.locator('#splms-ai-trigger-btn');
await arquitetoBtn.waitFor({ state: 'visible', timeout: 10000 });
await arquitetoBtn.click();
}

export async function abrirModalArquitetoIA(page) {
  const tituloModal = page.locator('h5.modal-title:has-text("Arquiteto de Cursos com IA")');
  
  await tituloModal.waitFor({ state: 'visible', timeout: 10000 });
  await expect(tituloModal).toBeVisible();
}
