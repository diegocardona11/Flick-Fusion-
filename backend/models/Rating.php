<?php
/**
 * Rating.php
 * ----------------------------------------
 * Represents a Rating object in the system
 *   - used for storing and passing rating data
 */

class Rating {
    public $id;
    public $movieId;
    public $userId;
    public $score;

    public function __construct($id, $movieId, $userId, $score) {
        $this->id = $id;
        $this->movieId = $movieId;
        $this->userId = $userId;
        $this->score = $score;
    }
}