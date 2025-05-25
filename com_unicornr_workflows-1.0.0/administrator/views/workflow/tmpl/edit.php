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

use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;


HTMLHelper::addIncludePath(JPATH_COMPONENT . '/helpers/html');
HTMLHelper::_('behavior.tooltip');
HTMLHelper::_('behavior.formvalidation');
HTMLHelper::_('behavior.keepalive');
// Import CSS
$document = Factory::getDocument();
$document->addStyleSheet(Uri::root() . 'media/com_unicornr_workflows/css/form.css');
?>
<script type="text/javascript">
	js = jQuery.noConflict();
	js(document).ready(function() {

		js('input:hidden.type').each(function() {
			var name = js(this).attr('name');
			// Fix: Correct indexOf logic
			if (name.indexOf('typehidden') !== -1) {
				js('#jform_type option[value="' + js(this).val() + '"]').attr('selected', true);
			}
		});
		// Fix: Only trigger chosen update if #jform_type uses Chosen		
		if (js("#jform_type").hasClass("chzn-done")) {
			js("#jform_type").trigger("liszt:updated");
		}

		// Function to update interval reference text based on type selection
		function updateIntervalReferenceText() {
			var selectedType = js('#jform_type').val();
			var selectedText = js('#jform_type option:selected').text();
			var referenceTextElement = js('#interval-reference-text');
			
			// Check if selected type contains "Event Reminder"
			if (selectedText.indexOf('Event Reminder') !== -1) {
				referenceTextElement.text('event date (at 8 am)');
			} else {
				referenceTextElement.text('now');
			}
		}

		// Call function on page load to set initial state
		updateIntervalReferenceText();

		// Add event listener for type dropdown change
		js('#jform_type').on('change', function() {
			updateIntervalReferenceText();
		});
	});

	Joomla.submitbutton = function(task) {
		if (task == 'workflow.cancel') {
			Joomla.submitform(task, document.getElementById('workflow-form'));
		} else {
			// Remove this debug line:
			// console.log(document.formvalidator.isValid(document.id('workflow-form')));
			if (task != 'workflow.cancel' && document.formvalidator.isValid(document.id('workflow-form'))) {
				Joomla.submitform(task, document.getElementById('workflow-form'));
			} else {
				alert('<?php echo $this->escape(Text::_('JGLOBAL_VALIDATION_FORM_FAILED')); ?>');
			}
		}
	}
</script>

<form
	action="<?php echo JRoute::_('index.php?option=com_unicornr_workflows&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="workflow-form" class="form-validate form-horizontal">


	<?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'workflow')); ?>
	<?php echo JHtml::_('bootstrap.addTab', 'myTab', 'workflow', JText::_('COM_UNICORNR_WORKFLOWS_TAB_WORKFLOW', true)); ?>
	<div class="row-fluid">
		<div class="span10 form-horizontal">
			<fieldset class="adminform">
				<legend><?php echo JText::_('COM_UNICORNR_WORKFLOWS_FIELDSET_WORKFLOW'); ?></legend>
				<?php echo $this->form->renderField('name'); ?>
				<?php echo $this->form->renderField('type'); ?>
				<?php
				foreach ((array)$this->item->type as $value) {
					if (!is_array($value)) {
						echo '<input type="hidden" class="type" name="jform[typehidden][' . $value . ']" value="' . $value . '" />';
					}
				}
				?>
				<?php echo $this->form->renderField('updatetype'); ?>
				<?php echo $this->form->renderField('keyword'); ?>
				<?php echo $this->form->renderField('sendtype'); ?>
				<?php // Remove the default workflow field 
				?>
				<!-- Custom Workflow Field -->
				<div class="control-group">
					<div class="control-label">
						<label id="jform_workflow-lbl" for="jform_workflow">
							Workflow
						</label>
					</div>
					<div class="controls">
						<!-- Hidden textarea that will hold the JSON -->
						<textarea name="jform[workflow]" id="jform_workflow" class="form-control" style="display:none;"><?php echo htmlspecialchars($this->item->workflow, ENT_QUOTES, 'UTF-8'); ?></textarea>
						<!-- Container for dynamic workflow items -->
						<div id="workflow_items_container"></div>
						<!-- Button to add a new workflow item -->
						<button type="button" id="add_workflow_item" class="btn btn-secondary" style="margin-top:8px;"><?php echo JText::_('Add Workflow Item'); ?></button>
					</div>
				</div>
				<!-- Workflow Item Template (hidden) -->
				<div id="workflow-item-template" style="display:none;">
					<div class="workflow-item" style="margin-bottom: 10px; border: 1px solid #ccc; padding: 10px; border-radius:4px; background:#fafbfc;">
						<div class="control-group">
							<div class="control-label">
								<label><?php echo JText::_('Message'); ?>:</label>
							</div>
							<div class="controls">
								<textarea class="inputbox workflow-message" placeholder="<?php echo JText::_('Enter message'); ?>" style="min-height:60px;"></textarea>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<label><?php echo JText::_('Time Interval'); ?>:</label>
							</div>
							<div class="controls">
								<div class="input-append">
									<input type="number" class="input-mini workflow-interval" placeholder="<?php echo JText::_('Value'); ?>" min="1" style="width:80px;">
									<select class="inputbox workflow-unit" style="width:120px;">
										<option value="minutes"><?php echo JText::_('Minutes'); ?></option>
										<option value="days"><?php echo JText::_('Days'); ?></option>
										<option value="weeks"><?php echo JText::_('Weeks'); ?></option>
									</select>
								</div>								<br>
								<small class="form-text text-muted"><?php echo JText::_('Send message after the specified interval from '); ?><span id="interval-reference-text">now</span></small>
							</div>
						</div>
						<div class="control-group">
							<div class="control-label">
								<label><?php echo JText::_('Image'); ?>:</label>
							</div>
							<div class="controls">
								<input type="file" class="workflow-image" accept="image/*" style="max-width:300px;">
								<br>
								<img class="workflow-image-preview" src="" alt="" style="max-width:100%; height:auto; max-height:200px; display:none; margin-top:8px; border:1px solid #ccc; border-radius:4px; background:#fff;" />
								<span class="workflow-image-uploading" style="display:none;"><?php echo JText::_('Uploading...'); ?></span>
							</div>
						</div>
						<div class="control-group">
							<div class="controls">
								<button type="button" class="btn btn-danger remove_workflow_item"><?php echo JText::_('Remove'); ?></button>
							</div>
						</div>
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

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>

