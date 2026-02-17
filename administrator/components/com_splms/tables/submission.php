<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class SplmsTableSubmission extends Table
{
    /**
     * Construtor
     */
    public function __construct(&$db)
    {
        // Vincula esta classe à tabela '#__splms_submissions' e à chave primária 'id'
        parent::__construct('#__splms_submissions', 'id', $db);
    }

    /**
     * Sobrescreve o método store (Salvar) para injetar os e-mails
     */
    public function store($updateNulls = false)
    {
        // 🛑 DEBUG: TELA BRANCA PARA TESTAR CARREGAMENTO DO ARQUIVO 🛑
        ('<h1>SUCESSO! O ARQUIVO TABLES/SUBMISSION.PHP FOI CARREGADO!</h1>');

        // 1. Antes de salvar, tenta pegar os dados antigos do banco
        // Isso serve para compararmos se a nota mudou ou se é um registro novo
        $oldItem = null;
        if ($this->id) {
            // Usamos uma query direta para evitar conflitos de instância
            $db = $this->getDbo();
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName($this->_tbl))
                ->where($db->quoteName($this->_tbl_key) . ' = ' . (int) $this->id);
            $db->setQuery($query);
            $oldItem = $db->loadObject();
        }

        // 2. Salva os dados no banco (Método padrão do Joomla)
        $result = parent::store($updateNulls);

        // Se deu erro ao salvar, para tudo e retorna falso.
        if (!$result) {
            return false;
        }

        // 3. Processa os e-mails (dentro de um try/catch para não travar o sistema se o e-mail falhar)
        try {
            $this->processNotifications($oldItem);
        } catch (Exception $e) {
            // Se o e-mail falhar, logamos o erro mas não impedimos o salvamento
            // Factory::getApplication()->enqueueMessage('Erro no envio de e-mail: ' . $e->getMessage(), 'warning');
        }

        return true;
    }

    /**
     * Lógica Centralizada de envio de e-mail
     */
    protected function processNotifications($oldItem)
    {
        $mailer = Factory::getMailer();
        $config = Factory::getConfig();
        $sender = array($config->get('mailfrom'), $config->get('fromname'));

        // ====================================================================
        // CENÁRIO 1: NOVO ENVIO (Aluno enviou no site -> Notificar Professor)
        // ====================================================================
        // Se não tinha $oldItem (era null), significa que é um registro novo
        if (empty($oldItem)) {
            
            $subject = '[Guideway LMS] Novo trabalho recebido';
            $body    = "Um aluno enviou um novo trabalho no sistema.\n\n";
            $body   .= "Acesse o painel administrativo para corrigir e atribuir a nota.";
            
            $mailer = Factory::getMailer();
            $mailer->setSender($sender);
            $mailer->addRecipient($config->get('mailfrom')); // Envia para o E-mail do Admin Global
            $mailer->setSubject($subject);
            $mailer->setBody($body);
            $mailer->Send();
        }

        // ====================================================================
        // CENÁRIO 2: ATUALIZAÇÃO (Professor Avaliou -> Notificar Aluno)
        // ====================================================================
        else {
            // Verifica se houve alteração na nota ou no feedback
            // (Compara o valor atual $this->grade com o valor antigo $oldItem->grade)
            $gradeChanged    = ($this->grade != $oldItem->grade);
            $feedbackChanged = ($this->feedback != $oldItem->feedback);
            $statusChanged   = ($this->status != $oldItem->status);

            // Só manda e-mail se algo relevante mudou E se a nota não está vazia
            if (($gradeChanged || $feedbackChanged || $statusChanged) && (string)$this->grade !== '') {
                
                // Precisamos buscar o e-mail do aluno (temos apenas o user_id na tabela submissions)
                $db = Factory::getDbo();
                $query = $db->getQuery(true)
                    ->select('email, name')
                    ->from('#__users')
                    ->where('id = ' . (int) $this->user_id);
                $db->setQuery($query);
                $student = $db->loadObject();

                if ($student) {
                    $subject = 'Seu trabalho foi avaliado!';
                    
                    $body  = "Olá, " . $student->name . ".\n\n";
                    $body .= "Sua avaliação foi atualizada no sistema.\n";
                    $body .= "--------------------------------------------------\n";
                    $body .= "Nota: " . $this->grade . "\n";
                    
                    // Adiciona o status textual
                    if ($this->status == 1) {
                        $body .= "Situação: APROVADO\n";
                    } elseif ($this->status == 2) {
                        $body .= "Situação: REPROVADO (Verifique o feedback)\n";
                    }

                    if (!empty($this->feedback)) {
                        $body .= "Parecer do Professor: " . $this->feedback . "\n";
                    }
                    $body .= "--------------------------------------------------\n\n";
                    $body .= "Acesse a plataforma para ver mais detalhes ou reenviar se necessário.";

                    // Reinicia o mailer para garantir que não misture com o envio anterior
                    $mailer = Factory::getMailer();
                    $mailer->setSender($sender);
                    $mailer->addRecipient($student->email); // Envia para o Aluno
                    $mailer->setSubject($subject);
                    $mailer->setBody($body);
                    $mailer->Send();
                }
            }
        }
    }
}