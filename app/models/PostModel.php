<?php
class PostModel extends Model
{
    /**
     * By default, the entity will be persisted to a table with the same name 
     * as the class name (the class name is converted to an underscored and 
     * lowercase string). In order to change that, you can use the $table property 
     * as follows:
     * 
     * protected $table = 'posts';
     * 
     * Now instances of "Post" will be persisted into a table named "posts".
     */
    
    /**
     * Fetches a row from a result set associated with a PDOStatement object.
     * 
     * @param string $foo
     * @param string $bar
     * @returns false|obj
     * @throws ModelException
     */
    public function findByFooOrBar($foo, $bar)
    {
        $sql = 'SELECT * FROM post WHERE foo = ? OR bar = ?';
        
        try {
            // http://php.net/manual/en/pdostatement.fetch.php
            $stmt = $this->execute($sql, array($foo, $bar));
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        }
    }
    
    /**
     * Returns an array containing all of the result set rows.
     * Similar to $postModel->findAllBy(array('category_id'=>1), $options);
     * 
     * @param int $categoryId
     * @param array $options sort, order, page and count
     * @returns false|array
     * @throws ModelException
     */
    public function findAllByCategoryId($categoryId, $options)
    {        
        $sql = sprintf('SELECT * FROM post WHERE category_id = ? 
            ORDER BY %s %s', $options['sort'], $options['order']);
        
        /**
         * 1. Limit and offset
         * 
         *    You can use $options['limit'] to specify the number of records to be 
         *    retrieved, and use $options['offset'] to specify the number of records 
         *    to skip before starting to return the records.
         * 
         * 2. Page and count:
         * 
         *    You can use $options['page'] and $options['count'] to perform a paginated 
         *    query.
         */
        $sql = $this->limit($sql, $options);
        
        try {
            // http://www.php.net/manual/en/pdostatement.fetchall.php
            $stmt = $this->execute($sql, array($categoryId));
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        }
    }
}
