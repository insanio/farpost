<?php
/**
 *
 *
 *
 */

class fcIBlockPropertyBase
{
	protected $db_table = '';

	protected $_property_id;
	protected $_element_id;

	protected $_object = array();

	public $type = '';

	public function __construct($property_id = NULL, $element_id = NULL)
	{
		$this->property_id($property_id);
		$this->element_id($element_id);

		return $this;
	}

	public function delete()
	{
        if (count($this->_object) > 0)
        {
            $q = fcDB::delete($this->db_table);

            foreach($this->_object as $property)
            {
                $q->or_where('id', '=', $property['id']);
            }
            $q->execute();
        }

		$this->_object = array();

		return $this;
	}

	public function element_id($element_id = NULL)
	{
		if ($element_id !== NULL)
		{
			$this->_element_id = $element_id;
			return $this;
		}
		else
		{
			return $this->_element_id;
		}
	}

	public function get($conditions = NULL)
	{
		$q = fcDB::select()
				->from($this->db_table);

		$property_id = $this->property_id();
		$element_id = $this->element_id();

		if ($property_id !== NULL)
		{
			$q->and_where('property_id', '=', $property_id);
		}

		if ($element_id !== NULL)
		{
			$q->and_where('element_id', '=', $element_id);
		}

		if ($conditions !== NULL && !empty($conditions))
		{
			//если первое значение не массив
			if (! is_array($conditions[0]))
			{
				$conditions = array($conditions);
			}

			foreach($conditions as $i=>$condition)
			{
				$q->and_where($condition[0], $condition[1], $condition[2]);
			}

		}

        $this->_object = $q->execute()->as_array();

		return $this;
	}

	public function get_value()
	{
		if ($this->_object)
		{
			// когда нужно вернуть значение, а выбрано больше 1 объекта, мы используем только 1 значение первого объекта
			$r = count($this->_object) >= 1? $this->_object[0]: $this->_object;
			return $r;
		}

		return false;
	}

	public function is_empty()
	{
		if (count($this->_object) > 0)
		{
			return false;
		}

		return true;
	}

	/**
	 * Обрабатывает значение свойства перед добавлением в БД
	 * @param mixed $value
	 * @return mixed
	 */
	public function process($value)
	{
		return $value;
	}

	public function property_id($property_id = NULL)
	{
		if ($property_id !== NULL)
		{
			$this->_property_id = $property_id;
			return $this;
		}
		else
		{
			return $this->_property_id;
		}
	}

	/**
	 * Добавляет значение свойства.
	 * @param type $iblock_id
	 * @param type $element_id
	 * @param type $value
	 */
	public function value($value = NULL, $property_id = NULL, $element_id = NULL, $multiple = false)
	{
		$this->property_id($property_id);
		$this->element_id($element_id);

		// если установлены $property_id, $element_id - то значит не было предварительной выборки, значит выбираем
		// свойства, которым нужно присвоить значения здесь
		if ($property_id != NULL && $element_id != NULL)
		{
			$this->get();
		}

		$value_ex = $this->get_value();

		if ($value === NULL)
		{
			return $value_ex;
		}
		else
		{
			if (! $multiple && $value_ex)
			{
				$this->delete();
			}

			$value = $this->process($value);

			$q = fcDB::insert($this->db_table);
			$q->columns(array(
				'property_id',
				'element_id',
				'value'
			));

			$q->values(array(
				$this->property_id(),
				$this->element_id(),
				$value
			));

			$r = $q->execute();

			return $r[0];
		}
	}

	public function db_table()
	{
		return $this->db_table;
	}

}
?>