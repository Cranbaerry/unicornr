DROP TABLE IF EXISTS `#__unicornr_workflows`;
DROP TABLE IF EXISTS `#__unicornr_workflows_types`;

DELETE FROM `#__content_types` WHERE (type_alias LIKE 'com_unicornr_workflows.%');