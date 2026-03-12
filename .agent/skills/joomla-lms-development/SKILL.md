---
name: joomla-lms-development
description: Desenvolve e mantém uma plataforma LMS (Learning Management System) robusta em modelo Micro SaaS usando Joomla 5 como base, com SP LMS como extensão principal. Segue rigorosamente as diretrizes de código legado (J3) vs. moderno (J5) conforme o contexto.
---

# Joomla LMS Development Skill

Quando desenvolver ou modificar código para o Guideway LMS, siga estas diretrizes críticas:

## Stack Tecnológica

### Core do Sistema
- **CMS**: Joomla 5
- **Extensão LMS Base**: SP LMS (JoomShaper)
- **Page Builder**: SP Page Builder (JoomShaper)
- **Banco de Dados**: MySQL/MariaDB
- **Linguagens**: PHP 8.x, JavaScript (ES6+), HTML5, CSS3
- **Ambiente**: WSL + Docker

### Ambiente de Desenvolvimento
```bash
# Repositório
git@github.com:Guideway-LMS/guidewaylms.git

# Branches de trabalho
- grupo1
- grupo2

# Docker Service
Host: mariadb
User: root
Password: us35#w3(b)%
Database: guideway_lms_db
```

## Regra de Ouro: O Que Pode e Não Pode Editar

### ❌ NÃO PODE EDITAR (Core Protegido)
**ESTRITAMENTE PROIBIDO** editar:
- Arquivos do core do Joomla
- Extensões de terceiros que recebem atualizações (ex: SP Page Builder)
- **Motivo**: Quebra futuras atualizações do sistema

### ✅ PODE E DEVE EDITAR (SP LMS)
A extensão **SP LMS** é a **EXCEÇÃO**. Não está mais sendo ativamente evoluída pela desenvolvedora. Nosso projeto assumiu sua evolução.

**Arquivos editáveis do SP LMS**:
- `com_splms` (Componente principal)
- `mod_splmscart` (Módulo de carrinho)
- `mod_splmscoursecategories` (Módulo de categorias)
- `mod_splmscourses` (Módulo de cursos)
- `mod_splmscoursesearch` (Módulo de busca)
- `mod_splmsperson` (Módulo de pessoa)
- `user_lmsprofile` (Plugin de perfil)

## Paradigma de Código: Legado vs. Moderno

### Cenário A: Modificações no SP LMS (PADRÃO LEGADO J3 OBRIGATÓRIO)

**Quando usar**: Ao criar funcionalidades que se integram diretamente ao `com_splms` ou modificam seu comportamento (ex: Mural de Avisos conectado aos cursos do SP LMS).

**Justificativa**: Garantir estabilidade e compatibilidade com a base de código J3 existente. Forçar estrutura moderna (J5) dentro de extensão legada (J3) é inviável e instável.

#### ✅ O Que USAR (Padrão Legado J3)

**Classes Globais**:
```php
// Database
$db = JFactory::getDbo();

// Application
$app = JFactory::getApplication();

// User
$user = JFactory::getUser();

// Document
$doc = JFactory::getDocument();

// Session
$session = JFactory::getSession();
```

**JHtml para Assets**:
```php
// JavaScript
JHtml::_('script', 'com_splms/custom.js', ['relative' => true]);

// CSS
JHtml::_('stylesheet', 'com_splms/custom.css', ['relative' => true]);
```

**Estrutura de Arquivos Legada**:
```
components/com_splms/
├── controller.php
├── controllers/
│   └── meucontroller.php
├── models/
│   └── meumodel.php
├── views/
│   └── minhaview/
│       ├── view.html.php
│       └── tmpl/
│           └── default.php
└── helpers/
    └── meuhelper.php
```

**Classes Base Legadas**:
```php
// Controller
class SplmsControllerMeuController extends JControllerLegacy
{
    public function display($cachable = false, $urlparams = [])
    {
        parent::display($cachable, $urlparams);
    }
}

// View
class SplmsViewMinhaView extends JViewLegacy
{
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        parent::display($tpl);
    }
}

// Model
class SplmsModelMeuModel extends JModelList
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        // ...
        return $query;
    }
}
```

**Query Building Legado**:
```php
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$query->select($db->quoteName(['id', 'title', 'created']))
      ->from($db->quoteName('#__splms_minha_tabela'))
      ->where($db->quoteName('published') . ' = 1')
      ->order($db->quoteName('created') . ' DESC');

$db->setQuery($query);
$results = $db->loadObjectList();
```

