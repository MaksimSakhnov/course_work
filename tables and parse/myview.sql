DROP VIEW IF EXISTS `ssu_abit_spisok_with_specs`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `ssu_abit_spisok_with_specs` AS SELECT
	`ssu_abit_pers`.`id_pers`,
   GROUP_CONCAT(  
     `ssu_abit_spisok`.`id_grp` ORDER BY `ssu_abit_spisok`.`id_grp` SEPARATOR ';'
   ) AS `all_groups`
FROM
  `ssu_abit_pers`
  INNER JOIN `ssu_abit_spisok` ON (`ssu_abit_pers`.`id_pers` = `ssu_abit_spisok`.`id_pers`)
GROUP BY
  `ssu_abit_pers`.`id_pers`
ORDER BY
  `ssu_abit_pers`.`id_pers`;
