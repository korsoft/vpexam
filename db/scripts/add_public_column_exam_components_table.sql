ALTER TABLE exam_components ADD COLUMN `public` TINYINT(1) UNSIGNED NOT NULL DEFAULT 1 AFTER `sort`;
UPDATE `virtual_physical_secure`.`exam_components` SET `sort`='25' WHERE `id`='1';