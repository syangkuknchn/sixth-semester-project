CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    email VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    unique_id BIGINT(20) NOT NULL,
    fname VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    lname VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    img VARCHAR(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    status VARCHAR(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    INDEX (unique_id)
);

CREATE TABLE messages (
    msg_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    msg TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
    sender_id INT(11) NOT NULL,
    receiver_id INT(11) NOT NULL,
    sent_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    incoming_msg_id BIGINT(20) NOT NULL,
    outgoing_msg_id BIGINT(20) NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    INDEX (sender_id),
    INDEX (receiver_id)
);