**Redirecionamento Legado**:
```php
$app = JFactory::getApplication();
$app->redirect('index.php?option=com_splms&view=courses', 'Mensagem de sucesso', 'message');
```

#### ⚠️ Técnicas Modernas Permitidas (Se 100% Compatíveis)

Apenas se não quebrarem a estrutura legada:
```php
// Helper moderno chamado por view legada
use MinhaEmpresa\Component\Splms\Site\Helper\ModernHelper;

class SplmsViewMinhaView extends JViewLegacy
{
    public function display($tpl = null)
    {
        // Usar helper moderno é OK se ele não depende de infraestrutura J5
        $this->data = ModernHelper::processData();
        parent::display($tpl);
    }
}
```

### Cenário B: Novas Extensões Independentes (PADRÃO MODERNO J5)

**Quando usar**: Ao criar novos componentes, módulos ou plugins que NÃO são extensões diretas do SP LMS (não dependem de suas classes ou tabelas para funcionar).

**Diretriz**: Priorizar desenvolvimento moderno (J5).

#### ✅ O Que USAR (Padrão Moderno J5)

**Namespaces (PSR-4)**:
```php
namespace MinhaEmpresa\Component\MeuComponente\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class DisplayController extends BaseController
{
    public function display($cachable = false, $urlparams = [])
    {
        $app = Factory::getApplication();
        return parent::display($cachable, $urlparams);
    }
}
```

**Factory Moderna**:
```php
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

// Database
$db = Factory::getContainer()->get('DatabaseDriver');

// Application
$app = Factory::getApplication();

// User
$user = $app->getIdentity();

// URI
$uri = Uri::getInstance();
```

**Web Asset Manager**:
```php
use Joomla\CMS\Factory;

$wa = Factory::getApplication()->getDocument()->getWebAssetManager();

// JavaScript
$wa->registerAndUseScript(
    'com_meucomponente.script',
    'com_meucomponente/script.js',
    [],
    ['defer' => true],
    ['component' => 'com_meucomponente']
);

// CSS
$wa->registerAndUseStyle(
    'com_meucomponente.style',
    'com_meucomponente/style.css',
    [],
    [],
    ['component' => 'com_meucomponente']
);
```

**Estrutura de Arquivos Moderna**:
```
components/com_meucomponente/
├── joomla.xml
├── services/
│   └── provider.php
├── src/
│   ├── Controller/
│   │   └── DisplayController.php
│   ├── Model/
│   │   └── MeuModel.php
│   ├── View/
│   │   └── MinhaView/
│   │       └── HtmlView.php
│   └── Helper/
│       └── MeuHelper.php
└── tmpl/
    └── minhaview/
        └── default.php
```

**Services Provider**:
```php
// services/provider.php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\MinhaEmpresa\\Component\\MeuComponente'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\MinhaEmpresa\\Component\\MeuComponente'));
        $container->registerServiceProvider(new RouterFactory('\\MinhaEmpresa\\Component\\MeuComponente'));
    }
};
```

**Query Builder Moderno**:
```php
use Joomla\CMS\Factory;

$db = Factory::getContainer()->get('DatabaseDriver');
$query = $db->getQuery(true);

$query->select($db->quoteName(['id', 'title', 'created']))
      ->from($db->quoteName('#__minha_tabela_nova'))
      ->where($db->quoteName('state') . ' = :state')
      ->bind(':state', 1, Joomla\Database\ParameterType::INTEGER)
      ->order($db->quoteName('created') . ' DESC');

$db->setQuery($query);
$results = $db->loadObjectList();
```

**Fonte da Verdade**: Para o cenário moderno, consulte a documentação oficial em https://manual.joomla.org/

## Regras de Banco de Dados (OBRIGATÓRIO)

### 🔒 Princípio Fundamental

**O Dump Existente (`dump.sql`) é IMUTÁVEL**

### Regras Críticas

1. **NÃO PODE MODIFICAR**: O desenvolvimento não pode exigir modificações (ex: `ALTER TABLE`) nas tabelas existentes do SP LMS.

2. **Justificativa**: A base de código legada (J3) do SP LMS depende dessa estrutura específica. Modificá-la pode causar falhas inesperadas e quebrar a funcionalidade central.

3. **PODE CRIAR**: Novas tabelas e pastas/arquivos são permitidos e incentivados.

