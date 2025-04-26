<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Unicornr_notifications
 * @author     dreamztech <support@dreamztech.com.my>
 * @copyright  2025 dreamztech
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Statusupdatenotification controller class.
 *
 * @since  1.6
 */
class Unicornr_notificationsControllerStatusupdatenotification extends \Joomla\CMS\MVC\Controller\FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'statusupdatenotifications';
		parent::__construct();
	}
}
