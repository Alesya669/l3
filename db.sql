
CREATE TABLE application (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fio VARCHAR(150) NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male','female') NOT NULL,
    bio TEXT NOT NULL,
    contract TINYINT(1) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE languages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE
);


CREATE TABLE app_languages (
    app_id INT,
    lang_id INT,
    PRIMARY KEY (app_id, lang_id),
    FOREIGN KEY (app_id) REFERENCES application(id) ON DELETE CASCADE,
    FOREIGN KEY (lang_id) REFERENCES languages(id) ON DELETE CASCADE
);

INSERT INTO languages (name, code) VALUES
('Pascal', 'pascal'), ('C', 'c'), ('C++', 'cpp'),
('JavaScript', 'javascript'), ('PHP', 'php'), ('Python', 'python'),
('Java', 'java'), ('Haskell', 'haskell'), ('Clojure', 'clojure'),
('Prolog', 'prolog'), ('Scala', 'scala'), ('Go', 'go');
