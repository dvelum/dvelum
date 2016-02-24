<?php
class Db_Select_Filter
{
	const GT = '>';
	const LT = '<';
	const GT_EQ = '>=';
	const LT_EQ = '<=';
	const EQ = '=';
	const NOT_NULL = 'IS NOT NULL';
	const IS_NULL = 'IS NULL';
	const NOT = '!=';
	const IN = 'IN';
	const NOT_IN = 'NOT IN';
	const LIKE = 'LIKE';
	const NOT_LIKE = 'NOT LIKE';
	const BETWEEN = 'BETWEEN';
	const NOT_BETWEEN = 'NOT BETWEEN';

	public $type = null;
	public $value = null;
	public $field = null;
	/**
	 * @param string $field
	 * @param string $type
	 * @param mixed $value
	 */
	public function __construct($field ,  $value = '' , $type = self::EQ)
	{
		$this->type = $type;
		$this->value = $value;
		$this->field = $field;
	}

	/**
	 * Apply filter
	 * @param Zend_Db_Adapter_Abstract $db
	 * @param Db_Select | Zend_Db_Select $sql
	 * @throws Exception
	 */
	public function applyTo( Zend_Db_Adapter_Abstract $db , $sql)
	{
		if(!($sql instanceof Db_Select) && !($sql instanceof Zend_Db_Select))
			throw new Exception('Db_Select_Filter::applyTo  $sql must be instance of Db_Select/Zend_Db_Select');


		$quotedField = $db->quoteIdentifier($this->field);
		switch ($this->type)
		{
			case self::LIKE:
			case self::NOT_LIKE:
				if(is_array($this->value)) {
					$conditions = array();
					foreach ($this->value as $k => $v) {
						$quotedField = $db->quoteIdentifier($k);
						$conditions[] = $quotedField . ' LIKE ' . $db->quote('%' . $v . '%');
					}
					if ($this->type == self::LIKE)
						$condition = implode(' OR ', $conditions);
					else
						$condition = implode(' AND ', $conditions);
					$condition = '('.$condition.')';
					$sql->where($condition);
					break;
				}
			case self::LT:
			case self::GT:
			case self::EQ:
			case self::GT_EQ:
			case self::LT_EQ:
			case self::NOT:
				$sql->where($quotedField . ' ' . $this->type . ' ?' , $this->value);
				break;
			case self::IN:
			case self::NOT_IN:
				$sql->where($quotedField . ' ' . $this->type . ' (?)' , $this->value);
				break;
			case self::NOT_NULL :
			case self::IS_NULL :
				$sql->where($quotedField . ' ' . $this->type);
				break;
			case self::BETWEEN:
			case self::NOT_BETWEEN:
				$sql->where($quotedField . ' ' . $this->type . ' ' . $db->quote($this->value[0]) . ' AND ' . $db->quote($this->value[1]));
				break;
		}
	}
}