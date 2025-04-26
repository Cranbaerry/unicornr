DROP TABLE IF EXISTS `#__unicornr_notifications_transactions`;
DROP TABLE IF EXISTS `#__unicornr_notifications_transactions_updates`;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_unicornr_notifications.%');