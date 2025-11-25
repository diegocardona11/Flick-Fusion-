-- schema.sql
-- ------------------------------
-- Defines the database structure for Flick Fusion
-- Contains tables for users, movies, ratings, and friends


-- Users table
CREATE TABLE if NOT EXISTS users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,           -- unique usernames
    email VARCHAR(100) NOT NULL UNIQUE,            -- unique emails
    password_hash VARCHAR(255) NOT NULL,            -- hashed passwords
    avatar_url TEXT,                                -- stores JSON with emoji/color or NULL for default
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Movies table
CREATE TABLE if NOT EXISTS movies (
    movie_id INT AUTO_INCREMENT PRIMARY KEY,
    api_id VARCHAR(50) UNIQUE,
    title VARCHAR(255) NOT NULL,
    year INT,
    genre VARCHAR(100),
    description TEXT,
    poster_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ratings table
CREATE TABLE if NOT EXISTS ratings (
    rating_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    movie_id INT NOT NULL,
    score_10 TINYINT NOT NULL CHECK (score_10 >= 1 AND score_10 <= 10),
    status ENUM('watched', 'watchlist') NOT NULL DEFAULT 'watchlist',
    review TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_user_movie (user_id, movie_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (movie_id) REFERENCES movies(movie_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Friends table
CREATE TABLE if NOT EXISTS friends (
    user_id INT NOT NULL,
    friend_id INT NOT NULL,
    status ENUM('pending', 'accepted') DEFAULT 'pending',
    PRIMARY KEY (user_id, friend_id),
    FOREIGN KEY (user_id) REFERENCES users(user_id),
    FOREIGN KEY (friend_id) REFERENCES users(user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
