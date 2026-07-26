-- Allow guest ratings: make user_id nullable and add guest fields
ALTER TABLE ratings DROP FOREIGN KEY ratings_ibfk_1;
ALTER TABLE ratings MODIFY user_id INT NULL;
ALTER TABLE ratings ADD COLUMN guest_name VARCHAR(100) DEFAULT NULL AFTER user_id;
ALTER TABLE ratings ADD COLUMN guest_email VARCHAR(255) DEFAULT NULL AFTER guest_name;
