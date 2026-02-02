import fs from 'fs';
import path from 'path';

export function ensureMediaFixture(fileName) {
  const source = path.resolve('tests-playwright/fixtures', fileName);
  const target = path.resolve('images/tests', fileName);

  if (!fs.existsSync(source)) {
    throw new Error(`Fixture não encontrada: ${source}`);
  }

  fs.mkdirSync(path.dirname(target), { recursive: true });

  if (!fs.existsSync(target)) {
    fs.copyFileSync(source, target);
  }
}
