<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Unicornr_workflows
 * @author     dreamztech <support@dreamztech.com.my>
 * @copyright  2025 dreamztech
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;
use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Language\Text;

jimport('joomla.form.formfield');

/**
 * Supports a value from an external table
 *
 * @since  1.6
 */
class JFormFieldWorkflowupdatetype extends \Joomla\CMS\Form\FormField
{
	/**
	 * The form field custom type.
	 *
	 * @var        string
	 * @since    1.6
	 */
	protected $type = 'workflowupdatetype';

	/**
	 * Method to get the field input markup.
	 *
	 * @return   string  The field input markup.
	 *
	 * @since    1.6
	 */
	protected function getInput()
	{
		$value = $this->value;

		$db    = Factory::getDbo();

		$db->setQuery("SELECT *
  FROM #__unicornr_transaction_custom
 WHERE fieldtype = 'status';");

		$results = $db->loadObjectList();

		$input_options = 'class="' . $this->getAttribute('class') . '"';

		$options = array();

		
		// Iterate through all the results
		foreach ($results as $result)
		{
			$options[] = HTMLHelper::_('select.option', $result->id, Text::_($result->fielddata));
		}


		// If the value is a string -> Only one result
		if (is_string($value))
		{
			$value = array($value);
		}
		elseif (is_object($value))
		{
			// If the value is an object, let's get its properties.
			$value = get_object_vars($value);
		}

		// If the select is multiple
		if ($this->multiple)
		{
			$input_options .= 'multiple="multiple"';
		}
		else
		{
			array_unshift($options, HTMLHelper::_('select.option', '', ''));
		}

		$html = HTMLHelper::_('select.genericlist', $options, $this->name, $input_options, 'value', 'text', $value, $this->id);

		return $html;
	}

	/**
	 * Wrapper method for getting attributes from the form element
	 *
	 * @param   string  $attr_name  Attribute name
	 * @param   mixed   $default    Optional value to return if attribute not found
	 *
	 * @return mixed The value of the attribute if it exists, null otherwise
	 */
	public function getAttribute($attr_name, $default = null)
	{
		if (!empty($this->element[$attr_name]))
		{
			return $this->element[$attr_name];
		}
		else
		{
			return $default;
		}
	}
}
