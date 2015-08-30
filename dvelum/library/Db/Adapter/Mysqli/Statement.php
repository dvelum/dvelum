<?php
class Db_Adapter_Mysqli_Statement extends Zend_Db_Statement_Mysqli
{
    /**
     * @param string $sql
     * @return void
     */
    protected function _parseParameters($sql)
    {
        $sql = $this->_stripQuoted($sql);

        // split into text and params
        $this->_sqlSplit = preg_split('/(\?[a-zA-Z0-9_]+)/',
            $sql, -1, PREG_SPLIT_DELIM_CAPTURE|PREG_SPLIT_NO_EMPTY);

        // map params
        $this->_sqlParam = array();
        foreach ($this->_sqlSplit as $key => $val) {
            if ($val == '?') {
                if ($this->_adapter->supportsParameters('positional') === false) {
                    throw new Zend_Db_Statement_Exception("Invalid bind-variable position '$val'");
                }
            }
            $this->_sqlParam[] = $val;
        }
        // set up for binding
        $this->_bindParam = array();
    }


    /**
     * Returns an array containing all of the result set rows.
     * [DVelum performance patch]
     * @param int $style OPTIONAL Fetch mode.
     * @param int $col   OPTIONAL Column number, if fetch mode is by column.
     * @return array Collection of rows, each in a format by the fetch mode.
     */
    public function fetchAllAssoc()
    {
        $data = array();
        if ($this->_stmt) {
            $this->_stmt->execute();
            $result = $this->_stmt->get_result();
            if($result)
                $data = $result->fetch_all(MYSQLI_ASSOC);
        }
        return $data;
    }

    /**
     * Executes a prepared statement.
     * [DVelum performance patch]
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     * @throws Zend_Db_Statement_Mysqli_Exception
     */
    public function _executeFast(array $params = null)
    {
        if (!$this->_stmt) {
            return false;
        }

        // if no params were given as an argument to execute(),
        // then default to the _bindParam array
        if ($params === null) {
            $params = $this->_bindParam;
        }
        // send $params as input parameters to the statement
        if ($params) {
            array_unshift($params, str_repeat('s', count($params)));
            $stmtParams = array();
            foreach ($params as $k => &$value) {
                $stmtParams[$k] = &$value;
            }
            call_user_func_array(
                array($this->_stmt, 'bind_param'),
                $stmtParams
            );
        }

        // execute the statement
        $retval = $this->_stmt->execute();
        if ($retval === false) {
            /**
             * @see Zend_Db_Statement_Mysqli_Exception
             */
            throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement execute error : " . $this->_stmt->error, $this->_stmt->errno);
        }

        // retain metadata
        if ($this->_meta === null) {
            $this->_meta = $this->_stmt->result_metadata();
            if ($this->_stmt->errno) {
                /**
                 * @see Zend_Db_Statement_Mysqli_Exception
                 */
                throw new Zend_Db_Statement_Mysqli_Exception("Mysqli statement metadata error: " . $this->_stmt->error, $this->_stmt->errno);
            }
        }
        return $retval;
    }

    /**
     * Executes a prepared statement.
     * [DVelum performance patch]
     * @param array $params OPTIONAL Values to bind to parameter placeholders.
     * @return bool
     */
    public function executeFastAssoc(array $params = null)
    {
        /*
         * Simple case - no query profiler to manage.
         */
        if ($this->_queryId === null) {
            return $this->_executeFast($params);
        }
        /*
         * Do the same thing, but with query profiler
         * management before and after the execute.
         */
        $prof = $this->_adapter->getProfiler();
        $qp = $prof->getQueryProfile($this->_queryId);
        if ($qp->hasEnded()) {
            $this->_queryId = $prof->queryClone($qp);
            $qp = $prof->getQueryProfile($this->_queryId);
        }
        if ($params !== null) {
            $qp->bindParams($params);
        } else {
            $qp->bindParams($this->_bindParam);
        }
        $qp->start($this->_queryId);

        $retval = $this->_executeFast($params);

        $prof->queryEnd($this->_queryId);

        return $retval;
    }

}