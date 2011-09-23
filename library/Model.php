<?php
/**
 * Apify - Copyright (c) 2011, Kewnode Ltd. All rights reserved.
 * 
 * THIS COPYRIGHT INFORMATION MUST REMAIN INTACT AND MAY NOT BE MODIFIED IN ANY WAY.
 * 
 * THIS SOFTWARE IS PROVIDED BY KEWNODE LTD "AS IS" AND ANY EXPRESS OR IMPLIED 
 * WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF 
 * MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO 
 * EVENT SHALL KEWNODE LTD BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, 
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT 
 * OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS 
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, 
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY 
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category    Library
 * @package     Model
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Model
{
    /**
     * @var Database
     */
    protected $db;
    
    /**
     * @var null|string
     */
    protected $entity;
    
    /**
     * @var null|string
     */
    protected $table;
    
    /**
     * @var array
     */
    protected $defaultOptions = array(
        'sort'   => 'id',
        'order'  => 'DESC',
        'offset' => 0,
        'count'  => 50
    );
    
    /**
     * Class constructor.
     * 
     * @param Database $db
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }
    
    /**
     * Sets default option values.
     *
     * @param array $options
     * @return Model 
     */
    public function setDefaultOptions(array $options)
    {
        $this->defaultOptions = $options;
        return $this;
    }
    
    /**
     * Returns default option values.
     * 
     * @param array $options An array containing additional elements to merge in
     * @return array
     */
    public function getDefaultOptions(array $options = array())
    {
        $defaultOptions = $this->defaultOptions;
        if (count($options) > 0) {
            $defaultOptions = array_merge($defaultOptions, $options);
        }
        return $defaultOptions;
    }
    
    /**
     * Sets the table name.
     * 
     * @param string $name
     * @return Model
     */
    public function setTable($name)
    {
        $this->table = $name;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
    
    /**
     * Sets the entity class name.
     * 
     * @param string $name
     * @return Model
     */
    public function setEntity($name)
    {
        $this->entity = $name;
        return $this;
    }
    
    /**
     * @return string
     */
    public function getEntity()
    {
        return $this->entity;
    }
    
    /**
     * Builds and executes a prepared statement.
     * 
     * @param string $sql
     * @param array $values
     * @return PDOStatement
     * @throws RuntimeException
     */
    public function execute($sql, array $values = array())
    {
       if (! isset($this->entity)) {
            throw new RuntimeException('Entity name is undefined');
        } else if (! isset($this->table)) {
            throw new RuntimeException('Table name is undefined');
        }
        
        $stmt = $this->db->prepare(trim($sql));
        $stmt->setFetchMode(PDO::FETCH_OBJ|PDO::FETCH_PROPS_LATE);
        $stmt->execute($values);
        $this->db->addQuery($stmt->queryString);
        
        return $stmt;
    }
    
    /**
     * @param int $id
     * @return object|false
     * @throws ModelException
     */
    public function find($id)
    {
        $sql = 'SELECT * FROM `' . $this->table . '` WHERE id = ?';
        
        try {
            $stmt = $this->execute($sql, array((int)$id));
            $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->entity);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        }
    }
    
    /**
     * @param array $condition
     * @return object|false
     * @throws ModelException
     */
    public function findBy(array $condition)
    {
        $columns = array();
        $values = array();
        foreach ($condition as $key => $value) {
            $columns[] = '`' . $key . '` = ?';
            $values[] = $value;
        }
        
        $sql = 'SELECT * FROM `' . $this->table . '` '
            . ' WHERE ' . implode(' AND ', $columns);
            
        try {    
            $stmt = $this->execute($sql, $values);
            $stmt->setFetchMode(PDO::FETCH_CLASS|PDO::FETCH_PROPS_LATE, $this->entity);
            return $stmt->fetch();
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        }
    }
    
    /**
     * @param array $options
     * @return array|false
     * @throws ModelException
     */
    public function findAll(array $options = array())
    {
        $options = $this->getDefaultOptions($options);
        
        $sql = 'SELECT * FROM `' . $this->table . '` ORDER BY ? ? ';
        $sql = $this->limit($sql, $options);
        
        try {
            $stmt = $this->execute($sql, array($options['sort'], $options['order']));
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        } 
    }
    
    /**
     * @param array $condition
     * @param array $options
     * @return object|false
     * @throws ModelException
     */
    public function findAllBy(array $condition, array $options = array())
    {
        $options = $this->getDefaultOptions($options);
        
        $columns = array();
        $values = array();
        foreach ($condition as $key => $value) {
            $columns[] = '`' . $key . '` = ?';
            $values[] = $value;
        }
        $values[] = $options['sort'];
        $values[] = $options['order'];
        
        $sql = 'SELECT * FROM `' . $this->table . '` WHERE ' . implode(' AND ', $columns) . ' ORDER BY ? ?';
        $sql = $this->limit($sql, $options);
        
        try {
            $stmt = $this->execute($sql, $values);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        } 
    }
    
    /**
     * @param Entity $entity
     * @return mixed
     * @throws ModelException
     */
    public function save(Entity $entity)
    {
        try {
            if (null === $entity->id) {
                return $this->insert($entity);
            } else {
                return $this->update($entity);
            }
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        }
    }
    
    /**
     * @param Entity $entity
     * @return int Returns the ID of the last inserted row
     */
    public function insert(Entity $entity)
    {
        $vars = $entity->toArray();
        unset($vars['id']);
        
        if (array_key_exists('created_at', $vars)) {
            $vars['created_at'] = date('Y-m-d H:i:s');
        }
        if (array_key_exists('updated_at', $vars)) {
            $vars['updated_at'] = date('Y-m-d H:i:s');
        }
        
        $columns = '';
        $tokens = '';
        $values = array();
        foreach ($vars as $key => $value) {
            $columns .= '`' . $key . '`,';
            $tokens .= '?,';
            $values[] = $value;
        }
        
        $sql = 'INSERT INTO ' . $this->table 
            . ' (' . rtrim($columns, ',') . ')'
            . ' VALUES' 
            . ' (' . rtrim($tokens, ',') . ')'; 
        
        $stmt = $this->execute($sql, $values);        
        return ($stmt instanceof PDOStatement) ? $this->db->lastInsertId(): false;
    }
        
    /**
     * @param Entity $entity
     * @return int Returns the number of rows affected by the query 
     */
    public function update(Entity $entity)
    {
        $vars = $entity->toArray();
        unset($vars['id'], $vars['created_at']);
        
        if (array_key_exists('updated_at', $vars)) {
            $vars['updated_at'] = date('Y-m-d H:i:s');
            $entity->addUpdated('updated_at');
        }
        
        $updatedColumns = $entity->getUpdated();
        if (count($updatedColumns) <= 0) {
            return false;
        }
        $entity->resetUpdated();

        $columns = '';
        $values = array();
        foreach ($updatedColumns as $column) {
            $columns .= '`' . $column . '` = ?,';
            $values[] = $vars[$column];
        }
        
        $sql = 'UPDATE ' . $this->table 
            . ' SET ' . rtrim($columns, ',')
            . ' WHERE id = ' . $entity->id;
        
        $stmt = $this->execute($sql, $values);
        return ($stmt instanceof PDOStatement) ? $stmt->rowCount() : false;
    }
    
    /**
     * @param int $id
     * @return int Returns the number of rows affected by the query
     */
    public function delete($id)
    {
        $sql = 'DELETE FROM `' . $this->table . '`'
            . ' WHERE id = ?';
        
        try {
            $stmt = $this->execute($sql, array((int)$id));
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        }
        
        return ($stmt instanceof PDOStatement) ? $stmt->rowCount() : false;
    }
    
    /**
     * Returns the total number of rows in the last executed query.
     *
     * @return int
     * @throws ModelException
     */
    public function getTotalRows()
    {
        $sql = 'SELECT FOUND_ROWS() AS rowsCount';
        
        try {
            $row = $this->execute($sql)->fetch();
            $rowsCount = 0;
            if (isset($row->rowsCount)) {
                $rowsCount = (int) $row->rowsCount;
            }
            return $rowsCount;
        } catch (Exception $e) {
            throw new ModelException($e->getMessage());
        }
    }
    
    /**
     * Adds a limit clause to a given SQL query.
     *
     * @param string $sql
     * @param array $options
     * @return string
     */
    public function limit($sql, $options)
    {
        $itemCountPerPage = isset($options['count']) ? (int) $options['count'] : $this->defaultOptions['count'];
        
        if (isset($options['page'])) {
            $offset = ((int) $options['page'] - 1) * $itemCountPerPage;
            if (false === strpos($sql, 'SQL_CALC_FOUND_ROWS')) {
                $sql = preg_replace('/SELECT/i', 'SELECT SQL_CALC_FOUND_ROWS', $sql, 1);
            }
        } else {
            $offset = isset($options['offset']) ? (int) $options['offset'] : $this->defaultOptions['offset'];
        }

        return sprintf('%s LIMIT %s, %s', $sql, $offset, $itemCountPerPage);
    }
    
    /**
     * Returns the last executed query.
     *
     * @return string
     */
    public function getQueries()
    {
        return $this->db->getQueries();
    }
    
    /**
     * Generates the pagination array.
     *
     * @param array $options array('page'=>1, 'count'=>20)
     * @return stdClass
     */
    public function paginate(array $options)
    {
        $currentPageNumber = isset($options['page']) ? (int) $options['page'] : 1;
        $itemCountPerPage = isset($options['count']) ? (int) $options['count'] : $this->defaultOptions['count'];
        
        $itemCount = $this->getTotalRows();
        $pageCount = ceil($itemCount / $itemCountPerPage);
        $pagesBefore = $currentPageNumber - 1;
        $pagesAfter = $pageCount - $currentPageNumber;
        
        $range = array();
        if ($pageCount > 15) {
            if ($pagesBefore > 7) {
                $range = array(1,2,0);
                if ($pagesAfter > 7) {
                    for ($i=($currentPageNumber-(4)); $i<$currentPageNumber; $i++) {
                        $range[] = $i;
                    }
                } else {
                    for ($i=($pageCount-11); $i<$currentPageNumber; $i++) {
                        $range[] = $i;
                    }
                }
            } else {
                for ($i=1; $i<$currentPageNumber; $i++) {
                    $range[] = $i;
                }
            }
            $range[] = $currentPageNumber;
            
            if ($pagesAfter > 7) {
                if ($pagesBefore > 7) {
                    for ($i=($currentPageNumber+1); $i<=$currentPageNumber+4; $i++) {
                        $range[] = $i;
                    }
                } else {
                    for ($i=($page+1); $i<13; $i++) {
                        $range[] = $i;
                    }
                }
                $range[] = 0;
                $range[] = $pageCount-1;
                $range[] = $pageCount;
            } else {
                for ($i=($currentPageNumber+1); $i<=$pageCount; $i++) {
                    $range[] = $i;
                }
            }
        } else {
            for ($i=1; $i<=$pageCount; $i++) {
                $range[] = $i;
            }
        }        
        
        $pages = new stdClass();
        $pages->pagesInRange = $range;
        $pages->pageCount = $pageCount;
        $pages->itemCountPerPage = $itemCountPerPage;
        $pages->current = $currentPageNumber;
        $pages->first = 1;
        $pages->last = $pageCount;
        
        // Previous and next
        if ($currentPageNumber - 1 > 0) {
            $pages->previous = $currentPageNumber - 1;
        }
        if ($currentPageNumber + 1 <= $pageCount) {
            $pages->next = $currentPageNumber + 1;
        }
        
        return $pages;
    }
}


