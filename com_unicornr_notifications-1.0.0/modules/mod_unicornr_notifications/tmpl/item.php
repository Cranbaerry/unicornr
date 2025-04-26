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
$element = ModUnicornr_notificationsHelper::getItem($params);
?>

<?php if (!empty($element)) : ?>
	<div>
		<?php $fields = get_object_vars($element); ?>
		<?php foreach ($fields as $field_name => $field_value) : ?>
			<?php if (ModUnicornr_notificationsHelper::shouldAppear($field_name)): ?>
				<div class="row">
					<div class="span4">
						<strong><?php echo ModUnicornr_notificationsHelper::renderTranslatableHeader($params->get('item_table'), $field_name); ?></strong>
					</div>
					<div
						class="span8"><?php echo ModUnicornr_notificationsHelper::renderElement($params->get('item_table'), $field_name, $field_value); ?></div>
				</div>
			<?php endif; ?>
		<?php endforeach; ?>
	</div>
<?php endif;
