-- ============================================================
-- LINKRA DATABASE v2
-- Import in phpMyAdmin → linkra_db → Import
-- ============================================================
CREATE DATABASE IF NOT EXISTS linkra_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE linkra_db;

-- TABLE 1: users
CREATE TABLE users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,
    role       ENUM('business','creator','admin') NOT NULL DEFAULT 'creator',
    avatar     VARCHAR(500)  DEFAULT NULL,
    is_active  TINYINT(1)    NOT NULL DEFAULT 1,
    created_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- TABLE 2: businesses
CREATE TABLE businesses (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT          NOT NULL UNIQUE,
    company_name VARCHAR(200) NOT NULL,
    industry     VARCHAR(100) DEFAULT NULL,
    website      VARCHAR(300) DEFAULT NULL,
    description  TEXT         DEFAULT NULL,
    logo         VARCHAR(500) DEFAULT NULL,
    created_at   TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 3: creators
CREATE TABLE creators (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT           NOT NULL UNIQUE,
    bio           TEXT          DEFAULT NULL,
    niche         VARCHAR(200)  DEFAULT NULL,
    tiktok_url    VARCHAR(300)  DEFAULT NULL,
    instagram_url VARCHAR(300)  DEFAULT NULL,
    youtube_url   VARCHAR(300)  DEFAULT NULL,
    facebook_url  VARCHAR(300)  DEFAULT NULL,
    x_url         VARCHAR(300)  DEFAULT NULL,
    rate_min      DECIMAL(10,2) DEFAULT NULL,
    rate_max      DECIMAL(10,2) DEFAULT NULL,
    created_at    TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 4: projects (campaigns)
-- New workflow: no applications, creators submit directly
CREATE TABLE projects (
    id                    INT AUTO_INCREMENT PRIMARY KEY,
    business_id           INT           NOT NULL,
    title                 VARCHAR(200)  NOT NULL,
    description           TEXT          NOT NULL,
    requirements          TEXT          DEFAULT NULL,
    platform              ENUM('tiktok','reels','shorts','facebook','x','all') NOT NULL DEFAULT 'all',
    content_type          ENUM('short-form','long-form','both') NOT NULL DEFAULT 'both',
    category              VARCHAR(100)  DEFAULT NULL,
    overall_budget        DECIMAL(12,2) NOT NULL DEFAULT 0,
    budget_remaining      DECIMAL(12,2) NOT NULL DEFAULT 0,
    payout_per_1k         DECIMAL(10,2) NOT NULL DEFAULT 0,
    max_payout_per_creator DECIMAL(10,2) NOT NULL DEFAULT 0,
    deadline              DATE          DEFAULT NULL,
    thumbnail             VARCHAR(500)  DEFAULT NULL,
    instruction           VARCHAR(500)  DEFAULT NULL,
    status                ENUM('open','closed','completed') NOT NULL DEFAULT 'open',
    created_at            TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at            TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (business_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 5: submissions
-- Core table — any creator can submit to any open campaign
CREATE TABLE submissions (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    project_id   INT          NOT NULL,
    creator_id   INT          NOT NULL,
    video_url    VARCHAR(500) NOT NULL,
    video_type   ENUM('tiktok','youtube','instagram','facebook','x','other') DEFAULT 'other',
    view_count   INT          DEFAULT 0,
    payout_amount DECIMAL(10,2) DEFAULT 0,
    status       ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    rejection_reason TEXT     DEFAULT NULL,
    submitted_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    reviewed_at  TIMESTAMP    NULL DEFAULT NULL,
    UNIQUE KEY unique_submission (project_id, creator_id),
    FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (creator_id)  REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 6: payments
CREATE TABLE payments (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT           NOT NULL UNIQUE,
    project_id    INT           NOT NULL,
    business_id   INT           NOT NULL,
    creator_id    INT           NOT NULL,
    amount        DECIMAL(10,2) NOT NULL,
    status        ENUM('pending','released') NOT NULL DEFAULT 'pending',
    released_at   TIMESTAMP     NULL DEFAULT NULL,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id)    REFERENCES projects(id)    ON DELETE CASCADE,
    FOREIGN KEY (business_id)   REFERENCES users(id)       ON DELETE CASCADE,
    FOREIGN KEY (creator_id)    REFERENCES users(id)       ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 7: project_comments
CREATE TABLE project_comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT  NOT NULL,
    user_id    INT  NOT NULL,
    body       TEXT NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id)    REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 8: posts (community feed)
CREATE TABLE posts (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    body       VARCHAR(280) NOT NULL,
    image_url  VARCHAR(500) DEFAULT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 9: post_likes
CREATE TABLE post_likes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT NOT NULL,
    user_id    INT NOT NULL,
    UNIQUE KEY unique_like (post_id, user_id),
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 10: post_comments
CREATE TABLE post_comments (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    post_id    INT          NOT NULL,
    user_id    INT          NOT NULL,
    body       VARCHAR(500) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 11: notifications
CREATE TABLE notifications (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT          NOT NULL,
    message    VARCHAR(300) NOT NULL,
    link       VARCHAR(300) DEFAULT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- TABLE 12: reviews
CREATE TABLE reviews (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    project_id  INT      NOT NULL,
    reviewer_id INT      NOT NULL,
    reviewee_id INT      NOT NULL,
    rating      TINYINT  NOT NULL CHECK (rating BETWEEN 1 AND 5),
    comment     TEXT     DEFAULT NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_review (project_id, reviewer_id),
    FOREIGN KEY (project_id)  REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id)    ON DELETE CASCADE,
    FOREIGN KEY (reviewee_id) REFERENCES users(id)    ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SEED DATA
-- All passwords = "password"
-- ============================================================
INSERT INTO users (name, email, password, role) VALUES
('LINKRA Admin',  'admin@linkra.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Juan Santos',   'business@linkra.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'business'),
('Ali Cruz',      'creator@linkra.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'creator');

INSERT INTO businesses (user_id, company_name, industry, description) VALUES
(2, "Juan's Bistro", 'Food & Beverage', 'A modern Filipino bistro looking for content creators to promote our brand.');

INSERT INTO creators (user_id, bio, niche, tiktok_url) VALUES
(3, 'Short-form content creator specializing in food and lifestyle.', 'Food, Lifestyle', 'https://tiktok.com/@alicruz');
