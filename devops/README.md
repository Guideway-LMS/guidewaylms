# 🧪 Scripts de Teste - Guideway LMS

Esta pasta contém scripts de teste e debug para o desenvolvimento do **Guideway LMS**, especificamente para a funcionalidade de **Avisos de Cursos (Course Announcements)**.

## 📁 Estrutura de Arquivos

### 1. `test_announcements_check.php`
**Propósito:** Testa o Model `SplmsModelAnnouncements` e verifica se a lógica de segurança está funcionando corretamente.

**O que ele testa:**
- ✅ Bootstrap do Joomla
- ✅ Autenticação do usuário
- ✅ Verificação de matrícula via tabela `#__splms_orders`
- ✅ Busca de avisos do curso
- ✅ Aplicação de filtros (curso, matrícula)

**Como usar:**
```
http://seu-dominio.com/guidewaylms/devops/test_announcements_check.php
```

**Configuração:**
- Por padrão, testa com **Usuário ID = 1** (Super User)
- Por padrão, testa com **Curso ID = 1**
- Edite as linhas 63-67 do arquivo para alterar estes valores

---

### 2. `create_test_data.php`
**Propósito:** Cria dados de teste (matrícula/ordem) para permitir que o teste de avisos funcione.

**O que ele faz:**
- Busca um usuário ativo no sistema
- Busca um curso publicado
- Cria uma entrada na tabela `#__splms_orders` vinculando o usuário ao curso

**Como usar:**
```
http://seu-dominio.com/guidewaylms/devops/create_test_data.php
```

**Nota:** Execute este script **ANTES** de rodar `test_announcements_check.php` se ainda não houver matrículas no sistema.

---

### 3. `debug_db_columns.php`
**Propósito:** Lista todas as colunas de uma tabela do banco de dados para debug de schema.

**O que ele faz:**
- Conecta ao banco de dados do Joomla
- Lista todas as colunas e tipos da tabela especificada
- Destaca colunas importantes (ex: `user_id`, `created_at`, `message`)

**Como usar:**
```
http://seu-dominio.com/guidewaylms/devops/debug_db_columns.php
```

**Configuração:**
- Por padrão, exibe a tabela `#__splms_course_announcements`
- Para verificar outra tabela, edite a variável `$tableName` na linha 24

---

### 4. `groq_prompt_test.php`
**Propósito:** Testa a lógica de seleção de prompts e processamento de texto do `GuidewayAIHelper`.

**O que ele testa:**
- ✅ Ação `Revisar`: Correção gramatical.
- ✅ Ação `Resumir`: Síntese de texto.
- ✅ Ação `Reescrever`: Reescrita de estilo.
- ✅ Tratamento de erro para ações inválidas.

**Como usar:**
```
http://seu-dominio.com/guidewaylms/devops/groq_prompt_test.php
```

**Como funciona este teste:**
Este script envia um texto padrão (com erros propositais e propositalmente longo) para o Helper, solicitando diferentes ações.
1. **Revisar**: Espera-se que a IA retorne o texto corrigido.
2. **Resumir**: Espera-se que a IA retorne uma versão condensada do texto.
3. **Reescrever**: Espera-se que a IA retorne o texto com vocabulário melhorado.
4. **Invalida**: O script envia intencionalmente uma ação que não existe (`acao_inexistente`).
   - O objetivo é verificar se o sistema é robusto o suficiente para não quebrar.
   - O resultado **correto** para este caso é o sistema retornar uma mensagem de erro ("Ação desconhecida"). Portanto, ver um erro aqui significa que o teste PASSOU.

---

### 5. `test_callai_endpoint.php`
**Propósito:** Testa o endpoint AJAX `lesson.callAI` e a proteção CSRF.

**O que ele testa:**
- ✅ Endpoint acessível via URL
- ✅ Rejeição de requisições sem token CSRF (403)

**Como usar:**
```
http://seu-dominio.com/guidewaylms/devops/test_callai_endpoint.php
```

---

### 6. `pdf_upload_test.php`
**Propósito:** Testa a funcionalidade de upload e extração de texto de PDFs utilizando a biblioteca `smalot/pdfparser`.

**O que ele testa:**
- ✅ Upload de arquivo via AJAX (`gw_ai_file`).
- ✅ Validações de arquivo (Tamanho 5MB, extensão .pdf).
- ✅ Extração de texto do conteúdo do PDF (`Smalot\PdfParser`).
- ✅ **Integração com IA:** Envio opcional de prompt (`gw_ai_prompt`).
  - Se prompt enviado: Processa com ação `CUSTOM`.
  - Se sem prompt: Processa com ação `FORMATAR` (limpeza inteligente).
