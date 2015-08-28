<?php
/**
 * Extended Misqli adapter
 * Error handler implementation, performance patch, Db_Select , Light Version
 * @author Kirill A Egorov
 * @package DB
 * @uses Zend_Db_Adapter_Mysqli
 */
class Db_Adapter_Mysqli extends Zend_Db_Adapter_Mysqli
{
	protected $_connectionErrorHandler = null;
	protected $_defaultStmtClass = 'Db_Adapter_Mysqli_Statement';

	 /**
     * Constructor.
     *
     * $config is an array of key/value pairs
     * containing configuration options.  These options are common to most adapters:
     *
     * dbname         => (string) The name of the database to user
     * username       => (string) Connect to the database as this username.
     * password       => (string) Password associated with the username.
     * host           => (string) What host to connect to, defaults to localhost
     *
     * Some options are used on a case-by-case basis by adapters:
     *
     * port           => (string) The port of the database
     * persistent     => (boolean) Whether to use a persistent connection or not, defaults to false
     * protocol       => (string) The network protocol, defaults to TCPIP
     * caseFolding    => (int) style of case-alteration used for identifiers
     *
     * @param  array $config An array having configuration data
     * @throws Zend_Db_Adapter_Exception
     */
	public function __construct($config)
	{
		/*
		 * Verify that adapter parameters are in an array.
		*/
		if (!is_array($config))
			throw new Zend_Db_Adapter_Exception('Adapter parameters must be in an array or a Zend_Config object');

		$this->_checkRequiredOptions($config);

		$options = array(
				Zend_Db::CASE_FOLDING           => $this->_caseFolding,
				Zend_Db::AUTO_QUOTE_IDENTIFIERS => $this->_autoQuoteIdentifiers,
				Zend_Db::FETCH_MODE             => $this->_fetchMode,
		);

		$driverOptions = array();

		if (isset($config['driver_options']))
		{
			if (!empty($config['driver_options']))
			{
				// can't use array_merge() because keys might be integers
				foreach ((array) $config['driver_options'] as $key => $value)
				{
					$driverOptions[$key] = $value;
				}
			}
		}

		if (!isset($config['charset']))
			$config['charset'] = null;

		if (!isset($config['persistent']))
			$config['persistent'] = false;

		$this->_config = array_merge($this->_config, $config);
		$this->_config['options'] = $options;
		$this->_config['driver_options'] = $driverOptions;
		$this->setProfiler(false);
	}

	public function setConnectionErrorHandler($function)
	{
		if(!is_callable($function))
			throw new Exception('Zend_Db_Adapter_Mysqli_Extended::setConnection passed argument is not callable');
		$this->_connectionErrorHandler = $function;
	}

	protected function _connect()
	{
		if ($this->_connection)
            return;

		try{
			parent::_connect();
		}
		catch (Zend_Db_Adapter_Mysqli_Exception $e)
		{
			if(!is_null($this->_connectionErrorHandler)){
				/**
				 * @var callable $f
				 */
				$f = $this->_connectionErrorHandler;
				$f($e);
			}
			throw $e;
		}
	}

 	/**
 	 * Performance patch
 	 *
     * Quote an identifier.
     *
     * @param  string $value The identifier or expression.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifier($value, $auto=false)
    {
        if ($auto === false || $this->_autoQuoteIdentifiers === true) {
            return ('`' . str_replace('`', '', $value) . '`');
        }
        return $value;
    }

	/**
	 * Performance patch
	 *
     * Quote an identifier and an optional alias.
     *
     * @param string|array|Zend_Db_Expr $ident The identifier or expression.
     * @param string $alias An optional alias.
     * @param boolean $auto If true, heed the AUTO_QUOTE_IDENTIFIERS config option.
     * @param string $as The string to add between the identifier/expression and the alias.
     * @return string The quoted identifier and alias.
     */
    protected function _quoteIdentifierAs($ident, $alias = null, $auto = false, $as = ' AS ')
    {
        if($ident instanceof Zend_Db_Expr)
        {
            $quoted = $ident->__toString();
        }
        elseif($ident instanceof Db_Select)
        {
            $quoted = '(' . $ident->assemble() . ')';
        }
        elseif(is_string($ident))
        {
        	$quoted = '`'.str_replace(array('`','.'), array('','`.`'), $ident).'`';
        }
        elseif(is_array($ident))
        {
           $segments = array();
           foreach ($ident as $segment)
           {
              if($segment instanceof Zend_Db_Expr)
              {
                 $segments[] = $segment->__toString();
              }
              else
              {
                 $segments[] = $this->_quoteIdentifier($segment, $auto);
              }
            }

            if($alias !== null && end($ident) == $alias)
            {
               $alias = null;
            }

            $quoted = implode('.', $segments);
        }
        else
        {
                $quoted = $this->_quoteIdentifier($ident, $auto);
        }


        if($alias !== null)
        {
            $quoted.= $as . $this->_quoteIdentifier($alias, $auto);
        }

        return $quoted;
    }
    /**
     * @return Db_Select
     */
    public function select()
    {
    	return new Db_Select();
    }

