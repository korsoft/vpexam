UPDATE patients SET username=CONCAT(LOWER(first_name),LOWER(last_name),DATE_FORMAT(dob,"%m%d%Y"),(if(gender='female','f','m')));