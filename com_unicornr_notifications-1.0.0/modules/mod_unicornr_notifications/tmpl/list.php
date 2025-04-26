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
$elements = ModUnicornr_notificationsHelper::getList($params);

$tableField = explode(':', $params->get('field'));
$table_name = !empty($tableField[0]) ? $tableField[0] : '';
$field_name = !empty($tableField[1]) ? $tableField[1] : '';
?>

<?php if (!empty($elements)) : ?>
	<table class="table">
		<?php foreach ($elements as $element) : ?>
			<tr>
				<th><?php echo ModUnicornr_notificationsHelper::renderTranslatableHeader($table_name, $field_name); ?></th>
				<td><?php echo ModUnicornr_notificationsHelper::renderElement(
						$table_name, $params->get('field'), $element->{$field_name}
					); ?></td>
			</tr>
		<?php endforeach; ?>
	</table>
<?php endif;
