CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    email_address VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    contact_number VARCHAR(15),
    user_role VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS project (
    project_id INT AUTO_INCREMENT PRIMARY KEY,
    project_name VARCHAR(255) NOT NULL,
    project_start_date DATE,
    project_end_date DATE,
    project_description TEXT,
    project_created_by INT,
    project_assigned_to INT,
    
    FOREIGN KEY (project_created_by) REFERENCES user(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
        
    FOREIGN KEY (project_assigned_to) REFERENCES user(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
);

