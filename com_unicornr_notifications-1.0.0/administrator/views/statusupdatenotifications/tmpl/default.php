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


use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;

HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/');
HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
HTMLHelper::_('formbehavior.chosen', 'select');

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'administrator/components/com_unicornr_notifications/assets/css/unicornr_notifications.css');
$document->addStyleSheet(Uri::root() . 'media/com_unicornr_notifications/css/list.css');

$user      = Factory::getUser();
$userId    = $user->get('id');
$listOrder = $this->state->get('list.ordering');
$listDirn  = $this->state->get('list.direction');
$canOrder  = $user->authorise('core.edit.state', 'com_unicornr_notifications');

$saveOrder = $listOrder == 'a.ordering';

if (!empty($saveOrder))
{
	$saveOrderingUrl = 'index.php?option=com_unicornr_notifications&task=statusupdatenotifications.saveOrderAjax&tmpl=component';
    HTMLHelper::_('sortablelist.sortable', 'statusupdatenotificationList', 'adminForm', strtolower($listDirn), $saveOrderingUrl);
}

$sortFields = $this->getSortFields();

// Function to display messages nicely
function formatMessages($jsonMessages) {
    $messages = json_decode($jsonMessages, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($messages)) {
        // If successfully decoded as JSON array
        if (count($messages) === 1) {
            // If only one message, display it directly
            return htmlspecialchars($messages[0]);
        } else {
            // If multiple messages, display count and first message with ellipsis
            $firstMessage = isset($messages[0]) ? htmlspecialchars(substr($messages[0], 0, 50)) : '';
            $count = count($messages);
            return "<span title='$count messages'>" . $firstMessage . 
                  (strlen($messages[0]) > 50 ? '...' : '') . 
                  " <span class='badge badge-info'>+$count</span></span>";
        }
    }
    // If not valid JSON or not an array, return as is
    return htmlspecialchars($jsonMessages);
}
?>

<form action="<?php echo Route::_('index.php?option=com_unicornr_notifications&view=statusupdatenotifications'); ?>" method="post"
      name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)): ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
		<?php else : ?>
		<div id="j-main-container">
			<?php endif; ?>

			<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

			<div class="clearfix"></div>
			<table class="table table-striped" id="statusupdatenotificationList">
				<thead>
				<tr>
					<th width="1%" >
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)"/>
					</th>
					
					<?php if (isset($this->items[0]->ordering)): ?>
					<th scope="col" class="w-1 text-center d-none d-md-table-cell">

					<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>

					</th>
					<?php endif; ?>

					
					<th width="1%" class="nowrap center">
						<?php echo JHtml::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
					</th>
					
					<th class='nowrap'>
						<?php echo JHtml::_('searchtools.sort',  'COM_UNICORNR_NOTIFICATIONS_STATUSUPDATENOTIFICATIONS_STATUS_TYPE', 'a.status_type', $listDirn, $listOrder); ?>
					</th>
					<th class='nowrap'>
						<?php echo JHtml::_('searchtools.sort',  'COM_UNICORNR_NOTIFICATIONS_STATUSUPDATENOTIFICATIONS_MESSAGE', 'a.message', $listDirn, $listOrder); ?>
					</th>
					
					<th width="1%" class="nowrap hidden-phone">
						<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
					</th>
				</tr>
				</thead>
				<tfoot>
				<tr>
					<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
						<?php echo $this->pagination->getListFooter(); ?>
					</td>
				</tr>
				</tfoot>
				<tbody>
				<?php foreach ($this->items as $i => $item) :
					$ordering   = ($listOrder == 'a.ordering');
					$canCreate  = $user->authorise('core.create', 'com_unicornr_notifications');
					$canEdit    = $user->authorise('core.edit', 'com_unicornr_notifications');
					$canCheckin = $user->authorise('core.manage', 'com_unicornr_notifications');
					$canChange  = $user->authorise('core.edit.state', 'com_unicornr_notifications');
					?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
						</td>
						
							<?php if (isset($this->items[0]->ordering)) : ?>

							<td class="text-center d-none d-md-table-cell">

							<?php

							$iconClass = '';

							if (!$canChange)

							{
								$iconClass = ' inactive';

							}
							elseif (!$saveOrder)

							{
								$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');

							}							?>							<span class="sortable-handler<?php echo $iconClass ?>">							<span class="icon-menu"></span>							</span>							<?php if ($canChange && $saveOrder) : ?>							<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">								<?php endif; ?>
							</td>							<?php endif; ?>
						
							<td >
							<div class="btn-group">
								<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'statusupdatenotifications.', $canChange, 'cb'); ?>
								<?php if ($canChange) : ?>
									<?php  HTMLHelper::_('actionsdropdown.' . ((int) $item->state === 2 ? 'un' : '') . 'archive', 'cb' . $i, 'statusupdatenotifications'); ?>
									<?php HTMLHelper::_('actionsdropdown.' . ((int) $item->state === -2 ? 'un' : '') . 'trash', 'cb' . $i, 'statusupdatenotifications'); ?>
									<?php echo HTMLHelper::_('actionsdropdown.render', $this->escape($item->state)); ?>
								<?php endif; ?>
							</div>
							</td>
						
						<td class="">
							<?php echo $item->status_type; ?>
						</td>
						<td class="">
							<?php if (isset($item->checked_out) && $item->checked_out && ($canEdit || $canChange)) : ?>
								<?php echo JHtml::_('jgrid.checkedout', $i, $item->uEditor, $item->checked_out_time, 'statusupdatenotifications.', $canCheckin); ?>
							<?php endif; ?>
							<?php if ($canEdit) : ?>
								<a href="<?php echo JRoute::_('index.php?option=com_unicornr_notifications&task=statusupdatenotification.edit&id='.(int) $item->id); ?>">
									<?php echo formatMessages($item->message); ?>
								</a>
							<?php else : ?>
								<?php echo formatMessages($item->message); ?>
							<?php endif; ?>
						</td>
						
							<td class="hidden-phone">
							<?php echo $item->id; ?>

							</td>

					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="boxchecked" value="0"/>
            <input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>"/>
			<?php echo HTMLHelper::_('form.token'); ?>
		</div>
</form>
<script>
    window.toggleField = function (id, task, field) {

        var f = document.adminForm, i = 0, cbx, cb = f[ id ];

        if (!cb) return false;

        while (true) {
            cbx = f[ 'cb' + i ];

            if (!cbx) break;

            cbx.checked = false;
            i++;
        }

        var inputField   = document.createElement('input');

        inputField.type  = 'hidden';
        inputField.name  = 'field';
        inputField.value = field;
        f.appendChild(inputField);

        cb.checked = true;
        f.boxchecked.value = 1;
        Joomla.submitform(task);

        return false;
    };
</script>