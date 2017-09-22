ALTER TABLE exam_components ADD sort TINYINT(3) UNSIGNED NOT NULL DEFAULT 99;
UPDATE exam_components SET sort = CASE WHEN id=1 THEN 1 CASE WHEN id=2 THEN 2 WHEN id=13 THEN 3 WHEN id=14 THEN 4 WHEN id=15 THEN 5 WHEN id=16 THEN 6 WHEN id=19 THEN 7 WHEN id=20 THEN 8 WHEN id=21 THEN 9 WHEN id=22 THEN 10 WHEN id=23 THEN 11 WHEN id=24 THEN 12 WHEN id=3 THEN 13 WHEN id=4 THEN 14 WHEN id=5 THEN 15 WHEN id=6 THEN 16 WHEN id=17 THEN 17 WHEN id=18 THEN 18 WHEN id=9 THEN 19 WHEN id=10 THEN 20 WHEN id=11 THEN 21 WHEN id=12 THEN 22 WHEN id=7 THEN 23 WHEN id=8 THEN 24 ELSE sort END;