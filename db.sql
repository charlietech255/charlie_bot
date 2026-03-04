-- ============================================================
--  Charlie Bot – Database Schema
--  Database: dsnonli_bot
--  Import: mysql -u dsnonli_luna -p dsnonli_bot < db.sql
-- ============================================================

-- ── Chat Logs ───────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS chats (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    session_id  VARCHAR(64)   NOT NULL,
    ip          VARCHAR(45)   NOT NULL DEFAULT '',
    style       VARCHAR(50)   NOT NULL DEFAULT 'charlie',
    user_message TEXT          NOT NULL,
    bot_response TEXT          NOT NULL,
    created_at  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip        (ip),
    INDEX idx_style     (style),
    INDEX idx_created   (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Blocked IPs ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS blocked_ips (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    ip          VARCHAR(45)  NOT NULL UNIQUE,
    reason      VARCHAR(255) NOT NULL DEFAULT 'Manual block',
    blocked_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip (ip)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Admin Users ──────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admins (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    username     VARCHAR(80)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    last_login   DATETIME     NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── Admin Sessions ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS admin_sessions (
    token       VARCHAR(64)  NOT NULL PRIMARY KEY,
    admin_id    INT          NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    expires_at  DATETIME     NOT NULL,
    FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
