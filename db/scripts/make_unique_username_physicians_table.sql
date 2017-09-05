UPDATE IGNORE virtual_physical_secure.physicians SET username = LOWER(CONCAT('dr', first_name, last_name));
ALTER TABLE virtual_physical_secure.physicians ADD UNIQUE phy_username_idx (username);