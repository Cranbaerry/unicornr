<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Unicornr_workflows
 * @author     dreamztech <support@dreamztech.com.my>
 * @copyright  2025 dreamztech
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access
defined('_JEXEC') or die;

use \Joomla\Utilities\ArrayHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Access\Access;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Table\Table as Table;

/**
 * workflow Table class
 *
 * @since  1.6
 */
class Unicornr_workflowsTableworkflow extends Table
{
	/**
	 * Check if a field is unique
	 *
	 * @param   string  $field  Name of the field
	 *
	 * @return bool True if unique
	 */
	private function isUnique ($field)
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($db->quoteName($field))
			->from($db->quoteName($this->_tbl))
			->where($db->quoteName($field) . ' = ' . $db->quote($this->$field))
			->where($db->quoteName('id') . ' <> ' . (int) $this->{$this->_tbl_key});

		$db->setQuery($query);
		$db->execute();

		return ($db->getNumRows() == 0) ? true : false;
	}

	/**
	 * Constructor
	 *
	 * @param   JDatabase  &$db  A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__unicornr_workflows', 'id', $db);
		
		
		$this->setColumnAlias('published', 'state');
		
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param   array  $array   Named array
	 * @param   mixed  $ignore  Optional array or list of parameters to ignore
	 *
	 * @return  null|string  null is operation was satisfactory, otherwise returns an error
	 *
	 * @see     JTable:bind
	 * @since   1.5
     * @throws Exception
	 */
	public function bind($array, $ignore = '')
	{
	    $date = Factory::getDate();
		$task = Factory::getApplication()->input->get('task');
	    
		$input = JFactory::getApplication()->input;
		$task = $input->getString('task', '');

		if ($array['id'] == 0 && empty($array['created_by']))
		{
			$array['created_by'] = JFactory::getUser()->id;
		}

		if ($array['id'] == 0 && empty($array['modified_by']))
		{
			$array['modified_by'] = JFactory::getUser()->id;
		}

		if ($task == 'apply' || $task == 'save')
		{
			$array['modified_by'] = JFactory::getUser()->id;
		}

		// Support for multiple or not foreign key field: type
			if(!empty($array['type']))
			{
				if(is_array($array['type'])){
					$array['type'] = implode(',',$array['type']);
				}
				else if(strrpos($array['type'], ',') != false){
					$array['type'] = explode(',',$array['type']);
				}
			}
			else {
				$array['type'] = 0;
			}

		// Support for multiple field: sendtype
		if (isset($array['sendtype']))
		{
			if (is_array($array['sendtype']))
			{
				$array['sendtype'] = implode(',',$array['sendtype']);
			}
			elseif (strpos($array['sendtype'], ',') != false)
			{
				$array['sendtype'] = explode(',',$array['sendtype']);
			}
			elseif (strlen($array['sendtype']) == 0)
			{
				$array['sendtype'] = '';
			}
		}
		else
		{
			$array['sendtype'] = '';
		}

		// Support for multiple field: updatetype
		if (isset($array['updatetype']))
		{
			if (is_array($array['updatetype']))
			{
				$array['updatetype'] = implode(',',$array['updatetype']);
			}
			elseif (strpos($array['updatetype'], ',') != false)
			{
				$array['updatetype'] = explode(',',$array['updatetype']);
			}
			elseif (strlen($array['updatetype']) == 0)
			{
				$array['updatetype'] = '';
			}
		}
		else
		{
			$array['updatetype'] = '';
		}

		if (isset($array['params']) && is_array($array['params']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['params']);
			$array['params'] = (string) $registry;
		}

		if (isset($array['metadata']) && is_array($array['metadata']))
		{
			$registry = new JRegistry;
			$registry->loadArray($array['metadata']);
			$array['metadata'] = (string) $registry;
		}

		if (!Factory::getUser()->authorise('core.admin', 'com_unicornr_workflows.workflow.' . $array['id']))
		{
			$actions         = Access::getActionsFromFile(
				JPATH_ADMINISTRATOR . '/components/com_unicornr_workflows/access.xml',
				"/access/section[@name='workflow']/"
			);
			$default_actions = Access::getAssetRules('com_unicornr_workflows.workflow.' . $array['id'])->getData();
			$array_jaccess   = array();

			foreach ($actions as $action)
			{
                if (key_exists($action->name, $default_actions))
                {
                    $array_jaccess[$action->name] = $default_actions[$action->name];
                }
			}

			$array['rules'] = $this->JAccessRulestoArray($array_jaccess);
		}

		// Bind the rules for ACL where supported.
		if (isset($array['rules']) && is_array($array['rules']))
		{
			$this->setRules($array['rules']);
		}

		return parent::bind($array, $ignore);
	}

	/**
	 * This function convert an array of JAccessRule objects into an rules array.
	 *
	 * @param   array  $jaccessrules  An array of JAccessRule objects.
	 *
	 * @return  array
	 */
	private function JAccessRulestoArray($jaccessrules)
	{
		$rules = array();

		foreach ($jaccessrules as $action => $jaccess)
		{
			$actions = array();

			if ($jaccess)
			{
				foreach ($jaccess->getData() as $group => $allow)
				{
					$actions[$group] = ((bool)$allow);
				}
			}

			$rules[$action] = $actions;
		}

		return $rules;
	}

	/**
	 * Overloaded check function
	 *
	 * @return bool
	 */
	public function check()
	{
		// If there is an ordering column and this is a new row then get the next ordering value
		if (property_exists($this, 'ordering') && $this->id == 0)
		{
			$this->ordering = self::getNextOrder();
		}
		
		// Check if name is unique
		if (!$this->isUnique('name'))
		{
			throw new \Exception(Text::sprintf('COM_UNICORNR_WORKFLOWS_WORKFLOW_NAME_UNIQUE_ERROR','name',$this->name));
		}
		

		return parent::check();
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 *
	 * @return string The asset name
	 *
	 * @see Table::_getAssetName
	 */
	protected function _getAssetName()
	{
		$k = $this->_tbl_key;

		return 'com_unicornr_workflows.workflow.' . (int) $this->$k;
	}

	/**
	 * Returns the parent asset's id. If you have a tree structure, retrieve the parent's id using the external key field
	 *
	 * @param   JTable   $table  Table name
	 * @param   integer  $id     Id
	 *
	 * @see Table::_getAssetParentId
	 *
	 * @return mixed The id on success, false on failure.
	 */
	protected function _getAssetParentId(JTable $table = null, $id = null)
	{
		// We will retrieve the parent-asset from the Asset-table
		$assetParent = Table::getInstance('Asset');

		// Default: if no asset-parent can be found we take the global asset
		$assetParentId = $assetParent->getRootId();

		// The item has the component as asset-parent
		$assetParent->loadByName('com_unicornr_workflows');

		// Return the found asset-parent-id
		if ($assetParent->id)
		{
			$assetParentId = $assetParent->id;
		}

		return $assetParentId;
	}

	
    /**
     * Delete a record by id
     *
     * @param   mixed  $pk  Primary key value to delete. Optional
     *
     * @return bool
     */
    public function delete($pk = null)
    {
        $this->load($pk);
        $result = parent::delete($pk);
        
        return $result;
    }

	

	
}
