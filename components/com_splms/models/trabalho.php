<?php
/**
 * Model para gerenciar envio de trabalhos
 * GUIDEWAY CUSTOM - Joshua - Grupo 1
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class SplmsModelTrabalho extends BaseDatabaseModel
{
    /**
     * Busca o ID do professor da lição
     */
    public function getTeacherId($lesson_id)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('created_by')
            ->from('#__splms_lessons')
            ->where('id = ' . (int)$lesson_id);
        
        $db->setQuery($query);
        return (int)$db->loadResult();
    }
    
    /**
     * Salva trabalho no banco
     */
    public function salvarTrabalho($dados)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        
        $columns = [
            'user_id', 
            'teacher_id',
            'lesson_id', 
            'course_id', 
            'file_path', 
            'original_filename', 
            'status', 
            'submitted_at'
        ];
        
        $values = [
            (int)$dados['user_id'],
            (int)$dados['teacher_id'],
            (int)$dados['lesson_id'],
            (int)$dados['course_id'],
            $db->quote($dados['file_path']),
            $db->quote($dados['original_filename']),
            0,
            $db->quote(Factory::getDate()->toSql())
        ];
        
        $query->insert($db->quoteName('#__splms_submissions'))
              ->columns($db->quoteName($columns))
              ->values(implode(',', $values));
        
        $db->setQuery($query);
        return $db->execute();
    }
    
    /**
     * Busca submissão existente do aluno
     */
    public function getSubmissao($user_id, $lesson_id)
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from('#__splms_submissions')
            ->where('user_id = ' . (int)$user_id)
            ->where('lesson_id = ' . (int)$lesson_id);
        
        $db->setQuery($query);
        return $db->loadObject();
    }
}
