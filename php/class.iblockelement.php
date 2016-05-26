<?php
/**
 * Немного не соответствует идиологии. должно быть так:
 *
 * $iem->get($cond)->delete();
 *
 *
 */

require_once dirname(__FILE__).DIR_SEP.'class.iblockelement.model.php';

class fcIblockElement
{
	public $db_table = 'iblock_element';

	protected $_iblock_id;

	protected $_object;

	public function __construct($iblock_id = NULL)
	{
		if ($iblock_id !== NULL)
		{
			$this->_iblock_id = $iblock_id;
		}
	}

	/**
	 * Добавляет элемент яблока.
	 *
	 *		$element = new fcIblockElement($iblock['id']);
	 *
	 *		//укажем ИД родителя
	 *		$parent_id = 5;
	 *		//ИД иблока необязателен, если указан в конструкторе
	 *		$iblock_id = 1;
	 *		//значения свойств
	 *		$properties = array(
	 *			'conditions' => array('symbolcode', '=', 'name'),
	 *			'value' => 'value'
	 *		)
	 *		$element->add($properties, $parent_id, $iblock_id);
	 *
	 * @param type $properties
	 * @param type $parent
	 * @param type $iblock
	 * @return fcIblockElement
	 */
	public function add($properties, $parent = NULL, $iblock = NULL)
	{
		$this->iblock_id($iblock);

		$this->_object = fcModel::factory('iblockelement', $this)->add($parent, $this->iblock_id());

		$this->properties($properties);

		return $this;
	}

	public function delete($conditions)
	{
		$elements = $this->get($conditions);
		$ipm = new fcIBlockProperty($this->iblock_id());
		foreach($elements as $element)
		{
			$ipm->get(array('element_id', '=', $element['id']))->delete();
		}
		fcModel::factory('iblockelement', $this)->delete($conditions);
	}

	public function properties($properties = NULL)
	{
		if ($properties !== NULL)
		{
			//установим значения свойств
			foreach ($properties as $property)
			{
				//условия
				$conditions = $property['conditions'];
				if ( !is_array($conditions[0]) )
				{
					$conditions = array($conditions);
				}

				$this->property($conditions, $property['value']);

			}
		}

		return $this;
	}

	public function property($conditions, $value = NULL)
	{
		if ($value !== NULL)
		{
			//создадим менеджер свойств
			$pm = new fcIblockProperty($this->iblock_id());
			//найдем нужные свойства и установим их значения
			$pm->get($conditions)->value($value, $this->_object['id']);
		}
	}

    /**
     * Возвращает элементы, удовл. условиям $conditions.
	 * Простая сортировка
	 *
	 *		// сортируем по id
	 *		$sort = array('id' => 'asc');
	 *
	 * Сортировка по свойствам.
	 *
	 *		// сортируем по значению свойства с symbolcode.
	 *		$sort = array('property.symbolcode' => 'asc')
	 *
     * @param array $conditions	Условия выборки
	 * @param array $sort	Условия сортировки
     * @return array	Массив элементов
     */
	public function get($conditions = NULL, $sort = array() )
	{
		if ($this->iblock_id())
		{
            $conditions = (is_array($conditions[0]) || $conditions == NULL)? $conditions: array($conditions);
			$conditions[] = array('iblock_id', '=', $this->iblock_id());
		}

		$elements = fcModel::factory('iblockelement', $this)->get($conditions, $sort);

		//свойства
		$ipm = new fcIblockProperty($this->iblock_id());
		foreach($elements as $i => $element)
		{
			$properties = $ipm->get(array('element_id', '=', $element['id']))->value();
			foreach($properties as $property)
			{
				$sc = empty($property['symbolcode'])? $property['id']: $property['symbolcode'];
				$elements[$i]['properties'][$sc] = $property;
			}
		}

		return $elements;
	}

	public function iblock_id($iblock_id = NULL)
	{
		if ($iblock_id !== NULL)
		{
			$this->_iblock_id = $iblock_id;
			return $this;
		}
		else
		{
			return $this->_iblock_id;
		}
	}

	public function id()
	{
		if (isset($this->_object['id']))
		{
			return $this->_object['id'];
		}
		else
			return NULL;
	}

	public function install()
	{
		fcModel::factory('iblockelement', $this)->create_tables();
	}
}

?>