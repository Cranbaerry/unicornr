<?php

/**
 * @version     CVS: 1.0.0
 * @package     com_unicornr_notifications
 * @subpackage  mod_unicornr_notifications
 * @author      dreamztech <support@dreamztech.com.my>
 * @copyright   2025 dreamztech
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Helper\ModuleHelper;

// Include the syndicate functions only once
JLoader::register('ModUnicornr_notificationsHelper', dirname(__FILE__) . '/helper.php');

$doc = Factory::getDocument();

/* */
$doc->addStyleSheet(URI::base() . 'media/mod_unicornr_notifications/css/style.css');

/* */
$doc->addScript(URI::base() . 'media/mod_unicornr_notifications/js/script.js');

require ModuleHelper::getLayoutPath('mod_unicornr_notifications', $params->get('content_type', 'blank'));
