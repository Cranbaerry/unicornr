<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Unicornr_workflows
 * @author     dreamztech <support@dreamztech.com.my>
 * @copyright  2025 dreamztech
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\Utilities\ArrayHelper;
/**
 * Methods supporting a list of Unicornr_workflows records.
 *
 * @since  1.6
 */
class Unicornr_workflowsModelWorkflows extends \Joomla\CMS\MVC\Model\ListModel
{
    
        
/**
	* Constructor.
	*
	* @param   array  $config  An optional associative array of configuration settings.
	*
	* @see        JController
	* @since      1.6
	*/
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'state', 'a.state',
				'ordering', 'a.ordering',
				'created_by', 'a.created_by',
				'modified_by', 'a.modified_by',
				'name', 'a.name',
				'type', 'a.type',
				'keyword', 'a.keyword',
				'sendtype', 'a.sendtype',
				'workflow', 'a.workflow',
				'updatetype', 'a.updatetype',
			);
		}

		parent::__construct($config);
	}

    
        
    
        

        
	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   Elements order
	 * @param   string  $direction  Order direction
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	protected function populateState($ordering = null, $direction = null)
	{
        // List state information.
        parent::populateState("a.id", "ASC");

        $context = $this->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
        $this->setState('filter.search', $context);

        JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
        // Split context into component and optional section
		// Split context into component and optional section
		if (!empty($context))
		{
			$parts = FieldsHelper::extract($context);

			if ($parts)
			{
				$this->setState('filter.component', $parts[0]);
				$this->setState('filter.section', $parts[1]);
			}
		}
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return   string A store id.
	 *
	 * @since    1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');

                
                    return parent::getStoreId($id);
                
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return   JDatabaseQuery
	 *
	 * @since    1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select', 'DISTINCT a.*'
			)
		);
		$query->from('`#__unicornr_workflows` AS a');
                
		// Join over the users for the checked out user
		$query->select("uc.name AS uEditor");
		$query->join("LEFT", "#__users AS uc ON uc.id=a.checked_out");

		// Join over the user field 'created_by'
		$query->select('`created_by`.name AS `created_by`');
		$query->join('LEFT', '#__users AS `created_by` ON `created_by`.id = a.`created_by`');

		// Join over the user field 'modified_by'
		$query->select('`modified_by`.name AS `modified_by`');
		$query->join('LEFT', '#__users AS `modified_by` ON `modified_by`.id = a.`modified_by`');
		// Join over the foreign key 'type'
		$query->select('`#__unicornr_workflows_types_4190380`.`name` AS workflowstypes_fk_value_4190380');
		$query->join('LEFT', '#__unicornr_workflows_types AS #__unicornr_workflows_types_4190380 ON #__unicornr_workflows_types_4190380.`id` = a.`type`');
                

		// Filter by published state
		$published = $this->getState('filter.state');

		if (is_numeric($published))
		{
			$query->where('a.state = ' . (int) $published);
		}
		elseif (empty($published))
		{
			$query->where('(a.state IN (0, 1))');
		}

		// Filter by search in title
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where('a.id = ' . (int) substr($search, 3));
			}
			else
			{
				$search = $db->Quote('%' . $db->escape($search, true) . '%');
				$query->where('( a.keyword LIKE ' . $search . ' )');
			}
		}
                

		// Filtering type
		$filter_type = $this->state->get("filter.type");

		if ($filter_type !== null && !empty($filter_type))
		{
			$query->where("a.`type` = '".$db->escape($filter_type)."'");
		}

		// Filtering sendtype
		$filter_sendtype = $this->state->get("filter.sendtype");

		if ($filter_sendtype !== null && (is_numeric($filter_sendtype) || !empty($filter_sendtype)))
		{
			$query->where("a.`sendtype` = '".$db->escape($filter_sendtype)."'");
		}
		// Add the list ordering clause.
		$orderCol  = $this->state->get('list.ordering', "a.id");
		$orderDirn = $this->state->get('list.direction', "ASC");

		if ($orderCol && $orderDirn)
		{
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}

		//XXX_CUSTOM_ORDER_FOR_NESTED

		return $query;
	}

	/**
	 * Get an array of data items
	 *
	 * @return mixed Array of data items on success, false on failure.
	 */
	public function getItems()
	{
		$items = parent::getItems();
                
		foreach ($items as $oneItem)
		{

			if (isset($oneItem->type))
			{
				$values    = explode(',', $oneItem->type);
				$textValue = array();

				foreach ($values as $value)
				{
					$db    = JFactory::getDbo();
					$query = $db->getQuery(true);
					$query
						->select('`#__unicornr_workflows_types_4190380`.`name`')
						->from($db->quoteName('#__unicornr_workflows_types', '#__unicornr_workflows_types_4190380'))
						->where($db->quoteName('#__unicornr_workflows_types_4190380.id') . ' = '. $db->quote($db->escape($value)));

					$db->setQuery($query);
					$results = $db->loadObject();

					if ($results)
					{
						$textValue[] = $results->name;
					}
				}

				$oneItem->type = !empty($textValue) ? implode(', ', $textValue) : $oneItem->type;
			}
					$oneItem->sendtype = !empty($oneItem->sendtype) ? Text::_('COM_UNICORNR_WORKFLOWS_WORKFLOWS_SENDTYPE_OPTION_' . preg_replace('/[^A-Za-z0-9\_-]/', '',strtoupper(str_replace(' ', '_',$oneItem->sendtype)))) : '';

			if (isset($oneItem->updatetype))
			{
				$values    = explode(',', $oneItem->updatetype);
				$textValue = array();

				foreach ($values as $value)
				{
					if (!empty($value))
					{
						$db = JFactory::getDbo();
						$query = "SELECT *
  FROM #__unicornr_transaction_custom
 WHERE #__unicornr_transaction_custom
.id = $value AND fieldtype = 'status';";
						$db->setQuery($query);
						$results = $db->loadObject();

						if ($results)
						{
							$textValue[] = $results->fielddata;
						}
					}
				}

				$oneItem->updatetype = !empty($textValue) ? implode(', ', $textValue) : $oneItem->updatetype;
			}
		}

		return $items;
	}
}
