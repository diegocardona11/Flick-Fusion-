<!-- PROJECT HEADER -->
<br />
<div align="center">
  <h1 align="center">Flick Fusion</h1>
</div>
<br />


<!-- ABOUT THE PROJECT -->
**Flick Fusion** is a web application designed for movie enthusiasts to discover, track, and rate movies. Users can build personal watchlists, compare their ratings with friends, and engage with a community of fellow movie lovers. 


## Built With

* [![PHP][PHP-badge]][PHP-url]
* [![MySQL][MySQL-badge]][MySQL-url]
* [![JavaScript][JavaScript-badge]][JavaScript-url]
* [![HTML][HTML-badge]][HTML-url]
* [![CSS][CSS-badge]][CSS-url]


<!-- GETTING STARTED -->
## Getting Started
To get a local copy up and running follow these simple steps.

### Prerequisites
You need a local server environment (like XAMPP) and Git. 
* XAMPP (Apache & MySQL) [Download Here](https://www.apachefriends.org/download.html)

### Installation 
1. **Get a free API Key** at [OMDb API](http://www.omdbapi.com/apikey.aspx)
2. **Clone the repo** into your web server directory (e.g., `htdocs`)
   ```sh
   git clone https://github.com/diegocardona11/Flick-Fusion-.git
3. **Setup the Database**
    - Open `http://localhost/phpmyadmin`
    - Create a new database named `flick_fusion`
4. **Choose your Data Option (Important)**
    - **Option A: Fresh install.** Import `database/schema.sql`. This creates an empty database ready for new users. 
    - **Option B: Demo Mode (Recommended for Testing).** Import `database/example_data.sql`.
        > **Why use this?** This pre-populates the database with test users, avatars, and friend connections so you can immediately test the social features and public/private profile settings. 
        > <br /> **Demo Login:**
        > * Username: `DemoUser`
        > * Password: `password123`
5. **Configure Envrionment.** Create a `.env` file in the root directory and enter your API key:
    ```sh
    OMDB_API_KEY='YOUR_KEY_HERE';
    DB_HOST='localhost';
    DB_NAME='flick_fusion';
    DB_USER='root';
    DB_PASS='';
6. Start the App Open your browser to `http://localhost/Flick-Fusion-/public/`



## License 
Distributed under the MIT License. See `LICENSE` for more information.


## Acknowledgements
* [OMDb API](http://www.omdbapi.com/)
* [Best-README-Template](https://github.com/othneildrew/Best-README-Template.git)



<!-- MARKDOWN LINKS & IMAGES -->
[PHP-badge]: https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white
[PHP-url]: https://www.php.net/

[MySQL-badge]: https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white
[MySQL-url]: https://www.mysql.com/

[JavaScript-badge]: https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black
[JavaScript-url]: https://developer.mozilla.org/en-US/docs/Web/JavaScript

[HTML-badge]: https://img.shields.io/badge/HTML-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white
[HTML-url]: https://developer.mozilla.org/en-US/docs/Web/HTML

[CSS-badge]: https://img.shields.io/badge/CSS-639?style=for-the-badge&logo=css3&logoColor=white
[CSS-url]: https://developer.mozilla.org/en-US/docs/Web/CSS