-- This is an example of created db schemas --

CREATE TABLE users
(
    id        INT AUTO_INCREMENT PRIMARY KEY,
    username  VARCHAR(255) NOT NULL,
    email     VARCHAR(255) NOT NULL UNIQUE,
    validts   INT UNSIGNED,
    confirmed TINYINT(1) DEFAULT 0,
    checked   TINYINT(1) DEFAULT 0,
    valid     TINYINT(1) DEFAULT 0
);

CREATE INDEX idx_validts ON users (validts);
CREATE INDEX idx_email ON users (email);

CREATE TABLE email_sending_queue
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    email_to   VARCHAR(255),
    email_body TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
