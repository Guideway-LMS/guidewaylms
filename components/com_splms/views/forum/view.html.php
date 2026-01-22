<?php
/**
 * @package     SP LMS
 * @subpackage  Components
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class SplmsViewForum extends HtmlView
{
	protected $items;
	protected $item;
	protected $answers;

	/**
	 * Exibe a view.
	 *
	 * @param   string  $tpl  O nome do arquivo de template.
	 *
	 * @return  void
	 */
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$layout = $app->input->get('layout', 'default');
		$model = $this->getModel();
		
		// Carrega CSS
		$doc = Factory::getDocument();
		$doc->addStyleSheet('components/com_splms/assets/css/forum.css');

		if ($layout === 'question' || $layout === 'edit')
		{
			// Detalhes da pergunta (ou edição)
			$id = $app->input->getInt('id');
			$this->item = $model->getQuestion($id);
			
			// Se for question, carrega answers. Se for edit, não precisa.
			if ($layout === 'question') {
				$this->answers = $model->getAnswers($id);
			}
			
			// Se não encontrar, erro 404
			if (empty($this->item))
			{
				throw new \Exception('Pergunta não encontrada', 404);
			}
		}
		else
		{
			// Lista de perguntas
			// Lista de perguntas e Filtros
			$courseId = $app->input->getInt('course_id', 0);
            
            if (empty($courseId) && $app->input->get('view') == 'course') {
                $courseId = $app->input->getInt('id', 0);
            }
            
            // Inputs
            $limit = 10;
            $limitStart = $app->input->getInt('limitstart', 0);
            $search = $app->input->getString('q', '');
            $filter = $app->input->getString('filter', '');

            // Dados
            $total = $model->getTotalQuestions($courseId, $search, $filter);
			$this->items = $model->getQuestions($courseId, $limit, $limitStart, $search, $filter);
			
			// Paginação
            $this->pagination = new \Joomla\CMS\Pagination\Pagination($total, $limitStart, $limit);

			// View vars
			$this->courseId = $courseId;
            $this->searchTerm = $search;
            $this->activeFilter = $filter;
		}

		parent::display($tpl);
	}
}
