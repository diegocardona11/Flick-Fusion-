<?php
/**
 * User.php
 * ----------------------------------------
 * Represents a User object in the system
 *   - used for storing and passing user data
 */

class User {
    public $id;
    public $username;
    public $email;
    public $passwordHash; // Store hashed password for security

    public function __construct($id, $username, $email, $passwordHash) {
        $this->id = $id;
        $this->username = $username;
        $this->email = $email;
        $this->passwordHash = $passwordHash;
    }
}