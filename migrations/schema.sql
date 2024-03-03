-- This is an example of created db schemas for PostgreSql --
CREATE TABLE IF NOT EXISTS users
(
    id        BIGSERIAL    PRIMARY KEY,
    username  VARCHAR(255) NOT NULL,
    email     VARCHAR(255) NOT NULL UNIQUE,
    validts   INT,
    confirmed BOOLEAN      DEFAULT FALSE,
    checked   BOOLEAN      DEFAULT FALSE,
    valid     BOOLEAN      DEFAULT FALSE
);

CREATE INDEX IF NOT EXISTS idx_validts ON users (validts);
CREATE INDEX IF NOT EXISTS idx_email ON users (email);

CREATE TABLE IF NOT EXISTS email_sending_queue
(
    id         BIGSERIAL PRIMARY KEY,
    email_to   VARCHAR(255),
    email_body TEXT,
    processing INT       DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS email_verifying_queue
(
    id         BIGSERIAL PRIMARY KEY,
    email      VARCHAR(255) UNIQUE,
    processing INT       DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
