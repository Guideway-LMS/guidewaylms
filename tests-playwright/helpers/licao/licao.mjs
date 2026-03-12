import { expect } from '@playwright/test';

export async function acessarLicao(page) {
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

  const muralLink = page.locator('a[aria-label="Lições"]');
  await muralLink.waitFor({ state: 'visible', timeout: 10000 });
  await muralLink.click();

}