- ✅ Sanitização de caracteres (UTF-8).
- ✅ Resposta padronizada JSON.

**Como usar:**
```
http://seu-dominio.com/guidewaylms/devops/pdf_upload_test.php
```

**⚠️ REQUISITO IMPORTANTE (Dependência):**
Este teste requer que a biblioteca `smalot/pdfparser` esteja instalada manualmente.
Veja o guia de instalação abaixo.

---

## 📦 Guia de Instalação de Dependências (Composer)

Devido às limitações de ambiente compartilhado, não podemos usar o composer global na raiz do site para este componente específico. Siga os passos abaixo para atualizar as dependências do `com_splms`:

### 1. NAVEGUE ATÉ A PASTA DE ASSETS
No terminal (ou via SSH), vá para a pasta onde o `composer.json` do componente está localizado:
```bash
cd /home/dante/public_html/guidewaylms/components/com_splms/assets/
```

### 2. EXECUTE O UPDATE MANUALMENTE
Se você tem permissão de execução do composer localmente:
```bash
composer update
```
Isso vai gerar a pasta `vendor/` dentro de `assets/`.

### 3. VERIFIQUE A PASTA VENDOR
Certifique-se de que a estrutura ficou assim:
```
components/com_splms/assets/
├── composer.json
├── composer.lock
└── vendor/
    ├── autoload.php
    └── smalot/
        └── pdfparser/
```

### 4. UPDATE NO SERVIDOR (Se não tiver composer)
Se você não tem acesso ao composer no servidor:
1. Rode `composer update` na sua máquina local (dentro da estrutura correta de pastas).
2. Faça upload da pasta `vendor` inteira para `/components/com_splms/assets/` via FTP/SFTP.

## 🚀 Fluxo de Trabalho Recomendado

### Primeira Vez (Configuração Inicial)

1. **Crie dados de teste:**
   ```
   http://seu-dominio.com/guidewaylms/devops/create_test_data.php
   ```

2. **Verifique o schema (opcional):**
   ```
   http://seu-dominio.com/guidewaylms/devops/debug_db_columns.php
   ```

3. **Execute o teste:**
   ```
   http://seu-dominio.com/guidewaylms/devops/test_announcements_check.php
   ```

### Debug de Problemas

**Se o teste retornar "FALSE" ou erro SQL:**
1. Execute `debug_db_columns.php` para verificar se as colunas estão corretas
2. Verifique se existe uma matrícula (ordem) na tabela `#__splms_orders`
3. Verifique se o usuário está logado corretamente

**Se o teste retornar "0 avisos encontrados":**
1. Isso é normal se não houver avisos cadastrados
2. Acesse o painel admin e crie um aviso para o curso
3. OU aguarde a implementação do CRUD de avisos

---

## 📋 Informações Técnicas

### Model Testado
- **Arquivo:** `/components/com_splms/models/announcements.php`
- **Classe:** `SplmsModelAnnouncements`
- **Método Principal:** `getListQuery()`

### Tabelas Utilizadas
- `#__splms_course_announcements` - Armazena os avisos
- `#__splms_orders` - Registra matrículas (verificação de acesso)
- `#__users` - Informações do usuário/autor

### Colunas Importantes

**Na tabela `#__splms_course_announcements`:**
- `id` - ID do aviso
- `course_id` - ID do curso
- `title` - Título do aviso
- `message` - Conteúdo (aliasado como `description` no Model)
- `created_by` - ID do autor
- `created_at` - Data de criação (aliasado como `created_on` no Model)

**Na tabela `#__splms_orders`:**
- `id` - ID da ordem
- `order_user_id` - ID do usuário matriculado
- `course_id` - ID do curso
- `published` - Status da matrícula (1 = ativa)

---

## ⚠️ Avisos de Segurança

- ⚠️ **NUNCA** deixe estes scripts em produção - são apenas para desenvolvimento/debug
- ⚠️ Os scripts fazem bootstrap completo do Joomla e podem expor informações sensíveis
- ⚠️ Mova ou delete a pasta `/devops/` antes do deploy em produção

---

## 🐛 Problemas Conhecidos

### "Class 'JFactory' not found"
- **Causa:** Bootstrap do Joomla não foi carregado corretamente
- **Solução:** Verifique se o caminho `JPATH_BASE` está correto

