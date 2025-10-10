# 1. Baixando o Projeto e Configurando sua Branch
Com o acesso liberado, vamos clonar o repositório para a sua pasta de projetos no WSL.

a. Abra o terminal do WSL
Abra o Windows Terminal, que por padrão deve iniciar no seu ambiente Linux.

b. Navegue até a pasta de projetos
O nosso ambiente Docker está configurado para servir os arquivos que estão na pasta public_html. Vamos clonar o projeto para dentro dela.

```bash
cd ~/public_html
```
c. Clone o repositório do GitHub
Use o comando git clone com a URL SSH oficial do repositório.

```bash
git clone git@github.com:Guideway-LMS/guidewaylms.git
```

Após a clonagem, uma nova pasta chamada guidewaylms será criada dentro de public_html. Entre nela:

```Bash
cd guidewaylms
```

d. Mude para a branch do seu grupo
O projeto está organizado em branches de desenvolvimento separadas. É essencial que você trabalhe na branch correta.

Se você é do Grupo 1:

```bash
git checkout grupo1
```

Se você é do Grupo 2:

```bash
git checkout grupo2
```

Para confirmar que você está na branch certa, use o comando git branch. A branch ativa estará marcada com um *.

# 2. Configuração do Projeto Local
Depois de baixar os arquivos, você precisa configurar o projeto para que ele funcione no seu ambiente.

a. Importando o Banco de Dados

Localize o arquivo de dump: No diretório do projeto, há uma pasta _dumps. Dentro dela, utilize o arquivo .sql mais recente.

Importe o dump via linha de comando:

**ATENÇÃO:** Substitua 'nome_do_dump.sql' e 'guideway_lms_db' se necessário.
```bash
mysql -u root -p -e "CREATE DATABASE IF NOT EXISTS guideway_lms_db;"
mysql -u root -p guideway_lms_db < _dumps/nome_do_dump.sql
```
(A senha solicitada é us35#w3(b))

Sugestão de Software (GUI): Para gerenciar o banco de dados de forma visual, você pode usar softwares no Windows.

Host: localhost (Se encontrar problemas para conectar, tente usar mariadb)

>Porta: 3306
>
>Usuário: root
>
>Senha: us35#w3(b)%

**Softwares recomendados:** DBeaver, HeidiSQL ou Navicat.

b. Arquivo de Configuração (configuration.php)

Duplique o arquivo de exemplo:

Estando na pasta ~/public_html/guidewaylms
```bash
cp "configuration - example.php" configuration.php
```

Ajuste o conteúdo: Abra o novo arquivo configuration.php e edite as variáveis conforme as instruções internas.

c. Arquivo de Roteamento (.htaccess)

Duplique o arquivo de exemplo:

Estando na pasta ~/public_html/guidewaylms

```bash
cp ".htaccess - example" .htaccess
```

Ajuste o RewriteBase: Abra o novo .htaccess e localize a linha que contém RewriteBase /guidewaylms/. Este valor deve corresponder ao nome do diretório do seu projeto.

Se sua URL é https://localhost/guidewaylms/, o valor está correto.

Exemplo: Se você renomeou a pasta do projeto para meu-projeto, a URL seria https://localhost/meu-projeto/ e a linha deveria ser RewriteBase /meu-projeto/.

Se o projeto está na raiz (public_html), a linha deve ser RewriteBase /.

d. Acessando a Área Administrativa (Joomla)

Após configurar o site, você precisará acessar o painel de administração da plataforma, que é baseada em Joomla.

**URL de Acesso:** Para acessar a área administrativa, basta adicionar /administrator ao final da URL do seu site local.

Exemplo: Se o endereço do seu site é https://localhost/guidewaylms/, a URL de acesso ao painel de administração será https://localhost/guidewaylms/administrator/.

Credenciais de Acesso: O usuário e a senha para acessar a área administrativa são:

>Usuário: lmswill
>
>Senha: 8w6vvO3CSKAQ1ALLo8

# 3. Ferramenta Recomendada: Visual Studio Code
Sugerimos o uso do Visual Studio Code (VS Code) como editor de código principal. Ele se integra perfeitamente com o WSL e possui um cliente Git visual que facilita o fluxo de trabalho.

a. Fluxo de Trabalho com Git no VS Code

Sincronizar Alterações (Pull): Antes de começar a trabalhar, sempre puxe as últimas alterações da sua branch. Clique no botão de sincronização na barra de status inferior. Ele mostrará o número de commits para puxar (seta para baixo) e empurrar (seta para cima).
[Imagem de um botão de sincronização no VS Code]

Adicionar e "Committar" Alterações:

Acesse a aba Source Control (ícone de galho de árvore na lateral ou Ctrl+Shift+G).

Os arquivos modificados aparecerão na lista. Clique no + ao lado de cada arquivo que deseja incluir no commit (ou do título "Changes" para adicionar todos).

Digite sua mensagem de commit na caixa de texto no topo.

Clique no botão de "check" (✓) para "committar".
[Imagem da interface de commit do VS Code]

Enviar Alterações (Push): Após fazer o commit, o botão de sincronização na barra de status mostrará uma seta para cima, indicando que você tem commits locais para enviar. Clique nele para fazer o push para a sua branch no GitHub.

b. Extensões Úteis para PHP

PHP Intelephense: Fornece autocompletar de código inteligente, documentação e análise de erros.

PHP Debug: Permite depurar seu código PHP usando Xdebug.

PHP DocBlocker: Facilita a criação de blocos de comentários padronizados.

GitLens: Superpotencializa os recursos do Git, mostrando o histórico do arquivo e de cada linha.

c. Dica: Destaque para Comentários TODO e FIXME

Para visualizar facilmente tarefas pendentes (TODO) ou pontos que precisam de correção (FIXME), instale a extensão TODO Highlight.

Para customizar as cores (ex: amarelo para TODO e vermelho para FIXME):

Pressione Ctrl+Shift+P para abrir a paleta de comandos.

Digite e selecione Preferences: Open User Settings (JSON).

Adicione o seguinte código ao seu arquivo settings.json:

```JSON
"todohighlight.keywords": [
  {
    "text": "TODO:",
    "color": "#000",
    "backgroundColor": "#ffab00",
    "overviewRulerColor": "rgba(255,171,0,0.7)"
  },
  {
    "text": "FIXME:",
    "color": "#fff",
    "backgroundColor": "#e01c31",
    "overviewRulerColor": "rgba(224,28,49,0.7)"
  }
]
```

# 4. Boas Práticas para Mensagens de Commit
Uma mensagem de commit clara ajuda toda a equipe. Siga o padrão: tipo: Descrição curta da alteração

>tipo: Define a categoria da alteração.
>
>feat: Novas funcionalidades.
>
>fix: Correção de bugs.
>
>docs: Alterações na documentação.
>
>style: Ajustes de formatação de código.
>
>refactor: Refatoração de código.
>
>chore: Tarefas de manutenção.

Exemplos:

>feat: Implementa sistema de login com e-mail e senha
>
>fix: Corrige erro de SQL ao salvar novo curso
>
>docs: Adiciona manual de instalação do projeto
