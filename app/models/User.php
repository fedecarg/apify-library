<?php
class User extends Apify_Entity
{
    protected $id;
    protected $email;
    protected $username;
    protected $password;
    protected $name = '';
    protected $gender = '';
    protected $updated_at;
    protected $created_at;
    
    // validate username (optional)
    public function setUsername($value)
    {
        if (preg_match('/[^a-z0-9\-_.]/i', $value)) { // Undefined variable error fixed
            throw new Apify_ValidationException('Invalid username');
        }
        $this->username = $value;
    }
    
    // sanitize and validate name (optional) 
    public function setName($value)
    {
        $value = htmlspecialchars(trim($value), ENT_QUOTES);
        if (empty($value) || strlen($value) < 3) {
            throw new Apify_ValidationException('Invalid name');
        }
        $this->name = $value;
    }
    
    // validate email (optional)
    public function setEmail($value)
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new Apify_ValidationException('Invalid email address');
        }
        $this->email = $value;
    }
    
}