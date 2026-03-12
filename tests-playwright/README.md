# 🧪 Testes Automatizados com Playwright

Guia completo para executar e desenvolver testes automatizados.

## 🚀 Começando

### Pré-requisitos
- Node.js 16+ 
- npm ou yarn
- Git

### Instalação
```bash
# Na pasta tests-playwright/
npm install          # Instala dependências
npm update           # Atualiza pacotes
```

## 🧪 Executando Testes

### Rodar todos os testes
```bash
npx playwright test
```

### Rodar testes específicos
```bash
npx playwright test tests/aula.spec.js      # Testes de aula
npx playwright test tests/mural.spec.js     # Testes de mural
```

### Rodar com interface gráfica (UI Mode)
```bash
npx playwright test --ui
```

### Rodar em modo debug
```bash
npx playwright test --debug
```

## 📊 Visualizando Relatórios

### Relatório HTML (após execução)
```bash
npx playwright show-report
```

### Gerar relatório sempre
```bash
npx playwright test --reporter=html
```

## 🛠️ Desenvolvimento

### Abrir Playwright Inspector
```bash
npx playwright codegen http://localhost:8080
```

### Rodar testes em navegador específico
```bash
npx playwright test --project=chromium
npx playwright test --project=firefox
npx playwright test --project=webkit
```

## 📁 Estrutura de Pastas

```
tests-playwright/
├── tests/           # Arquivos de teste (.spec.js)
├── fixtures/        # Dados de teste reutilizáveis
├── helpers/         # Funções auxiliares
├── auth/            # Configurações de autenticação
├── package.json     # Dependências
└── playwright.config.js # Configuração
```

## 🔧 Comandos Úteis

| Comando | Descrição |
|---------|-----------|
| `npm test` | Roda todos os testes (atalho) |
| `npx playwright test --headed` | Roda com navegador visível |
| `npx playwright test --trace on` | Gera traces para debug |
| `npx playwright test --grep "criar"` | Filtra testes por tag |

## ⚙️ Configuração

### Variáveis de Ambiente
Crie um arquivo `.env` (não commitado):
```env
BASE_URL=http://localhost:8080
USER_EMAIL=admin@example.com
USER_PASSWORD=senha123
```

### package.json scripts
```json
{
  "scripts": {
    "test": "playwright test",
    "test:ui": "playwright test --ui",
    "test:debug": "playwright test --debug",
    "test:chrome": "playwright test --project=chromium",
    "report": "playwright show-report"
  }
}
```

## 🐛 Solução de Problemas

### Erro: "node_modules não encontrado"
```bash
rm -rf node_modules package-lock.json
npm install
```

### Erro: Navegadores não instalados
```bash
npx playwright install
npx playwright install --with-deps
```

### Limpar relatórios antigos
```bash
rm -rf playwright-report test-results
```

## 📞 Suporte

- Documentação oficial: https://playwright.dev
- Issues: Abra no repositório do projeto
- Time: Consulte a equipe de desenvolvimento
