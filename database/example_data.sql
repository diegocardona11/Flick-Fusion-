-- FLICK FUSION - DEMO DATA SCRIPT
-- This script populates the database with example data for demonstration purposes.

-- Disable foreign key checks to allow truncation of tables
SET FOREIGN_KEY_CHECKS = 0;

-- Truncate existing data from tables
DELETE FROM users; 
DELETE FROM friends;
DELETE FROM ratings;
DELETE FROM movies;

-- Reset auto-increment counters to 1
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE friends AUTO_INCREMENT = 1;
ALTER TABLE ratings AUTO_INCREMENT = 1;
ALTER TABLE movies AUTO_INCREMENT = 1;


-- INSERT 10 DEMO USERS
-- Password for all users: 'password123' (hashed)
INSERT INTO users (user_id, username, email, password_hash, avatar_url, created_at) VALUES
(1, 'DemoUser', 'demo@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"😀", "color":"#FF6B6B"}', NOW()),
(2, 'MovieBuff99', 'buff@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"😎", "color":"#4ECDC4"}', NOW()),
(3, 'CinemaQueen', 'queen@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🦋", "color":"#45B7D1"}', NOW()),
(4, 'ActionHero', 'action@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🦁", "color":"#FFA07A"}', NOW()),
(5, 'SciFiFan2025', 'scifi@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🤖", "color":"#98D8C8"}', NOW()),
(6, 'DramaLlama', 'drama@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🐼", "color":"#F7DC6F"}', NOW()),
(7, 'AnimeLover', 'anime@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🦊", "color":"#82E0AA"}', NOW()),
(8, 'RetroViewer', 'retro@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🐢", "color":"#FF6B6B"}', NOW()),
(9, 'IndieSnob', 'indie@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🦉", "color":"#4ECDC4"}', NOW()),
(10, 'FamilyFilms', 'family@example.com', '$2y$10$xUENVHo5y43BZeOovwaVGuwkNAtgBFhG2s9XJD442sxRoZbkYM8xi', '{"emoji":"🐧", "color":"#F7DC6F"}', NOW());


