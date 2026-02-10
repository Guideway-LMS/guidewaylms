<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 **/

defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Filesystem\Path; 

class SplmsControllerLesson extends FormController {

  public function __construct($config = array()) {
    parent::__construct($config);
  }

  public function getModel($name = 'Lessons', $prefix = 'SplmsModel', $config = array()) {
    return parent::getModel($name, $prefix, $config);
  }

  public function completeditem() {
    $model  = $this->getModel();
    $user   = Factory::getUser();
    $input  = Factory::getApplication()->input;
    $output = array();

    if(!$user->id) {
      $output['status'] = false;
      $output['content'] = Text::_('COM_SPLMS_LOGIN_TO_REVIEW');
      echo json_encode($output);
      die();
    }

    $item_id   = $input->post->get('item_id', 0, 'INT');
    $item_type = $input->post->get('item_type', NULL, 'STRING');

    $output['status'] = false;
    if($item_id && $item_type) {
      $submitted = $model->completedItem($item_id, $item_type, $user->id);
      $output['content'] = Text::_('COM_SPLMS_LESSON_COMPLETED');
      $output['status'] = true;
    }

    echo json_encode($output);
    die();
  }

  // --- NOVA FUNÇÃO DE ENVIO (CORRIGIDA PARA task=lesson.uploadAssignment) ---
  public function uploadAssignment() {
      
      // 1. Inicialização
      $app   = Factory::getApplication();
      $input = $app->input;
      $user  = Factory::getUser();

      // DEBUG: Se a tela ficar branca com esta mensagem, o controller funcionou!
      // Se o envio funcionar, mantenha esta linha comentada ou apague-a.
      // die('DEBUG: O Controller uploadAssignment foi acionado com sucesso!');

      // Checa login
      if ($user->guest) {
          $this->setRedirect('index.php', 'Você precisa estar logado.', 'warning');
          return false;
      }

      // Checa Token CSRF
      if (!\Joomla\CMS\Session\Session::checkToken()) {
           // Em alguns casos de upload via AJAX o token pode falhar, 
           // mas por segurança mantemos a checagem.
           $this->setRedirect('index.php', 'Sessão expirada. Recarregue a página.', 'error');
           return false;
      }

      // 2. Recebe os dados
      $lesson_id = $input->getInt('lesson_id');
      
      // Tenta pegar o arquivo com nomes comuns (garante compatibilidade com views diferentes)
      $file = $input->files->get('uploaded_file'); 
      if (!$file) {
          $file = $input->files->get('assignment_file'); 
      }
      if (!$file) {
           $file = $input->files->get('arquivo'); 
      }

      // URL para redirecionamento
      $redirectUrl = Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false);

      // 3. Validações
      if (!$file || $file['error'] != 0) {
          $this->setRedirect($redirectUrl, 'Erro: Nenhum arquivo recebido. Verifique o tamanho.', 'warning');
          return false;
      }

      // Valida Tamanho (10MB)
      if ($file['size'] > 10 * 1024 * 1024) {
          $this->setRedirect($redirectUrl, 'Arquivo muito grande (Máx 10MB).', 'warning');
          return false;
      }

      // Valida Extensão
      $allowedExts = ['pdf', 'zip', 'rar', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'mp4', 'txt'];
      $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
      if (!in_array($ext, $allowedExts)) {
          $this->setRedirect($redirectUrl, 'Formato de arquivo não permitido.', 'error');
          return false;
      }

      // 4. Upload Físico
      $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
      if (!file_exists($uploadDir)) {
          mkdir($uploadDir, 0755, true);
          file_put_contents($uploadDir . 'index.html', '');
      }

      $cleanName   = Path::clean(pathinfo($file['name'], PATHINFO_FILENAME));
      $cleanName   = preg_replace('/[^a-zA-Z0-9_-]/', '', $cleanName); 
      $newFileName = time() . '_u' . $user->id . '_' . $cleanName . '.' . $ext;
      $targetPath  = $uploadDir . $newFileName;
      $dbPath      = 'images/uploads/submissions/' . $newFileName;

      if (!File::upload($file['tmp_name'], $targetPath)) {
          $this->setRedirect($redirectUrl, 'Falha ao salvar o arquivo físico no servidor.', 'error');
          return false;
      }

      // 5. Integração com Tabela e E-mail (Backend)
      // Carrega o caminho das tabelas do administrador
      \Joomla\CMS\Table\Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_splms/tables');
      
      // Instancia a tabela 'Submission'
      $table = \Joomla\CMS\Table\Table::getInstance('Submission', 'SplmsTable');

      // Prepara os dados conforme as colunas do seu banco
      $data = array(
          'user_id'      => (int) $user->id,
          'lesson_id'    => (int) $lesson_id,
          'file_path'    => $dbPath,
          'status'       => 0, // 0 = Pendente
          'submitted_at' => Factory::getDate()->toSql()
      );

