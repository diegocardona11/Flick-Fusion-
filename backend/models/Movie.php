<?php
/** Movie.php
 * ----------------------------------------
 * Represents a Movie object in the system
 *   - will be populated with data from external API
 *   - used for storing and passing movie data
 */

class Movie {
    public $id;
    public $title;
    public $year;
    public $description;
    public $releaseDate;
    public $rating;

    public function __construct($id, $title, $year, $description, $releaseDate, $rating) {
        $this->id = $id;
        $this->title = $title;
        $this->year = $year;
        $this->description = $description;
        $this->releaseDate = $releaseDate;
        $this->rating = $rating;
    }
}