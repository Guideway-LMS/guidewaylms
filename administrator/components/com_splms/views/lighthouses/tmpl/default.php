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
use Joomla\CMS\Uri\Uri;

$doc = Factory::getDocument();
$input = Factory::getApplication()->input;

$html = [];
$errors = [];

if (class_exists('Lighthouse'))
{
	$lighthouse = new Lighthouse;
	$lighthouse->run();

	$html = $lighthouse->getHtml();
	$errors = $lighthouse->getErrors();
}

$data = [
	'base' => Uri::root() . 'administrator/index.php',
	'component' => $input->get('option'),
	'btnStatus' => empty($html) && empty($errors) ? 'disabled' : 'enabled'
];

$doc->addScriptOptions('config', $data);

?>


<div>
	<?php if (SplmsHelper::getJoomlaVersion() < 4 && !empty($this->sidebar)) { ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>

	<div id="j-main-container" class="span10" >
		<?php } else { ?>
			<div id="j-main-container">
			</div>
		<?php } ?>

		<div class='lighthouse-container'>
			<div class='lighthouse-wrapper'>

				<?php if (!empty($errors)): ?>
					<div class='notification is-danger mt-5'>
						<button class='delete'></button>
						Some problems could not be fixed automatically. Click <strong>Try Again</strong> button. If it doesn't solve the problem then you have to fix them manually or contact with the service providers.
					</div>
				<?php endif ?>

				<?php if (!empty($html)): ?>
					<?php echo implode("\n", $html); ?>
				<?php else: ?>
					<span class="lighthouse-msg">Your Database is perfect, Nothing to fix</span>
				<?php endif ?>

			</div>
		</div>
	</div>
</div>
	
