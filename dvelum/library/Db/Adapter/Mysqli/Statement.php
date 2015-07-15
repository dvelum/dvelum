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
                    /**
                     * @see Zend_Db_Statement_Exception
                     */
                    require_once 'Zend/Db/Statement/Exception.php';
                    throw new Zend_Db_Statement_Exception("Invalid bind-variable position '$val'");
                }
            }
            $this->_sqlParam[] = $val;
        }

        // set up for binding
        $this->_bindParam = array();
    }
}