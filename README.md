# Flick Fusion 🎬

Flick Fusion is a web application designed for movie enthusiasts to discover, track, and rate movies. Users can build personal watchlists, compare their ratings with friends, and engage with a community of fellow movie lovers. 

This project is built with a PHP backend, MySQL database, and a dynamic, user-friendly interface that gets comprehensive movie data from the OMDb API. 

## Core Features
- **User Authentication:** Secure user registration and login system with password hashing.
- **Movie Search:** Instantly search for any movie using the OMDb API.
- **Personal Movie Lists:** Add or remove movies to a personal watch list.
- **Rating System:** Rate movies on a 1-10 scale.
- **Responsive Design:** A clean, modern interface that works seamlessly on desktop and mobile devices. 

## Future Features
- **Advanced User Profiles:** Public and private profile options with custom display names, avatars, and favorite genres.
- **Enhanced Security:** Password reset flows and account lockout policies.
- **Friend System:** Send and accept friend requests to build a social network.
- **Community & Location-Based Features:** Discover popular movies trending at your university, in your city, or a specific radius. 
- **Compare Ratings:** See how your movie ratins stack up against friends.
- **The "Flick Fusion" Score:** An advanced, multi-factor rating system for more nuanced reviews.
- **User Stats:** Automatically generated statistics like total movies rated and average scores. 

## Technology 
- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **External APIs:** OMDb API for movie data
- **Development Environment:** XAMPP (Apache, MySQL)

## Installation Steps 
1. Clone the repository.
2. Move the project to your web server directory.
    - Place the `flick-fusion` folder inside your XAMPP `htdocs`
3. Set up the Database.
    -  Start the **Apache** and **MySQL** modules in your XAMPP Control Panel.
    -  Open your web browser and navigate to `http://localhost/phpmyadmin`.
    -  Create a new database and name it `flick_fusion`.
    -  Select the new `flick_fusion` database, go to the **"Import"** tab, and upload the `database/schema.sql` file from this project. This will create all the necessary tables.
4. Configure the API Key (Optional but Recommended).
    -  The project will work with a fallback API key for OMDb. For better performance, it is recommended to get your own free key from omdbapi.com.
    -  In the project's root directory, create a new file named `.env`.
    -  Add the following line to the `.env` file, replacing `your_key_here` with your actual key.
5. Run the application
    - You can now access the project by navigating to `http://localhost/flick-fusion/public/` in your browser.
  
## Contribution Guidelines
All development follows a feature-branch workflow to maintain a stable `main` branch.
