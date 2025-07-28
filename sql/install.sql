-- 创建用户表（群主或平台用户）
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(150),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建模块表（每个群主可以有一个或多个模块）
CREATE TABLE IF NOT EXISTS modules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    module_name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 创建黑名单记录表
CREATE TABLE IF NOT EXISTS blacklists (
    id INT AUTO_INCREMENT PRIMARY KEY,
    module_id INT NOT NULL,
    wx_id VARCHAR(100) NOT NULL,              -- 微信号或原始ID
    nickname VARCHAR(100),
    reason TEXT,
    blacklist_time DATETIME,
    group_name VARCHAR(150),
    image_path VARCHAR(255),
    video_path VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (module_id) REFERENCES modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;