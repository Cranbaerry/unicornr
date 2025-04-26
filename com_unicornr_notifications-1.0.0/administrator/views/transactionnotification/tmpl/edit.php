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
use \Joomla\CMS\Language\Text;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('formbehavior.chosen', 'select');
HTMLHelper::_('behavior.keepalive');

// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'media/com_unicornr_notifications/css/form.css');

// Parse existing message field if it contains JSON
$messageValues = [];
$currentMessage = isset($this->item->message) ? $this->item->message : '';
if (!empty($currentMessage) && $jsonMessages = json_decode($currentMessage, true)) {
    $messageValues = $jsonMessages;
} elseif (!empty($currentMessage)) {
    $messageValues = [$currentMessage]; // Handle legacy single message format
}
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function () {
		// Initialize messages container with existing messages
		var messagesContainer = js('#messages-container');
		var originalMessageField = js('#jform_message');
		var messagesCounter = <?php echo !empty($messageValues) ? count($messageValues) : 1; ?>;
		
		// Hide the original message field
		originalMessageField.closest('.control-group').hide();
		
		// Initialize with existing messages or one empty message
		if (messagesContainer.find('.message-item').length === 0) {
			<?php if (!empty($messageValues)): ?>
			<?php foreach ($messageValues as $index => $message): ?>
			addMessageField('<?php echo addslashes($message); ?>');
			<?php endforeach; ?>
			<?php else: ?>
			addMessageField('');
			<?php endif; ?>
		}
		
		// Add message button
		js('#add-message-btn').on('click', function(e) {
			e.preventDefault();
			addMessageField('');
		});
		
		// Remove message button (using event delegation)
		messagesContainer.on('click', '.remove-message-btn', function(e) {
			e.preventDefault();
			if (messagesContainer.find('.message-item').length > 1) {
				js(this).closest('.message-item').remove();
				updateOriginalMessageField();
			}
		});
		
		// Update original field when any message field changes
		messagesContainer.on('change', '.message-input', function() {
			updateOriginalMessageField();
		});
		
		// Function to add a new message field
		function addMessageField(value) {
			var html = '<div class="message-item control-group">' +
				'<textarea class="message-input" rows="5" cols="50" required>' + value + '</textarea> ' +
				'<button class="btn btn-small btn-danger remove-message-btn"><i class="icon-minus"></i></button>' +
				'</div>';
				
			messagesContainer.append(html);
			messagesCounter++;
			updateOriginalMessageField();
		}
		
		// Function to update the original hidden field with JSON
		function updateOriginalMessageField() {
			var messages = [];
			var valid = true;
			
			js('.message-input').each(function() {
				var value = js(this).val().trim();
				if (value === '') {
					valid = false;
					js(this).addClass('invalid').attr('aria-invalid', 'true');
				} else {
					js(this).removeClass('invalid').removeAttr('aria-invalid');
					messages.push(value);
				}
			});
			
			if (valid && messages.length > 0) {
				originalMessageField.val(JSON.stringify(messages));
				return true;
			} else {
				return false;
			}
		}
	});

	Joomla.submitbutton = function (task) {
		if (task == 'transactionnotification.cancel') {
			Joomla.submitform(task, document.getElementById('transactionnotification-form'));
		}
		else {
			// Validate message fields before submitting
			var messagesValid = js('#messages-container .message-input').length > 0;
			js('#messages-container .message-input').each(function() {
				if (js(this).val().trim() === '') {
					messagesValid = false;
					js(this).addClass('invalid').attr('aria-invalid', 'true');
				}
			});
			
			if (!messagesValid) {
				alert('<?php echo $this->escape(Text::_('Message fields cannot be empty')); ?>');
				return false;
			}
			
			if (task != 'transactionnotification.cancel' && document.formvalidator.isValid(document.id('transactionnotification-form'))) {
				Joomla.submitform(task, document.getElementById('transactionnotification-form'));
			}
			else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_unicornr_notifications&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="transactionnotification-form" class="form-validate form-horizontal">

	
	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'transactionnotification')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'transactionnotification', JText::_('COM_UNICORNR_NOTIFICATIONS_TAB_TRANSACTIONNOTIFICATION', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_UNICORNR_NOTIFICATIONS_FIELDSET_TRANSACTIONNOTIFICATION'); ?></legend>
				<?php echo $this->form->renderField('days_before_reminder'); ?>
				
				<!-- Time field as a time selector -->
				<div class="control-group">
					<div class="control-label">
						<label id="jform_time-lbl" for="jform_time">
							<?php echo JText::_('COM_UNICORNR_NOTIFICATIONS_FORM_LBL_TRANSACTIONNOTIFICATION_TIME'); ?>
						</label>
					</div>
					<div class="controls">
						<?php
						$timeValue = isset($this->item->time) && !empty($this->item->time) ? $this->item->time : '09:00';
						?>
						<input type="time" name="jform[time]" id="jform_time" value="<?php echo $timeValue; ?>" class="input-medium" />
					</div>
				</div>
				
				<!-- Original message field will be hidden by JS -->
				<?php echo $this->form->renderField('message'); ?>
				
				<!-- Multiple messages interface -->
				<div class="control-group">
					<div class="control-label">
						<label id="jform_messages-lbl" for="jform_messages">
							<?php echo JText::_('COM_UNICORNR_NOTIFICATIONS_FORM_LBL_TRANSACTIONNOTIFICATION_MESSAGE'); ?>
						</label>
					</div>
					<div class="controls">
						<div id="messages-container"></div>
						<button id="add-message-btn" class="btn btn-small btn-success">
							<i class="icon-plus"></i> <?php echo JText::_('Add Message'); ?>
						</button>
					</div>
				</div>
				
			</fieldset>
		</div>
	</div>
	<?php echo JHtml::_('bootstrap.endTab'); ?>
	<input type="hidden" name="jform[id]" value="<?php echo $this->item->id; ?>" />
	<input type="hidden" name="jform[state]" value="<?php echo $this->item->state; ?>" />
	<input type="hidden" name="jform[ordering]" value="<?php echo $this->item->ordering; ?>" />
	<input type="hidden" name="jform[checked_out]" value="<?php echo $this->item->checked_out; ?>" />
	<input type="hidden" name="jform[checked_out_time]" value="<?php echo $this->item->checked_out_time; ?>" />
	<?php echo $this->form->renderField('created_by'); ?>
	<?php echo $this->form->renderField('modified_by'); ?>

	

	<?php $this->ignore_fieldsets = array('general', 'info', 'detail', 'jmetadata', 'item_associations', 'accesscontrol'); ?>
	<?php echo JLayoutHelper::render('joomla.edit.params', $this); ?>
	
	<?php echo JHtml::_('bootstrap.endTabSet'); ?>

	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>

</form>
