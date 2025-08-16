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

CREATE TABLE IF NOT EXISTS task (
    tm_task_id INT AUTO_INCREMENT PRIMARY KEY,
    tm_task_title VARCHAR(255) NOT NULL,
    tm_task_description TEXT,
    tm_task_type VARCHAR(255) NOT NULL,
    tm_task_priority VARCHAR(255) NOT NULL,
    tm_task_status VARCHAR(255) NOT NULL,
    tm_task_start_date DATE,
    tm_task_end_date DATE,
    tm_task_creation_datetime DATETIME DEFAULT CURRENT_TIMESTAMP,
    tm_task_created_by_id INT,
    tm_task_created_by_name VARCHAR(255) NOT NULL,
    tm_task_assigned_to_id INT,
    tm_task_assigned_to_name VARCHAR(255) NOT NULL,
    tm_task_project_id INT,
    tm_task_project_name VARCHAR(255) NOT NULL,
    tm_document VARCHAR(255) NOT NULL,

    FOREIGN KEY (tm_task_created_by_id)
        REFERENCES user(id) ON DELETE SET NULL ON UPDATE CASCADE,

    FOREIGN KEY (tm_task_assigned_to_id)
        REFERENCES user(id) ON DELETE SET NULL ON UPDATE CASCADE,

    FOREIGN KEY (tm_task_project_id)
        REFERENCES project(project_id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS comment (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    comment_content TEXT NOT NULL,
    comment_added_by_id INT NOT NULL,
    comment_added_by_name VARCHAR(255) NOT NULL,
    task_id INT NOT NULL,
    comment_datetime DATETIME NOT NULL,

    FOREIGN KEY (comment_added_by_id) REFERENCES user(id),
    FOREIGN KEY (task_id) REFERENCES task(tm_task_id)
);
