INSERT INTO physician_prefs( id, normal, phone_home, phone_work, phone_cell, exam_components, max_steth_record_time) 
SELECT phy.physician_id, '', '', '', '', '["htt","mm","aas","aps","ats","ams","ala","alm","arm","rjva","ljva","rleek","lleek","mv1"]', 0
FROM physicians phy 
LEFT JOIN physician_prefs php ON php.id = phy.physician_id 
WHERE php.id IS NULL;