4. **Princípio Central**: A solução de código (especialmente legada J3) deve se adaptar à estrutura do dump existente, **não o contrário**.

### ✅ Abordagem Correta para Novas Funcionalidades
```php
// CORRETO: Criar nova tabela para nova funcionalidade
CREATE TABLE IF NOT EXISTS `#__splms_course_announcements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created` datetime NOT NULL,
  `created_by` int(11) NOT NULL,
  `published` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `idx_course` (`course_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

// Depois, relacionar com curso existente via JOIN
$db = JFactory::getDbo();
$query = $db->getQuery(true);

$query->select('a.*, c.title AS course_title')
      ->from($db->quoteName('#__splms_course_announcements', 'a'))
      ->join('LEFT', $db->quoteName('#__splms_courses', 'c') . ' ON c.id = a.course_id')
      ->where($db->quoteName('a.published') . ' = 1');
```

### ❌ Abordagem PROIBIDA
```php
// ERRADO: Tentar adicionar coluna em tabela existente do SP LMS
ALTER TABLE `#__splms_courses` ADD COLUMN `announcements_enabled` tinyint(1);
// ❌ ISSO É PROIBIDO!
```

### Estratégia de Persistência

Para qualquer nova funcionalidade:
1. Crie **nova tabela** isolada
2. Use **chaves estrangeiras conceituais** (relacionamentos via JOIN)
3. Mantenha as tabelas do SP LMS **intocadas**
4. Documente o relacionamento entre tabelas
```php
// Exemplo: Mural de Avisos para Cursos
// Tabela: #__splms_course_announcements (NOVA)
// Relacionamento: announcements.course_id -> courses.id (via JOIN, não FK física)
```

## Checklist de Desenvolvimento

### Antes de Começar

- [ ] Identifiquei se é modificação do SP LMS (Cenário A) ou extensão nova (Cenário B)
- [ ] Verifiquei se preciso criar novas tabelas (não modificar existentes)
- [ ] Confirmei o padrão de código a ser usado (Legado J3 ou Moderno J5)
- [ ] Li a documentação relevante (código existente para J3, manual.joomla.org para J5)

### Durante o Desenvolvimento (Cenário A - SP LMS / Legado J3)

- [ ] Usei `JFactory` para database, application, user, session
- [ ] Usei `JHtml` para incluir JavaScript e CSS
- [ ] Criei controllers com `JControllerLegacy`
- [ ] Criei views com `JViewLegacy`
- [ ] Criei models com `JModelList` ou `JModelItem`
- [ ] Estrutura de arquivos segue o padrão legado (controller.php, view.html.php, tmpl/)
- [ ] Queries usam `$db->getQuery(true)` com quote/quoteName
- [ ] NÃO tentei misturar namespaces modernos na estrutura legada
- [ ] NÃO modifiquei tabelas existentes do SP LMS

### Durante o Desenvolvimento (Cenário B - Extensão Nova / Moderno J5)

- [ ] Usei namespaces PSR-4 corretos
- [ ] Criei `services/provider.php` com DI Container
- [ ] Usei `Factory` moderna (Joomla\CMS\Factory)
- [ ] Implementei Web Asset Manager para JS/CSS
- [ ] Estrutura de arquivos moderna (src/, Controller/, Model/, View/)
- [ ] Classes extendem de `Joomla\CMS\MVC\Controller\BaseController` ou similares
- [ ] Query builder usa bind parameters quando apropriado
- [ ] Segui a documentação oficial (manual.joomla.org)

### Banco de Dados

- [ ] Verifiquei que NÃO estou modificando tabelas existentes do SP LMS
- [ ] Se precisei de persistência, criei NOVA tabela
- [ ] Nomeei novas tabelas com prefixo `#__splms_` se relacionadas ao LMS
- [ ] Documentei relacionamentos entre tabelas novas e existentes
- [ ] Testei queries com dados reais do dump

### Testes e Qualidade

- [ ] Testei a funcionalidade no ambiente Docker local
- [ ] Verifiquei logs de erro do PHP e Joomla
- [ ] Testei com diferentes níveis de usuário (admin, instrutor, aluno)
- [ ] Código segue PSR-12 (para J5) ou convenções Joomla legadas (para J3)
- [ ] Não há SQL injection vulnerabilities (uso de bind ou quote)
- [ ] XSS prevention (escape de output com htmlspecialchars ou JText)

### Antes de Commit

- [ ] Código está na branch correta (grupo1 ou grupo2)
- [ ] Removi console.log e var_dump de debug
- [ ] Comentei código complexo quando necessário
- [ ] Mensagem de commit é descritiva
- [ ] Verifiquei que não commitei arquivos de configuração sensíveis

## Padrões de Código Comuns

### Padrão: Adicionar Nova Funcionalidade ao SP LMS (Legado J3)
```php
// 1. Criar nova tabela (via SQL ou installer)
CREATE TABLE IF NOT EXISTS `#__splms_minha_feature` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `course_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `data` text,
  `created` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

// 2. Criar Model (components/com_splms/models/minhafeature.php)
defined('_JEXEC') or die;

class SplmsModelMinhafeature extends JModelList
{
    protected function getListQuery()
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $query->select('a.*, c.title AS course_title')
              ->from($db->quoteName('#__splms_minha_feature', 'a'))
              ->join('LEFT', $db->quoteName('#__splms_courses', 'c') . ' ON c.id = a.course_id')
              ->order('a.created DESC');
        
        return $query;
    }
}

