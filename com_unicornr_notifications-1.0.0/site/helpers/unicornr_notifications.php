<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Unicornr_notifications
 * @author     dreamztech <support@dreamztech.com.my>
 * @copyright  2025 dreamztech
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('Unicornr_notificationsHelper', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_unicornr_notifications' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'unicornr_notifications.php');

use \Joomla\CMS\Factory;
use \Joomla\CMS\MVC\Model\BaseDatabaseModel;

/**
 * Class Unicornr_notificationsFrontendHelper
 *
 * @since  1.6
 */
class Unicornr_notificationsHelpersUnicornr_notifications
{
	/**
	 * Get an instance of the named model
	 *
	 * @param   string  $name  Model name
	 *
	 * @return null|object
	 */
	public static function getModel($name)
	{
		$model = null;

		// If the file exists, let's
		if (file_exists(JPATH_SITE . '/components/com_unicornr_notifications/models/' . strtolower($name) . '.php'))
		{
			require_once JPATH_SITE . '/components/com_unicornr_notifications/models/' . strtolower($name) . '.php';
			$model = BaseDatabaseModel::getInstance($name, 'Unicornr_notificationsModel');
		}

		return $model;
	}

	/**
	 * Gets the files attached to an item
	 *
	 * @param   int     $pk     The item's id
	 *
	 * @param   string  $table  The table's name
	 *
	 * @param   string  $field  The field's name
	 *
	 * @return  array  The files
	 */
	public static function getFiles($pk, $table, $field)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query
			->select($field)
			->from($table)
			->where('id = ' . (int) $pk);

		$db->setQuery($query);

		return explode(',', $db->loadResult());
	}

    /**
     * Gets the edit permission for an user
     *
     * @param   mixed  $item  The item
     *
     * @return  bool
     */
    public static function canUserEdit($item)
    {
        $permission = false;
        $user       = Factory::getUser();

        if ($user->authorise('core.edit', 'com_unicornr_notifications') || (isset($item->created_by) && $user->authorise('core.edit.own', 'com_unicornr_notifications') && $item->created_by == $user->id))
        {
            $permission = true;
        }

        return $permission;
    }
}