   /**
    * (non-PHPdoc)
    * @see Zend_Db_Adapter_Abstract::_checkRequiredOptions()
    */
    protected function _checkRequiredOptions(array $config)
    {
    	if (!isset($config['dbname']))
    		throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'dbname' that names the database instance");

    	if (!isset($config['password']))
    		throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'password' for login credentials");

    	if (!isset($config['username']))
    		throw new Zend_Db_Adapter_Exception("Configuration array must have a key for 'username' for login credentials");
    }
    /**
     * Disable special profilers
     * (non-PHPdoc)
     * @see Zend_Db_Adapter_Abstract::setProfiler()
     */
    public function setProfiler($profiler)
    {
        $this->_profiler = new $this->_defaultProfilerClass();
        $this->_profiler->setEnabled(false);
        return $this->_profiler;
    }

    /**
     * Fetches all SQL result rows as a sequential array.
     * Uses the current fetchMode for the adapter.
     *
     * @param string|Zend_Db_Select $sql  An SQL SELECT statement.
     * @param mixed                 $bind Data to bind into SELECT placeholders.
     * @param mixed                 $fetchMode Override current fetch mode.
     * @return array
     */
    public function fetchAll($sql, $bind = array(), $fetchMode = null)
    {
        if ($fetchMode === null) {
            $fetchMode = $this->_fetchMode;
        }
        // DVelum performance patch
        if($fetchMode === Zend_Db::FETCH_ASSOC && extension_loaded('mysqlnd')){
            $stmt = $this->queryAll($sql, $bind);
            $result = $stmt->fetchAllAssoc();
        }else{
            $stmt = $this->query($sql, $bind);
            $result = $stmt->fetchAll($fetchMode);
        }

        return $result;
    }

    /**
     * Prepares and executes an SQL statement with bound data.
     * [DVelum performance patch]
     * @param  mixed  $sql  The SQL statement with placeholders. May be a string or Zend_Db_Select.
     * @param  mixed  $bind An array of data to bind to the placeholders.
     * @return Zend_Db_Statement_Interface
     */
    public function queryAll($sql, $bind = array())
    {
        // connect to the database if needed
        $this->_connect();

        // is the $sql a Zend_Db_Select object?
        if ($sql instanceof Zend_Db_Select) {
            if (empty($bind)) {
                $bind = $sql->getBind();
            }

            $sql = $sql->assemble();
        }

        // make sure $bind to an array;
        // don't use (array) typecasting because
        // because $bind may be a Zend_Db_Expr object
        if (!is_array($bind)) {
            $bind = array($bind);
        }
        // prepare and execute the statement with profiling
        $stmt = $this->prepare($sql);
        $stmt->executeFastAssoc($bind);
        // return the results embedded in the prepared statement object
        $stmt->setFetchMode($this->_fetchMode);
        return $stmt;
    }

