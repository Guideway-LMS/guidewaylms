<?php
/**
 * Service provider of the component application
 *
 * @package GPTRANSLATE::administrator::components::com_gptranslate
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

if(!class_exists('JExtstore\Component\Gptranslate\Administrator\Dispatcher\Dispatcher') && function_exists('opcache_reset')){
	opcache_reset();
	Factory::getApplication()->redirect('index.php?option=com_gptranslate');
}

/**
 * The extension service provider.
 */
return new class implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param Container $container
	 *        	The DI container.
	 *        	
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	public function register(Container $container) {
		$container->registerServiceProvider ( new MVCFactory ( '\\JExtstore\\Component\\Gptranslate' ) );
		$container->registerServiceProvider ( new ComponentDispatcherFactory ( '\\JExtstore\\Component\\Gptranslate' ) );
		
		$container->set ( ComponentInterface::class, static function (Container $container) {
			$component = new MVCComponent ($container->get(ComponentDispatcherFactoryInterface::class));
			$component->setMVCFactory($container->get(MVCFactoryInterface::class));
			return $component;
		} );
	}
};