</form>
<script type="text/javascript">
	// --- Custom Workflow Field JS ---
	(function($) {
		function serializeWorkflowItems() {
			var items = [];
			$('#workflow_items_container .workflow-item').each(function() {
				var $item = $(this);
				var message = $item.find('.workflow-message').val();
				var interval = parseInt($item.find('.workflow-interval').val(), 10) || 0;
				var unit = $item.find('.workflow-unit').val();
				var image = $item.find('.workflow-image').data('uploaded-url') || '';
				items.push({
					message: message,
					time: interval,
					unit: unit,
					image: image
				});
			});
			$('#jform_workflow').val(JSON.stringify(items));
		}

		function uploadImage(file, $input, $preview, $uploading, callback) {
			var formData = new FormData();
			formData.append('file', file);
			formData.append('option', 'com_unicornr_workflows');
			formData.append('task', 'workflow.uploadImage');
			formData.append('<?php echo JSession::getFormToken(); ?>', '1');
			$uploading.show();
			$.ajax({
				// Change 'workflow' to 'workflowstype' if your controller is workflowstype.php,
				// or keep as 'workflow' if your controller is workflow.php
				url: 'index.php?option=com_unicornr_workflows&task=workflow.uploadImage',
				type: 'POST',
				data: formData,
				processData: false,
				contentType: false,
				dataType: 'json',
				success: function(resp) {
					$uploading.hide();
					// Fix: handle both resp.success and resp.data.success (Joomla JsonResponse wraps your array in "data")
					var url = '';
					if (resp && resp.data && resp.data.success && resp.data.url) {
						url = resp.data.url;
					} else if (resp && resp.success && resp.url) {
						url = resp.url;
					}
					if (url) {
						$input.data('uploaded-url', url);
						$preview.attr('src', url).show();
						if (typeof callback === 'function') callback(url);
					} else {
						alert((resp && resp.message) ? resp.message : 'Image upload failed');
						$input.val('');
						$input.data('uploaded-url', '');
						$preview.hide();
						if (typeof callback === 'function') callback('');
					}
					serializeWorkflowItems();
				},
				error: function() {
					$uploading.hide();
					alert('Image upload failed');
					$input.val('');
					$input.data('uploaded-url', '');
					$preview.hide();
					if (typeof callback === 'function') callback('');
					serializeWorkflowItems();
				}
			});
		}

		function addWorkflowItem(data) {
			var $tpl = $($('#workflow-item-template').html());
			$tpl.find('.workflow-message').attr('required', true);
			$tpl.find('.workflow-interval').attr('required', true);
			if (data) {
				$tpl.find('.workflow-message').val(data.message || '');
				// Fix: preserve 0 value for interval
				$tpl.find('.workflow-interval').val(
					typeof data.time !== 'undefined' ? data.time : ''
				);
				$tpl.find('.workflow-unit').val(data.unit || 'minutes');
				if (data.image) {
					$tpl.find('.workflow-image').data('uploaded-url', data.image);
					$tpl.find('.workflow-image-preview').attr('src', data.image).show();
				}
			}
			// Fix: Remove .chosen() or .formbehavior.chosen for workflow-unit selects
			// and trigger change event for serialization
			$tpl.find('.workflow-unit').on('change', function() {
				serializeWorkflowItems();
			});
			$tpl.find('.workflow-image').on('change', function() {
				var $input = $(this);
				var $preview = $tpl.find('.workflow-image-preview');
				var $uploading = $tpl.find('.workflow-image-uploading');
				var file = this.files && this.files[0] ? this.files[0] : null;
				if (file) {
					uploadImage(file, $input, $preview, $uploading);
				} else {
					$input.data('uploaded-url', '');
					$preview.hide();
					serializeWorkflowItems();
				}
			});
			$tpl.find('.remove_workflow_item').on('click', function() {
				$tpl.remove();
				serializeWorkflowItems();
			});
			$tpl.find('input,textarea').on('input change', function() {
				serializeWorkflowItems();
			});
			$('#workflow_items_container').append($tpl);
			serializeWorkflowItems();
		}

		$(function() {
			var existing = [];
			try {
				var raw = $('#jform_workflow').val();
				if (raw && raw.trim() !== '' && raw.trim() !== '[]') {
					existing = JSON.parse(raw);
				}
			} catch (e) {
				console.error('Error parsing workflow data:', e);
				existing = [];
			}
			if (Array.isArray(existing) && existing.length > 0) {
				for (var i = 0; i < existing.length; i++) addWorkflowItem(existing[i]);
			} else {
				addWorkflowItem();
			}
			$('#add_workflow_item').on('click', function() {
				addWorkflowItem();
			});
			$('#workflow-form').on('submit', function() {
				serializeWorkflowItems();
			});
		});
	})(jQuery);
	// --- End Custom Workflow Field JS ---
</script>