// 3. Criar View (components/com_splms/views/minhafeature/view.html.php)
defined('_JEXEC') or die;

class SplmsViewMinhafeature extends JViewLegacy
{
    protected $items;
    
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        
        if (count($errors = $this->get('Errors'))) {
            JError::raiseError(500, implode("\n", $errors));
            return false;
        }
        
        $this->addToolbar();
        parent::display($tpl);
    }
    
    protected function addToolbar()
    {
        JToolbarHelper::title('Minha Feature', 'list');
    }
}

// 4. Criar Template (components/com_splms/views/minhafeature/tmpl/default.php)
defined('_JEXEC') or die;

JHtml::_('stylesheet', 'com_splms/minhafeature.css', ['relative' => true]);
JHtml::_('script', 'com_splms/minhafeature.js', ['relative' => true]);
?>

<div class="splms-minhafeature">
    <?php if (!empty($this->items)) : ?>
        <?php foreach ($this->items as $item) : ?>
            <div class="item">
                <h3><?php echo htmlspecialchars($item->title); ?></h3>
                <p class="course">Curso: <?php echo htmlspecialchars($item->course_title); ?></p>
                <div class="content">
                    <?php echo nl2br(htmlspecialchars($item->data)); ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else : ?>
        <p>Nenhum item encontrado.</p>
    <?php endif; ?>
</div>

// 5. Criar Controller para ações (components/com_splms/controllers/minhafeature.php)
defined('_JEXEC') or die;

class SplmsControllerMinhafeature extends JControllerLegacy
{
    public function save()
    {
        JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
        
        $app = JFactory::getApplication();
        $data = $app->input->post->get('jform', [], 'array');
        
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        
        $columns = ['course_id', 'title', 'data', 'created'];
        $values = [
            $db->quote($data['course_id']),
            $db->quote($data['title']),
            $db->quote($data['data']),
            $db->quote(JFactory::getDate()->toSql())
        ];
        
        $query->insert($db->quoteName('#__splms_minha_feature'))
              ->columns($db->quoteName($columns))
              ->values(implode(',', $values));
        
        $db->setQuery($query);
        
        try {
            $db->execute();
            $app->redirect('index.php?option=com_splms&view=minhafeature', 'Item salvo com sucesso!', 'message');
        } catch (Exception $e) {
            $app->redirect('index.php?option=com_splms&view=minhafeature', 'Erro ao salvar: ' . $e->getMessage(), 'error');
        }
    }
}
```

### Padrão: Criar Novo Componente Independente (Moderno J5)
```php
// Estrutura:
// components/com_meucomponente/
// ├── joomla.xml
// ├── services/provider.php
// └── src/
//     ├── Controller/DisplayController.php
//     ├── Model/ItemsModel.php
//     └── View/Items/HtmlView.php

// services/provider.php
defined('_JEXEC') or die;

use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