/**
 * Base entity class
 */
class Entity
{
    private static $updated = array();
    
    /**
     * Class constructor.
     * 
     * @params array $vars key/value pairs
     * @return void
     */
    public function __construct(array $vars = null)
    {
        if (null !== $vars) {
            foreach ($vars as $key => $value) {
                $this->setProperty($key, $value);
            }
        }
    }
    
    /**
     * Sets a property value.
     *
     * @param string $name
     * @param mixed $value
     * @throws Exception
     */
    protected function setProperty($name, $value) 
    {
        if (! is_string($name)) {
            throw new EntityException('Invalid property name: '. $name);
        } else if (! property_exists($this, $name)) {
            throw new EntityException('Property not defined: ' . $name);
        }
        
        $setterMethod = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        if (method_exists($this, $setterMethod)) {
            $this->$setterMethod($value);
        } else {
            $this->$name = $value;
        }
    }
    
    /**
     * Set property value.
     * 
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        $this->setProperty($name, $value);
        $this->addUpdated($name);
    }
    
    /**
     * Support isset() overloading.
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->$name);
    }
    
    /**
     * Support unset() overloading.
     *
     * @param string $name
     * @return void
     */
    public function __unset($name)
    {
        unset($this->$name);
    }
    
    /**
     * Magic function so that $entity->value will work.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name)
    {
        return $this->$name;
    }
    
    /**
     * @return void
     */
    public function addUpdated($property)
    {
         self::$updated[] = $property;
    }
    
    /**
     * @return array
     */
    public function getUpdated()
    {
        return self::$updated;
    }
    
    /**
     * @return array
     */
    public function resetUpdated()
    {
       self::$updated = array();
    }
    
    /**
     * Return an associative array containing all of object propertiers 
     * and its values. 
     *
     * @return array
     */
    public function toArray()
    {
        return get_object_vars($this);
    }
    
    /**
     * Convert entity to an anonymous object.  
     *
     * @return stdClass
     */
    public function toObject()
    {
        return (object) $this->toArray();
    }
}