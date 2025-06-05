CREATE TABLE IF NOT EXISTS user_registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email_address VARCHAR(150),
    password VARCHAR(255),
    contact_number VARCHAR(15),
    user_role VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