### "Unknown column 'a.published'"
- **Causa:** A tabela `#__splms_course_announcements` não tem coluna `published`
- **Solução:** Já removido do código (versão atual não filtra por publicação)

### "Unknown column 'a.description'"
- **Causa:** A tabela usa `message`, não `description`
- **Solução:** Já corrigido com alias no Model

---

## 👥 Para a Equipe

Se você estiver começando a trabalhar neste projeto:

1. Leia este README primeiro
2. Execute `create_test_data.php` para preparar o ambiente
3. Execute `test_announcements_check.php` para verificar se tudo está funcionando
4. Se encontrar erros, use `debug_db_columns.php` para investigar

**Dúvidas?** Consulte a documentação do Joomla ou entre em contato com a equipe.

---

📅 **Última Atualização:** 23/11/2025  
🔧 **Desenvolvido por:** Equipe Guideway LMS
# Integração Groq API - Documentação Técnica

Este documento detalha a implementação da infraestrutura de integração com a API Groq no Guideway LMS.

## 🏗️ Arquitetura Implementada

A solução foi construída seguindo os padrões de segurança e arquitetura do Joomla:

1.  **Configuração Segura (`config.xml`)**:
    *   Novo campo `groq_api_key` adicionado ao painel de opções do componente.
    *   Tipo `password` para garantir que a chave não fique visível em texto plano na interface.
    *   Armazenamento criptografado nativo do Joomla na tabela `#__extensions`.

2.  **Helper de Integração (`GuidewayAIHelper.php`)**:
    *   Classe centralizada para todas as operações de IA.
    *   Método `getGroqApiKey()`: Recupera e valida a chave de forma segura.
    *   Método `makeApiRequest()`: Motor cURL robusto com tratamento de erros e SSL.
    *   Método `smokeTest()`: Teste de conectividade "Hello World".

---

## ⚙️ Configuração (Pré-Requisito)

Antes de executar qualquer teste, você precisa configurar a chave da API no Joomla:

1.  Acesse o Painel Administrativo do Joomla (`/administrator`).
2.  Vá em **Componentes** > **SP LMS** > **PAINEL** > **OPÇÕES** (botão no canto superior direito).
3.  Procure a aba ou seção **Configuração de API** (API Configuration).
4.  Insira sua chave: `gsk_...` (Sua chave Groq)
5.  Salve.

---

## 🚀 Como Testar (Smoke Test)

Uma vez configurada a chave, você pode executar o teste de duas formas: via **Navegador** (mais fácil) ou via **Terminal** (Docker).

### Opção A: Via Navegador (Recomendado)

1.  Acesse a pasta de testes no seu navegador:
    `http://localhost/guidewaylms/devops/index.php` (ajuste a URL conforme seu ambiente).
2.  No painel "Scripts de Teste", localize a coluna **🤖 Inteligência Artificial**.
3.  Clique no botão **🤖 Groq Smoke Test**.

O resultado será exibido formatado na tela.

### Opção B: Via Terminal (Docker)

Se preferir ou precisar debugar via CLI:

1.  Verifique se o arquivo está acessível:
    ```bash
    docker exec -it php8.3 ls -l /var/www/html/guidewaylms/devops/groq_smoke_test.php
    ```

2.  Execute o teste:
    ```bash
    docker exec -it php8.3 php /var/www/html/guidewaylms/devops/groq_smoke_test.php
    ```

### 3. Resultado Esperado
Você deve ver uma saída similar a esta:

```text
=== GROQ API SMOKE TEST ===
API Key: gsk_bHKCRa...

✅ Teste executado com sucesso!

Resposta da API:
Array
(
    [success] => 1
    [message] => Connection established successfully
    [data] => Array
        (
            [id] => chatcmpl-...
            [choices] => Array
                (
                    [0] => Array
                        (
                            [message] => Array
                                (
                                    [role] => assistant
                                    [content] => Hello! Connection successful.
                                )
...
```

---

## Como usar no código (Futuro)

Para usar a integração em outras partes do sistema, basta chamar:

```php
// Importar o helper (se necessário)
require_once JPATH_COMPONENT_SITE . '/helpers/GuidewayAIHelper.php';

try {
    // Exemplo de chamada
    $apiKey = GuidewayAIHelper::getGroqApiKey();
    // ... lógica de chamada ...
} catch (Exception $e) {
    // Tratamento de erro
}
```

---

## 🔍 Logs e Debug

Todas as operações são registradas no log do Joomla.
Para ver os logs em tempo real:

```bash
docker exec -it php8.3 tail -f /var/www/html/guidewaylms/administrator/logs/com_splms.php
```
