import fs from 'fs';
import { execSync } from 'child_process';

const resultsDir = './test-results';

fs.readdirSync(resultsDir).forEach(folder => {
  const webm = `${resultsDir}/${folder}/video.webm`;
  const mp4 = `${resultsDir}/${folder}/video.mp4`;

  if (fs.existsSync(webm)) {
    console.log(`Convertendo ${webm} ...`);
    execSync(`ffmpeg -i ${webm} -c:v libx264 -preset fast -crf 22 ${mp4}`);
    console.log(`→ Salvo como ${mp4}`);
  }
});