-- INSERT 15 MOVIES
INSERT INTO movies (movie_id, api_id, title, year, genre, description, poster_url) VALUES
(101, 'tt1375666', 'Inception', 2010, 'Action, Adventure, Sci-Fi', 'A thief who steals corporate secrets through the use of dream-sharing technology...', 'https://m.media-amazon.com/images/M/MV5BMjAxMzY3NjcxNF5BMl5BanBnXkFtZTcwNTI5OTM0Mw@@._V1_SX300.jpg'),
(102, 'tt0816692', 'Interstellar', 2014, 'Adventure, Drama, Sci-Fi', 'A team of explorers travel through a wormhole in space...', 'https://m.media-amazon.com/images/M/MV5BZjdkOTU3MDktN2IxOS00OGEyLWFmMjktY2FiMmZkNWIyODZiXkEyXkFqcGdeQXVyMTMxODk2OTU@._V1_SX300.jpg'),
(103, 'tt0468569', 'The Dark Knight', 2008, 'Action, Crime, Drama', 'When the menace known as the Joker wreaks havoc and chaos...', 'https://m.media-amazon.com/images/M/MV5BMTMxNTMwODM0NF5BMl5BanBnXkFtZTcwODAyMTk2Mw@@._V1_SX300.jpg'),
(104, 'tt0133093', 'The Matrix', 1999, 'Action, Sci-Fi', 'When a beautiful stranger leads computer hacker Neo...', 'https://m.media-amazon.com/images/M/MV5BNzQzOTk3OTAtNDQ0Zi00ZTVkLWI0MTEtMDllZjNkYzNjNTc4XkEyXkFqcGdeQXVyNjU0OTQ0OTY@._V1_SX300.jpg'),
(105, 'tt0245429', 'Spirited Away', 2001, 'Animation, Adventure, Family', 'A 10-year-old girl wanders into a world ruled by gods, witches, and spirits.', 'https://m.media-amazon.com/images/M/MV5BMjlmZmI5MDctNDE2YS00YWE0LWE5ZWItZDBhYWQ0NTcxNWRhXkEyXkFqcGdeQXVyMTMxODk2OTU@._V1_SX300.jpg'),
(106, 'tt6751668', 'Parasite', 2019, 'Drama, Thriller', 'Greed and class discrimination threaten the newly formed symbiotic relationship...', 'https://m.media-amazon.com/images/M/MV5BYWZjMjk3ZTItODQ2ZC00NTY5LWE0ZDYtZTI3MjcwN2Q5NTVkXkEyXkFqcGdeQXVyODk4OTc3MTY@._V1_SX300.jpg'),
(107, 'tt0110912', 'Pulp Fiction', 1994, 'Crime, Drama', 'The lives of two mob hitmen, a boxer, a gangster and his wife...', 'https://m.media-amazon.com/images/M/MV5BNGNhMDIzZTUtNTBlZi00MTRlLWFjM2ItYzViMjE3YzI5MjljXkEyXkFqcGdeQXVyNzkwMjQ5NzM@._V1_SX300.jpg'),
(108, 'tt1517268', 'Barbie', 2023, 'Adventure, Comedy, Fantasy', 'Barbie suffers a crisis that leads her to question her world and her existence.', 'https://m.media-amazon.com/images/M/MV5BNjU3N2QxNzYtMjk1NC00MTc4LTk1NTQtMmUxNTljM2I0NDA5XkEyXkFqcGdeQXVyODE5NzE3OTE@._V1_SX300.jpg'),
(109, 'tt0068646', 'The Godfather', 1972, 'Crime, Drama', 'The aging patriarch of an organized crime dynasty transfers control to his reluctant son.', 'https://m.media-amazon.com/images/M/MV5BM2MyNjYxNmUtYTAwNi00MTYxLWJmNWYtYzZlODY3ZTk3OTFlXkEyXkFqcGdeQXVyNzkwMjQ5NzM@._V1_SX300.jpg'),
(110, 'tt0088763', 'Back to the Future', 1985, 'Adventure, Comedy, Sci-Fi', 'Marty McFly, a 17-year-old high school student, is accidentally sent thirty years into the past.', 'https://m.media-amazon.com/images/M/MV5BZmU0M2Y1OGUtZjIxNi00ZjBkLTg1MjgtOWIyNThiZWIwYjRiXkEyXkFqcGdeQXVyMTQxNzMzNDI@._V1_SX300.jpg'),
(111, 'tt0111161', 'The Shawshank Redemption', 1994, 'Drama', 'Two imprisoned men bond over a number of years, finding solace and eventual redemption.', 'https://m.media-amazon.com/images/M/MV5BMDFkYTc0MGEtZmNhMC00ZDIzLWFmNTEtMzc3ZmDkMzA0YjUyXkEyXkFqcGdeQXVyNjU0OTQ0OTY@._V1_SX300.jpg'),
(112, 'tt0076759', 'Star Wars: A New Hope', 1977, 'Action, Adventure, Fantasy', 'Luke Skywalker joins forces with a Jedi Knight, a cocky pilot, a Wookiee and two droids.', 'https://m.media-amazon.com/images/M/MV5BNzVlY2MwMjktM2E4OS00Y2Y3LWE3ZjctYzhkZGM3YzA1ZWM2XkEyXkFqcGdeQXVyNzkwMjQ5NzM@._V1_SX300.jpg'),
(113, 'tt0109830', 'Forrest Gump', 1994, 'Drama, Romance', 'The presidencies of Kennedy and Johnson, the events of Vietnam, Watergate and other historical events unfold...', 'https://m.media-amazon.com/images/M/MV5BNWIwODRlZTUtY2U3ZS00Yzg1LWJhNzYtMmZiYmEyNmU1NjMzXkEyXkFqcGdeQXVyMTQxNzMzNDI@._V1_SX300.jpg'),
(114, 'tt1392190', 'Mad Max: Fury Road', 2015, 'Action, Adventure, Sci-Fi', 'In a post-apocalyptic wasteland, a woman rebels against a tyrannical ruler in search for her homeland.', 'https://m.media-amazon.com/images/M/MV5BN2EwM2I5OWMtMGQyMi00Zjg1LWJkNTctZTdjYTA4OGUwZjMyXkEyXkFqcGdeQXVyMTMxODk2OTU@._V1_SX300.jpg'),
(115, 'tt0407887', 'The Departed', 2006, 'Crime, Drama, Thriller', 'An undercover cop and a mole in the police attempt to identify each other.', 'https://m.media-amazon.com/images/M/MV5BMTI1MTY2OTIxNV5BMl5BanBnXkFtZTYwNjQ4NjY3._V1_SX300.jpg');


-- INSERT RATINGS AND WATCHLIST ENTRIES
INSERT INTO ratings (user_id, movie_id, score_10, status, review, created_at) VALUES
-- DemoUser (1)
(1, 101, 9, 'watched', 'Mind blowing movie!', NOW()),
(1, 102, 10, 'watched', 'Crying in space.', NOW()),
(1, 114, 8, 'watched', 'What a lovely day!', NOW()),
(1, 109, 5, 'watchlist', NULL, NOW()),