      try {
          // Bind, Check e Store (Store aciona o envio de e-mail definido na Table)
          if (!$table->bind($data)) { throw new Exception($table->getError()); }
          if (!$table->check()) { throw new Exception($table->getError()); }
          if (!$table->store()) { throw new Exception($table->getError()); }

          // Sucesso: Atualiza progresso da lição
          $model = $this->getModel();
          if (method_exists($model, 'completedItem')) {
               $model->completedItem($lesson_id, 'lesson', $user->id); 
          }

          $this->setRedirect($redirectUrl, 'Trabalho enviado com sucesso! O professor foi notificado.', 'success');
          return true;

      } catch (Exception $e) {
          // Se der erro no banco, apaga o arquivo para não deixar lixo
          if (file_exists($targetPath)) { File::delete($targetPath); }
          
          $this->setRedirect($redirectUrl, 'Erro ao salvar no banco: ' . $e->getMessage(), 'error');
          return false;
      }
  }

  /* // --- CÓDIGO ANTIGO (BACKUP) ---
  // Mantido comentado conforme solicitado.
  // O nome antigo era 'submit', mas a URL chamava 'uploadAssignment'.
  
  public function submit() {
      // 1. Inicialização e Segurança Básica
      $app   = Factory::getApplication();
      $input = $app->input;
      $user  = Factory::getUser();

      // --- VACINA CONTRA ARQUIVO GIGANTE (SERVER CRASH) ---
      if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($_POST) && empty($_FILES) && $_SERVER['CONTENT_LENGTH'] > 0) {
          
          $lesson_id = $input->getInt('lesson_id', 0); 

          if (!$lesson_id) {
             // MUDANÇA: Se o ID sumiu, vai para a página inicial (seguro)
             $redirectUrl = 'index.php'; 
          } else {
             $redirectUrl = 'index.php?option=com_splms&view=lesson&id=' . $lesson_id;
          }

          $this->setRedirect(
              Route::_($redirectUrl, false),
              'Erro: O arquivo é maior do que o servidor permite (Crash). Tente um arquivo menor que 2MB.',
              'error'
          );
          return false;
      }
      // -----------------------------------------------------

      // 1. Verificação de Login
      if ($user->guest) {
          $this->setRedirect('index.php', 'Você precisa estar logado.', 'warning');
          return false;
      }

      // 2. Verificação de Token (CSRF)
      if (!JSession::checkToken()) {
          $this->setRedirect('index.php', 'Token de segurança inválido. Tente recarregar a página.', 'error');
          return false;
      }

      $lesson_id = $input->getInt('lesson_id');
      $file      = $input->files->get('uploaded_file');
      $comment   = $input->getString('student_comment', ''); // Captura o comentário (se já tiver criado a coluna no banco)

      // Configurações de Segurança
      $maxSize = 10 * 1024 * 1024; // 10MB em Bytes
$allowedExts = ['pdf', 'doc', 'docx', 'txt', 'jpg', 'jpeg', 'png', 'mp4'];

      if ($file && $file['error'] == 0) {
          
          $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

          // TRAVA 1: Extensão não permitida (Bloqueia .exe, .php, etc)
          if (!in_array($ext, $allowedExts)) {
              $this->setRedirect(
                  Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                  'Erro: Formato de arquivo não permitido (.' . $ext . '). Envie apenas: PDF, ZIP, DOC, Imagens ou MP4.',
                  'error'
              );
              return false;
          }

          // TRAVA 2: Tamanho do Arquivo (Bloqueia > 10MB)
          if ($file['size'] > $maxSize) {
              $this->setRedirect(
                  Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                  'Erro: O arquivo é muito grande. O limite máximo é 10MB.',
                  'error'
              );
              return false;
          }

          // Se passou pelas travas, prepara o upload
          $uploadDir = JPATH_ROOT . '/images/uploads/submissions/';
          if (!file_exists($uploadDir)) {
              mkdir($uploadDir, 0755, true);
          }

          // Limpeza do nome do arquivo (Segurança extra)
          $cleanName = Path::clean(pathinfo($file['name'], PATHINFO_FILENAME));
          $cleanName = preg_replace('/[^a-zA-Z0-9_-]/', '', $cleanName); 
          $newFileName = time() . '_' . $cleanName . '.' . $ext;
          $targetPath = $uploadDir . $newFileName;
          $dbPath = 'images/uploads/submissions/' . $newFileName;

          if (File::upload($file['tmp_name'], $targetPath)) {
              $db = Factory::getDbo();
              $query = $db->getQuery(true);
              
              // Verifica se a coluna student_comment existe na tabela antes de tentar inserir
              // Se você ainda não rodou o SQL da coluna, remova 'student_comment' daqui temporariamente
              $columns = array('user_id', 'lesson_id', 'file_path', 'status', 'submitted_at'); 
              $values  = array((int) $user->id, (int) $lesson_id, $db->quote($dbPath), 0, 'NOW()');

              // Se quiser salvar o comentário, descomente a linha abaixo quando atualizar o banco:
              // $columns[] = 'student_comment'; $values[] = $db->quote($comment);

              $query->insert($db->quoteName('#__splms_submissions'))
                    ->columns($db->quoteName($columns))
                    ->values(implode(',', $values));
              
              $db->setQuery($query);
              
              try {
                  $db->execute();

                  // Marca a lição como completa no sistema geral do SPLMS
                  $model = $this->getModel();
                  $model->completedItem($lesson_id, 'lesson', $user->id); 

                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                      'Trabalho enviado com sucesso! Aguarde a correção.'
                  );
                  return true;

              } catch (Exception $e) {
                  $this->setRedirect(
                      Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
                      'Erro ao salvar no banco: ' . $e->getMessage(),
                      'error'
                  );
                  return false;
              }
          }
      }

      // Erro genérico de upload (ex: arquivo corrompido ou maior que o post_max_size do PHP)
      $this->setRedirect(
          Route::_('index.php?option=com_splms&view=lesson&id=' . $lesson_id, false),
          'Erro no upload. Verifique se o arquivo não ultrapassa o limite do servidor.',
          'error'
      );
      return false;
  }
  */
}