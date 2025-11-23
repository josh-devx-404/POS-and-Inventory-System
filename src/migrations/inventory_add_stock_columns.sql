-- Add stock column to menu_items table
-- Run this SQL script to add the stock tracking feature

USE makys_cafe;

-- Add stock column to menu_items
ALTER TABLE menu_items 
ADD COLUMN stock INT DEFAULT 0 AFTER price;

-- Update existing items with default stock value
UPDATE menu_items SET stock = 50 WHERE stock = 0;

-- You can also add an index for faster queries
CREATE INDEX idx_stock ON menu_items(stock);

-- Optional: Create a trigger to log stock changes (for audit purposes)
CREATE TABLE IF NOT EXISTS stock_history (
    history_id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    old_stock INT,
    new_stock INT,
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES menu_items(item_id) ON DELETE CASCADE
);

DELIMITER $$
CREATE TRIGGER after_stock_update
AFTER UPDATE ON menu_items
FOR EACH ROW
BEGIN
    IF OLD.stock != NEW.stock THEN
        INSERT INTO stock_history (item_id, old_stock, new_stock)
        VALUES (NEW.item_id, OLD.stock, NEW.stock);
    END IF;
END$$
DELIMITER ;