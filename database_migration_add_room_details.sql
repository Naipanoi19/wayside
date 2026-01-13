-- Migration: Add room details and gallery table
-- Run this script to add room details fields and room_gallery table
-- Note: Run each ALTER TABLE statement separately if column already exists

-- Add room details columns to rooms table (run individually if errors occur)
ALTER TABLE rooms ADD COLUMN price_per_night DECIMAL(10,2) DEFAULT 5000 AFTER type_id;
ALTER TABLE rooms ADD COLUMN has_toilet BOOLEAN DEFAULT 0;
ALTER TABLE rooms ADD COLUMN has_kitchen BOOLEAN DEFAULT 0;
ALTER TABLE rooms ADD COLUMN has_living_room BOOLEAN DEFAULT 0;
ALTER TABLE rooms ADD COLUMN has_bedroom BOOLEAN DEFAULT 1;
ALTER TABLE rooms ADD COLUMN has_bathroom BOOLEAN DEFAULT 0;
ALTER TABLE rooms ADD COLUMN has_balcony BOOLEAN DEFAULT 0;
ALTER TABLE rooms ADD COLUMN toilet_description TEXT;
ALTER TABLE rooms ADD COLUMN kitchen_description TEXT;
ALTER TABLE rooms ADD COLUMN living_room_description TEXT;
ALTER TABLE rooms ADD COLUMN bedroom_description TEXT;
ALTER TABLE rooms ADD COLUMN bathroom_description TEXT;
ALTER TABLE rooms ADD COLUMN balcony_description TEXT;
ALTER TABLE rooms ADD COLUMN amenities TEXT;

-- Create room_gallery table if it doesn't exist
CREATE TABLE IF NOT EXISTS room_gallery (
    id INT AUTO_INCREMENT PRIMARY KEY,
    room_id INT NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    is_video TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE CASCADE,
    INDEX idx_room_id (room_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

