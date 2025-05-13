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

jimport('joomla.application.component.controllerform');

/**
 * Workflow controller class.
 *
 * @since  1.6
 */
class Unicornr_workflowsControllerWorkflow extends \Joomla\CMS\MVC\Controller\FormController
{
	/**
	 * Constructor
	 *
	 * @throws Exception
	 */
	public function __construct()
	{
		$this->view_list = 'workflows';
		parent::__construct();
	}

	public function uploadImage()
	{
		// Check for request forgeries.
		if (!\JSession::checkToken('post')) {
			echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('JINVALID_TOKEN'), false);
			\Joomla\CMS\Factory::getApplication()->close();
		}

		$app = \Joomla\CMS\Factory::getApplication();
		$input = $app->input;
		$file = $input->files->get('file', null, 'raw');

		if (!$file || !isset($file['name']) || !$file['tmp_name']) {
			echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('No file uploaded'), false);
			$app->close();
		}

		// Only allow images
		$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
		if (!in_array($file['type'], $allowedTypes)) {
			echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('Invalid file type'), false);
			$app->close();
		}

		// Prepare destination
		$folder = 'images/unicornr_workflows';
		$absFolder = JPATH_ROOT . '/' . $folder;
		if (!\Joomla\CMS\Filesystem\Folder::exists($absFolder)) {
			\Joomla\CMS\Filesystem\Folder::create($absFolder);
		}

		// Sanitize and make unique filename
		$filename = \Joomla\CMS\Filesystem\File::makeSafe($file['name']);
		$dest = $absFolder . '/' . uniqid('wf_') . '_' . $filename;

		if (!\Joomla\CMS\Filesystem\File::upload($file['tmp_name'], $dest)) {
			echo new \Joomla\CMS\Response\JsonResponse(null, JText::_('Unable to save file'), false);
			$app->close();
		}

		// Build URL
		$url = \Joomla\CMS\Uri\Uri::root() . $folder . '/' . basename($dest);

		echo new \Joomla\CMS\Response\JsonResponse(['success' => true, 'url' => $url]);
		$app->close();
	}
}
