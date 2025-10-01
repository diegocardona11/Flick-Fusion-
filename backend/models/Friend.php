<?php
/**
 * Friend.php
 * ----------------------------------------
 * Represents a Friend object in the system
 *   - used for storing and passing friend data
 *   - each row links one user with another
 */

class Friend {
    public $id;
    public $userId;
    public $friendId;

    public function __construct($id, $userId, $friendId) {
        $this->id = $id;
        $this->userId = $userId;
        $this->friendId = $friendId;
    }
}