return new class () implements ServiceProviderInterface {
    public function register(Container $container): void
    {
        $container->registerServiceProvider(new MVCFactory('\\MinhaEmpresa\\Component\\MeuComponente'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\MinhaEmpresa\\Component\\MeuComponente'));
    }
};

// src/Controller/DisplayController.php
namespace MinhaEmpresa\Component\MeuComponente\Site\Controller;

use Joomla\CMS\MVC\Controller\BaseController;

class DisplayController extends BaseController
{
    protected $default_view = 'items';
    
    public function display($cachable = false, $urlparams = [])
    {
        return parent::display($cachable, $urlparams);
    }
}

// src/Model/ItemsModel.php
namespace MinhaEmpresa\Component\MeuComponente\Site\Model;

use Joomla\CMS\MVC\Model\ListModel;

class ItemsModel extends ListModel
{
    protected function getListQuery()
    {
        $db = $this->getDatabase();
        $query = $db->getQuery(true);
        
        $query->select($db->quoteName(['id', 'title', 'created']))
              ->from($db->quoteName('#__meucomponente_items'))
              ->where($db->quoteName('state') . ' = :state')
              ->bind(':state', 1)
              ->order($db->quoteName('created') . ' DESC');
        
        return $query;
    }
}

// src/View/Items/HtmlView.php
namespace MinhaEmpresa\Component\MeuComponente\Site\View\Items;

use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView
{
    protected $items;
    
    public function display($tpl = null)
    {
        $this->items = $this->get('Items');
        
        // Web Asset Manager
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->registerAndUseScript(
            'com_meucomponente.items',
            'com_meucomponente/items.js',
            [],
            ['defer' => true],
            ['component' => 'com_meucomponente']
        );
        
        return parent::display($tpl);
    }
}
```

## Mensagens de Erro Comuns e Soluções

### Erro: "Class 'JFactory' not found" (em código J5)
**Causa**: Tentativa de usar classe legada em contexto moderno.
**Solução**: Use `Joomla\CMS\Factory` em vez de `JFactory`.

### Erro: "Namespace not found" (em código J3)
**Causa**: Tentativa de usar namespace moderno em contexto legado.
**Solução**: Remova namespaces e use classes globais (JFactory, JControllerLegacy, etc.).

### Erro: "Table doesn't exist"
**Causa**: Query referencia tabela que não existe no dump.
**Solução**: Crie a tabela via SQL ou installer antes de usá-la.

### Erro: "Cannot modify header information"
**Causa**: Output antes de redirect ou sessão.
**Solução**: Verifique espaços em branco no início/fim dos arquivos PHP. Use `ob_start()` se necessário.

### Erro: Funcionalidade quebra após atualização
**Causa**: Edição de arquivos do core ou SP Page Builder.
**Solução**: Reverta mudanças e reimplemente usando override ou nova extensão.

## Recursos e Referências

### Documentação Oficial
- **Joomla 5 (Moderno)**: https://manual.joomla.org/
- **Joomla 3 (Legado)**: https://docs.joomla.org/J3.x:Developing_an_MVC_Component

### Repositório do Projeto
- **Git**: git@github.com:Guideway-LMS/guidewaylms.git
- **Branches**: grupo1, grupo2

### Banco de Dados
- **Host Docker**: mariadb
- **User**: root
- **Password**: us35#w3(b)%
- **Database**: guideway_lms_db

## Perguntas Frequentes

### Q: Posso usar técnicas J5 dentro do SP LMS?
**A**: Apenas se forem 100% compatíveis e não quebrarem a estrutura legada. Na dúvida, use J3.

### Q: Como sei se devo usar J3 ou J5?
**A**: Pergunta chave: "Isso modifica/integra o SP LMS?" → Sim = J3. Não = J5.

### Q: Preciso adicionar uma coluna em `#__splms_courses`. Posso?
**A**: NÃO. Crie uma nova tabela e relacione via JOIN.

### Q: O SP Page Builder pode ser editado?
**A**: NÃO. Apenas o SP LMS pode ser editado.

### Q: Como faço deploy?
**A**: Commit na branch apropriada (grupo1 ou grupo2). O ambiente Docker deve refletir as mudanças.

## Instrução Final

Ao desenvolver para o Guideway LMS:

1. **Identifique o cenário**: SP LMS (Legado J3) ou extensão nova (Moderno J5)
2. **Siga o padrão correto**: Não misture abordagens
3. **Respeite o banco de dados**: Dump existente é imutável
4. **Teste rigorosamente**: No ambiente Docker local antes de commit
5. **Documente**: Código complexo ou decisões de arquitetura
6. **Não sugira híbridos inviáveis**: Viabilidade > pureza de código

**Lembre-se**: A regra de ouro é compatibilidade e estabilidade. O sistema precisa funcionar hoje e continuar funcionando após futuras manutenções.
