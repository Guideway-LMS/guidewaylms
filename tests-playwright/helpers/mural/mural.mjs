import { expect } from '@playwright/test';

export async function acessarMuralAvisos(page) {
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

  const muralLink = page.locator('a[aria-label="Mural de Avisos"]');
  await muralLink.waitFor({ state: 'visible', timeout: 10000 });
  await muralLink.click();

}

export async function abrirPrimeiroAviso(page) {
  const primeiroAviso = page.locator('table tbody a[title="Editar item"]').first();
  await expect(primeiroAviso).toBeVisible();
  await primeiroAviso.click();

  await page.waitForURL(/task=announcement\.edit|layout=edit/, { timeout: 10000 });
}
