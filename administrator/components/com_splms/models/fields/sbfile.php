<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Form Field class for the Joomla Platform.
 * Provides an input field for files
 *
 * @link   http://www.w3.org/TR/html-markup/input.file.html#input.file
 * @since  11.1
 */

class JFormFieldSbfile extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'Sbfile';
	/**
	 * The accepted file type list.
	 *
	 * @var    mixed
	 * @since  3.2
	 */

	protected $accept;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'accept':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'accept':
				$this->$accept = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->accept = (string) $this->element['accept'];
		}

		return $return;
	}

	/**
	 * Method to get the field input markup for the file field.
	 * Field attributes allow specification of a maximum file size and a string
	 * of accepted file extensions.
	 *
	 * @return  string  The field input markup.
	 *
	 * @note    The field does not include an upload mechanism.
	 * @see     JFormFieldMedia
	 * @since   11.1
	 */
	protected function getInput(){
		// Initialize some field attributes.
		$accept    = !empty($this->accept) ? ' accept="' . $this->accept . '"' : '';
		$size      = !empty($this->size) ? ' size="' . $this->size . '"' : '';
		$class     = !empty($this->class) ? ' class="' . $this->class . '"' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$required  = $this->required ? ' required aria-required="true"' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';
		$multiple  = $this->multiple ? ' multiple' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->onchange ? ' onchange="' . $this->onchange . '"' : '';

		// Including fallback code for HTML5 non supported browsers.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', [], true);
		$app = Factory::getApplication();

		$file_preview = '';
		// Get the input.
		$input = Factory::getApplication()->input;
		$Itemid = $input->get('id',0,'INT');

		if ($app->isClient('administrator')) {
			// avatar
			$avatar='';
			$jinput = Factory::getApplication()->input;
	
			if ( $jinput->get('id') !='' && self::getUserProfileById($jinput->get('id')) !='' && self::getUserProfileById($jinput->get('id')) ) {
				$avatar = json_decode(self::getUserProfileById($jinput->get('id'))->profile_value)->avatar;
			}
			

			if ( isset($avatar) && $avatar ) {
				$file_preview = '<div class="lms-profile-image-preview">';
				$file_preview .= '<h4>user profile picture</h4>';
				$file_preview .= '<img src="'.Uri::root(true). $avatar . '" width="150" />';
				$file_preview .= '</div>';
			}

	
			// has attachment
			if ($this->value && $this->fieldname =='attachment') {
				$file_preview  = '<div class="splms-attached-file">';
				$file_preview .= '<a id="splms-attachment-file" href="' . Uri::root(true). '/'. $this->value . '">' . $this->value . '</a> ';
				if (isset($this->value) && $this->value) {
					$file_preview .= ' <a id="splms-remove-attachment" class="splms-remove-attachment" href="#" data-file="' . JPATH_ROOT . '/' . $this->value . '" data-id="'. $Itemid .'">('. Text::_('COM_SPLMS_REMOVE_ATTACHMENT') . ')</a>';
				}

				$file_preview .= '</div>';
			}
		}

		//$jinput = Factory::getApplication()->input;

		//$userid = $jinput;
		if ($app->isClient('site')) {
			$jinput = Factory::getApplication()->input;
			$user = Factory::getUser();

			if (self::getUserProfileById($user->get('id')) !='' && self::getUserProfileById($user->get('id'))) {
				$avatar = json_decode(self::getUserProfileById($user->get('id'))->profile_value)->avatar;
			}

			if ( isset($avatar) && $avatar ) {
				$file_preview = '<div class="lms-profile-image-preview">';
				$file_preview .= '<h4>Your profile picture</h4>';
				$file_preview .= '<img src="'.Uri::root(true). $avatar . '" width="150" />';
				$file_preview .= '</div>';
			}


		}

		return '<input type="file" name="' . $this->name . '" id="' . $this->id . '" ' . $accept
			. $disabled . $class . $size . $onchange . $required . $autofocus . $multiple . ' />' . $file_preview;
	}

	protected static function getUserProfileById( $userId = NULL ){
		// Get a database object.
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('profile_key'),
					$db->quoteName('profile_value'),
				]
			)
			->from($db->quoteName('#__user_profiles'))
			->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
			->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profilelms.%'))
			->order($db->quoteName('ordering'));

		$db->setQuery($query);
		$results = $db->loadObject();

		return $results;
	}

}
