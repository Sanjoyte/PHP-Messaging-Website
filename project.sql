CREATE TABLE user (
    user_id INT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password CHAR(255) NOT NULL
);
CREATE TABLE admin (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE student (
    user_id INT PRIMARY KEY,
    FOREIGN KEY (user_id) REFERENCES user(user_id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

CREATE TABLE messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    seen TINYINT(1) DEFAULT 0,
    FOREIGN KEY (sender_id) REFERENCES user(user_id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES user(user_id) ON DELETE CASCADE,
    INDEX idx_sender_receiver (sender_id, receiver_id),
    INDEX idx_receiver_seen (receiver_id, seen),
    INDEX idx_sent_at (sent_at)
);