-- MovieBuff99 (2)
(2, 115, 10, 'watched', 'Scorsese perfection.', NOW()),
(2, 106, 9, 'watched', 'So tense.', NOW()),
(2, 107, 9, 'watched', 'Iconic dialogue.', NOW()),
(2, 110, 5, 'watched', 'Overrated.', NOW()),

-- CinemaQueen (3)
(3, 113, 10, 'watched', 'Run Forrest Run!', NOW()),
(3, 108, 10, 'watched', 'Hi Barbie!', NOW()),
(3, 111, 9, 'watched', 'Beautiful ending.', NOW()),
(3, 114, 2, 'watched', 'Too loud for me.', NOW()),

-- ActionHero (4)
(4, 114, 10, 'watched', 'WITNESS ME!', NOW()),
(4, 101, 8, 'watched', 'Confusing but cool fights.', NOW()),
(4, 103, 10, 'watched', 'Joker is the goat.', NOW()),
(4, 104, 10, 'watched', 'Dodge this.', NOW()),
(4, 105, 4, 'watched', 'Weird cartoon.', NOW()),

-- SciFiFan2025 (5)
(5, 102, 10, 'watched', 'Best sci-fi of the decade.', NOW()),
(5, 112, 10, 'watched', 'The one that started it all.', NOW()),
(5, 104, 9, 'watched', NULL, NOW()),
(5, 110, 8, 'watched', 'Great time travel logic.', NOW()),

-- DramaLlama (6)
(6, 111, 10, 'watched', 'Best movie ever made.', NOW()),
(6, 113, 9, 'watched', 'I cried so much.', NOW()),
(6, 106, 10, 'watched', 'Speechless.', NOW()),
(6, 114, 5, 'watchlist', NULL, NOW()),

-- AnimeLover (7)
(7, 105, 10, 'watched', 'Masterpiece.', NOW()),
(7, 104, 8, 'watched', 'Feels like anime.', NOW()),
(7, 101, 7, 'watched', 'Reminds me of Paprika.', NOW()),
(7, 112, 5, 'watchlist', NULL, NOW()),

-- RetroViewer (8)
(8, 110, 10, 'watched', 'Peak 80s cinema.', NOW()),
(8, 112, 10, 'watched', 'Classic.', NOW()),
(8, 107, 9, 'watched', 'Cool soundtrack.', NOW()),
(8, 102, 4, 'watched', 'Too much CGI.', NOW()),

-- IndieSnob (9)
(9, 108, 1, 'watched', 'Corporate garbage.', NOW()),
(9, 112, 3, 'watched', 'Baby movie.', NOW()),
(9, 107, 10, 'watched', 'Real cinema.', NOW()),
(9, 106, 9, 'watched', 'Finally a good Oscar winner.', NOW()),

-- FamilyFilms (10)
(10, 105, 9, 'watched', 'A bit scary but great.', NOW()),
(10, 108, 8, 'watched', 'Fun for the family.', NOW()),
(10, 110, 10, 'watched', 'Kids loved it.', NOW()),
(10, 107, 5, 'watchlist', NULL, NOW());


-- INSERT FRIENDSHIPS BETWEEN USERS
INSERT INTO friends (user_id, friend_id, status) VALUES
-- DemoUser (1) Connections
(1, 2, 'accepted'), (2, 1, 'accepted'), -- MovieBuff99
(1, 4, 'accepted'), (4, 1, 'accepted'), -- ActionHero
(1, 10, 'accepted'), (10, 1, 'accepted'), -- FamilyFilms
(1, 5, 'pending'),  -- Demo sent request to SciFiFan
(3, 1, 'pending'),  -- CinemaQueen sent request to Demo

-- Other Circles
(2, 4, 'accepted'), (4, 2, 'accepted'), -- MovieBuff & ActionHero (Action fans)
(5, 8, 'accepted'), (8, 5, 'accepted'), -- SciFiFan & RetroViewer (Nostalgia)
(9, 6, 'accepted'), (6, 9, 'accepted'), -- IndieSnob & DramaLlama (Art films)
(7, 10, 'accepted'), (10, 7, 'accepted'), -- AnimeLover & FamilyFilms (Animation fans)
(3, 6, 'accepted'), (6, 3, 'accepted'); -- CinemaQueen & DramaLlama (Criers)


-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;