    /**
     * Returns the column descriptions for a table.
     *
     * The return value is an associative array keyed by the column name,
     * as returned by the RDBMS.
     *
     * The value of each array element is an associative array
     * with the following keys:
     *
     * SCHEMA_NAME      => string; name of database or schema
     * TABLE_NAME       => string;
     * COLUMN_NAME      => string; column name
     * COLUMN_POSITION  => number; ordinal position of column in table
     * DATA_TYPE        => string; SQL datatype name of column
     * DEFAULT          => string; default expression of column, null if none
     * NULLABLE         => boolean; true if column can have nulls
     * LENGTH           => number; length of CHAR/VARCHAR
     * SCALE            => number; scale of NUMERIC/DECIMAL
     * PRECISION        => number; precision of NUMERIC/DECIMAL
     * UNSIGNED         => boolean; unsigned property of an integer type
     * PRIMARY          => boolean; true if column is part of the primary key
     * PRIMARY_POSITION => integer; position of column in primary key
     * IDENTITY         => integer; true if column is auto-generated with unique values
     *
     * @param string $tableName
     * @param string $schemaName OPTIONAL
     * @return array
     */
    public function describeTable($tableName, $schemaName = null)
    {
        /**
         * @todo  use INFORMATION_SCHEMA someday when
         * MySQL's implementation isn't too slow.
         */
        if ($schemaName) {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier("$schemaName.$tableName", true);
        } else {
            $sql = 'DESCRIBE ' . $this->quoteIdentifier($tableName, true);
        }
        /**
         * Use mysqli extension API, because DESCRIBE doesn't work
         * well as a prepared statement on MySQL 4.1.
         */
        if ($queryResult = $this->getConnection()->query($sql)) {
            while ($row = $queryResult->fetch_assoc()) {
                $result[] = $row;
            }
            $queryResult->close();
        } else {
            /**
             * @see Zend_Db_Adapter_Mysqli_Exception
             */
            //require_once 'Zend/Db/Adapter/Mysqli/Exception.php';
            throw new Zend_Db_Adapter_Mysqli_Exception($this->getConnection()->error);
        }
        $desc = array();
        $row_defaults = array(
            'Length'          => null,
            'Scale'           => null,
            'Precision'       => null,
            'Unsigned'        => null,
            'Primary'         => false,
            'PrimaryPosition' => null,
            'Identity'        => false
        );
        $i = 1;
        $p = 1;
        foreach ($result as $key => $row) {
            $row = array_merge($row_defaults, $row);
            if (preg_match('/unsigned/', $row['Type'])) {
                $row['Unsigned'] = true;
            }
            if (preg_match('/^((?:var)?char)\((\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = $matches[1];
                $row['Length'] = $matches[2];
            } else if (preg_match('/^decimal\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'decimal';
                $row['Precision'] = $matches[2];
                $row['Scale'] = $matches[1];
            }
            /*
             * DVelum fix for double database type
             */
            else if (preg_match('/^double\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'double';
                $row['Precision'] = $matches[2];
                $row['Scale'] = $matches[1];
            }
            /*
             * end fix
             */
            else if (preg_match('/^float\((\d+),(\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = 'float';
                $row['Precision'] = $matches[2];
                $row['Scale'] = $matches[1];
            } else if (preg_match('/^((?:big|medium|small|tiny)?int)\((\d+)\)/', $row['Type'], $matches)) {
                $row['Type'] = $matches[1];
                $row['Length'] = $matches[2];
                /**
                 * The optional argument of a MySQL int type is not precision
                 * or length; it is only a hint for display width.
                 * DVelum fix. But it needed for foreign keys
                 */
            }
            if (strtoupper($row['Key']) == 'PRI') {
                $row['Primary'] = true;
                $row['PrimaryPosition'] = $p;
                if ($row['Extra'] == 'auto_increment') {
                    $row['Identity'] = true;
                } else {
                    $row['Identity'] = false;
                }
                ++$p;
            }
            $desc[$this->foldCase($row['Field'])] = array(
                'SCHEMA_NAME'      => null, // @todo
                'TABLE_NAME'       => $this->foldCase($tableName),
                'COLUMN_NAME'      => $this->foldCase($row['Field']),
                'COLUMN_POSITION'  => $i,
                'DATA_TYPE'        => $row['Type'],
                'DEFAULT'          => $row['Default'],
                'NULLABLE'         => (bool) ($row['Null'] == 'YES'),
                'LENGTH'           => $row['Length'],
                'SCALE'            => $row['Scale'],
                'PRECISION'        => $row['Precision'],
                'UNSIGNED'         => $row['Unsigned'],
                'PRIMARY'          => $row['Primary'],
                'PRIMARY_POSITION' => $row['PrimaryPosition'],
                'IDENTITY'         => $row['Identity']
            );
            ++$i;
        }
        return $desc;
    }
}