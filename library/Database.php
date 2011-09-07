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
 * @package     Database
 * @author      Federico Cargnelutti <federico@kewnode.com>
 * @copyright   Copyright (c) 2011 Kewnode Ltd.
 * @version     $Id: $
 */
class Database
{
    /**
     * @var $pdo PDO 
     */
    protected $pdo;

    /**
     * @var array
     */
    protected $queries = array();

    /**
     * Creates a PDO instance representing a connection to a database.
     *
     * @param string $host The host name
     * @param string $name The database name
     * @param string|false $user The user name for the DSN string. This parameter is optional for some PDO drivers.
     * @param string|false $pass The password for the DSN string. This parameter is optional for some PDO drivers.
     * @param array $options A key=>value array of driver-specific connection options
     * @return PDO
     * @throws Exception
     */
    public function __construct($host, $name, $user = false, $pass = false, $options = array())
    {
        if (!extension_loaded('pdo')) {
            throw new PDOException(__CLASS__ . ': The PDO extension is required for this class');
        }
        
        $dsn = sprintf('mysql:host=%s;dbname=%s', $host, $name);
        $this->pdo = new PDO($dsn, $user, $pass, $options);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Initiates a transaction
     *
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Commits a transaction
     *
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * Fetch the SQLSTATE associated with the last operation on the database handle
     *
     * @return string
     */
    public function errorCode()
    {
        return $this->pdo->errorCode();
    }

    /**
     * Fetch extended error information associated with the last operation on the database handle
     *
     * @return array
     */
    public function errorInfo()
    {
        return $this->pdo->errorInfo();
    }

    /**
     * Execute an SQL statement and return the number of affected rows
     *
     * @param string $stm
     */
    public function exec($stm)
    {
        return $this->pdo->exec($stm);
    }

    /**
     * Set an attribute
     *
     * @param int $attribute
     * @param mixed $value
     * @return bool
     */
    public function setAttribute($attribute, $value)
    {
        return $this->pdo->setAttribute($attribute, $value);
    }
    
    
    /**
     * Retrieve a database connection attribute
     *
     * @param int $attribute
     * @return mixed
     */
    public function getAttribute($attribute)
    {
        return $this->pdo->getAttribute($attribute);
    }

    /**
     * Returns the ID of the last inserted row or sequence value
     *
     * @param null|string $name Name of the sequence object from which the ID should be returned.
     * @return string
     */
    public function lastInsertId($name = null)
    {
        return $this->pdo->lastInsertId($name);
    }

    /**
     * Builds a prepare statement and returns a statement object.
     *
     * @param string $sql A valid SQL statement for the target database server
     * @return PDOStatement
     */
    public function prepare($sql)
    {
        return $this->pdo->prepare($sql);
    }

    /**
     * Executes an SQL statement, returning a result set as a PDOStatement object
     *
     * @param string $stm
     * @return PDOStatement
     */
    public function query($stm)
    {
        return $this->pdo->query($stm);
    }
    
    /**
     * Execute query and select one column only
     *
     * @param string $stm
     * @return mixed
     */
    public function fetchColumn($stm)
    {
        return $this->pdo->query($stm)->fetchColumn();
    }

    /**
     * Quotes a string for use in a query
     *
     * @param string $input
     * @param int $paramType
     * @return string
     */
    public function quote($input, $paramType = 0)
    {
        return $this->pdo->quote($input, $paramType);
    }

    /**
     * Rolls back a transaction
     *
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * Adds an executed SQL statement.
     *
     * @return void
     */
    public function addQuery($string)
    {
        return $this->queries[] = $string;
    }
    
    /**
     * Returns a list of executed SQL statements.
     *
     * @return array
     */
    public function getQueries()
    {
        return $this->queries;
    }
}