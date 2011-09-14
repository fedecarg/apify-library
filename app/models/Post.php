<?php
class Post extends Entity
{
    protected $id;
    protected $category_id;
    protected $title = '';
    protected $text = '';
    protected $updated_at;
    protected $created_at;
    
    public function setCategoryId($value)
    {
        if (! is_numeric($value)) {
            throw new ValidationException('The category you have selected is invalid');
        }
        $this->category_id = (int) $value;
    }
    
    public function setTitle($value)
    {
        $value = htmlspecialchars(strip_tags($value), ENT_QUOTES);
        if (empty($value) || strlen($value) < 5) {
            throw new ValidationException('The title is shorter than 5 characters');
        }
        $this->title = $value;
    }
    
    public function setText($value)
    {
        $value = htmlspecialchars(strip_tags($value), ENT_QUOTES);
        if (empty($value) || strlen($value) < 5) {
            throw new ValidationException('The text is shorter than 5 characters');
        }        
        $this->text = $value;
    }    
}