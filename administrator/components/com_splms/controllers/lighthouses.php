<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_splms
 * @author      JoomShaper <support@joomshaper.com>
 * @copyright   Copyright (c) 2010 - 2021 JoomShaper
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * Lighthouses list controller class.
 *
 * @since  1.0.0
 */
class SplmsControllerLighthouses extends AdminController
{
	public function getModel($name = 'Lighthouse', $prefix = 'SplmsModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);

		return $model;
	}

	public function fix()
	{
		$app 		= Factory::getApplication('site');
		$input 		= $app->input;

		$html = [];

		if (class_exists('Lighthouse'))
		{
			$lighthouse = new Lighthouse;
			$lighthouse->fix();
			$html = $lighthouse->getHtml();
			$errors = $lighthouse->getErrors();
		}

		$response = [
			'html' => implode("\n", $html),
			'errors' => $errors
		];

		$app->setHeader('status', 200, true);
		$app->sendHeaders();
		echo new JsonResponse($response);
		$app->close();
	}
}

