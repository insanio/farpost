<?php

class fcIBlockProperty
{

	static function types()
	{
		$r = array(
			'S' => 'строка',
			'T' => 'текст',
			'F' => 'файл',
			'N' => 'число (с плав. точкой)',
			'D' => 'дата и время',
			'B' => 'булево'
		);

		return $r;
	}

	protected $db_table = 'iblock_properties';

	protected $_object;

	protected $_element_id;
	protected $_iblock_id;
	protected $_property_id;

	protected $_conditions;

	/**
	 * @var array	Массив полей, которые используются для описания свойства. Поля, которые не входят в этот массив,
	 *				будут отдавать в обработчики значений свойств.
	 */
	protected $_meta = array(
					'name',
					'symbolcode',
					'active',
					'sort',
					'multiple',
					'required',
					'type',
					'iblock_id'
				);

	protected function _get(& $conditions = NULL, $order_by = array())
	{
		$q = fcDB::select()
				->from($this->db_table);

		if ($this->iblock_id())
		{
			$q->and_where('iblock_id', '=', $this->iblock_id());
		}

		if ($conditions !== NULL)
		{
			//если первое значение не массив
			if (! is_array($conditions[0]))
			{
				$conditions = array($conditions);
			}

			foreach($conditions as $i=>$condition)
			{
				if (in_array($condition[0], $this->_meta) || $condition[0] == 'id')
				{
					$q->and_where($condition[0], $condition[1], $condition[2]);
					unset($conditions[$i]);
				}

				if ($condition[0] == 'element_id' && $condition[1] == '=')
				{
					$this->element_id($condition[2]);
                }
			}

            $conditions = array_values($conditions);
		}

		$q->order_by($order_by[0], $order_by[1]);

        return $q->execute()->as_array();
	}

	public function __construct($iblock_id = NULL)
	{
		$this->iblock_id($iblock_id);
	}


	/**
	 * Привязывает свойство к инфоблоку.
	 *
	 * $meta массив с описанием свойства. Обязательно только поле $meta[name]
	 *
	 * @param char $type		Тип свойства 'S', 'N', 'F', 'T', 'D'
	 * @param array $meta		Массив со свойствами свойста O_O
	 * @param int  $element_id
	 * @param int  $block_id
	 */
	public function add($meta, $iblock_id = NULL)
	{
		$this->iblock_id($iblock_id);

		$iblock_id = $this->iblock_id();

		if (empty($meta['name']) || empty($iblock_id))
			return;

		$meta['symbolcode'] = $meta['symbolcode'] !== NULL ? $meta['symbolcode']: '';
		$meta['active'] = $meta['active'] !== NULL ? $meta['active']: true;
		$meta['sort'] = $meta['sort'] !== NULL ? $meta['sort']: 500;
		$meta['multiple'] = $meta['multiple'] !== NULL ? $meta['multiple']: 0;
		$meta['required'] = $meta['required'] !== NULL? $meta['required']: 0;
		$meta['type'] = $meta['type'] !== NULL? $meta['type']: $this->type;

		$meta = fcFiltering::factory($meta);
		$meta->filter(TRUE, 'trim');
		$meta->check();

		// создадим query
		$q = fcDB::insert($this->db_table)
				->columns($this->_meta)
				->values(array(
					$meta['name'],
					$meta['symbolcode'],
					$meta['active'],
					$meta['sort'],
					$meta['multiple'],
					$meta['required'],
					$meta['type'],
					$iblock_id
				));

		//выполним его
		$r = $q->execute();

		$this->_property_id = $r[0];

		return $this;
	}

	/**
	 * Удаляет значения свойств и само свойство из реестра.
	 *
	 * @param bool $delete_meta признак, нужно ли удалять мета инфо из реестра свойств
	 * @return fcIBlockProperty
	 */
	public function delete($delete_meta = false)
	{
		if (!empty($this->_object))
		{
			foreach($this->_object as $object)
			{
				$model_class = $this->get_model_class($object['type']);

				if ($model_class)
				{
					$model = new $model_class($object['id']);
					$model->get($this->_conditions)->delete();
				}

				// если нужно удалить свойство из реестра яблока
				if ($delete_meta)
				{
					// то удаляем
					$q = fcDB::delete($this->db_table)->where('id', '=', $object['id']);
					$q->execute();
				}
			}
		}

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

	public function get($conditions = NULL, $order_by = array('sort', 'asc'))
	{
		//выбираем все свойства, которые соответсвуют условиям.
		//здесь выбираются только свойства, без привязки к элементам.
		//нужные условия удаляеются из массива $conditions
		$properties = $this->_get($conditions, $order_by);

		$this->_object = $properties;

		//сохраним условия, которые нужны для выбора элементов
		$this->_conditions = $conditions;

		return $this;
	}

	public function get_model_class($type)
	{
		switch ($type)
		{
			case 'S':
				$model_class = 'fcIBlockPropertyString';
				break;
			case 'T':
				$model_class = 'fcIBlockPropertyText';
				break;
			case 'F':
				$model_class = 'fcIBlockPropertyFile';
				break;
			case 'N':
				$model_class = 'fcIBlockPropertyNumber';
				break;
			case 'D':
				$model_class = 'fcIBlockPropertyDateTime';
				break;
			case 'B':
				$model_class = 'fcIBlockPropertyBool';
				break;
			default:
				return false;
		}

		return $model_class;
	}

    public function table($type = NULL)
    {
        if ($type == NULL)
            $type = $this->_object[0]['type'];

        switch ($type)
        {
            case 'S':
                $table = 'iblock_property_string_values';
                break;
            case 'T':
                $table = 'iblock_property_text_values';
                break;
            case 'F':
                $table = 'iblock_property_file_values';
                break;
            case 'N':
                $table = 'iblock_property_number_values';
                break;
            case 'D':
                $table = 'iblock_property_datetime_values';
                break;
            case 'B':
                $table = 'iblock_property_bool_values';
                break;
            default:
                return false;
        }

        return $table;

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

	/**
	 * Создает таблицы в БД
	 */
	public function install()
	{

		$q = new fcQuery(NULL, "DROP TABLE IF EXISTS $this->db_table");
        $q->execute();

        //создаем таблицу, если её еще нет
        $q = new fcQuery(NULL, "CREATE TABLE  `$this->db_table` (
				`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
				`active` tinyint(1) DEFAULT NULL,
				`sort` int(10) unsigned DEFAULT NULL,
				`name` varchar(255) DEFAULT NULL,
				`symbolcode` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
				`multiple` tinyint(1) DEFAULT NULL,
				`required` tinyint(1) DEFAULT NULL,
				`type` char(1) CHARACTER SET utf8 DEFAULT NULL,
				`iblock_id` int(10) unsigned NOT NULL,
				PRIMARY KEY (`id`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='реестр всех свойств иблоков типа \"строка\" (string)';");
        $q->execute();

		$types = fcIBlockProperty::types();
		foreach ($types as $c => $type)
		{
			$class = $this->get_model_class($c);

			$m = new $class;

			$m->install();
		}

	}

	/**
	 * Возвращает мета информацию о свойствах яблока. Мета информация - это техническая информация, нужная для работы
	 * со свойствами в админке и тд.
	 */
	public function meta()
	{
		return $this->_object;
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
	 * Возвращает данные (таблицу и тд) для построения запроса с сортировкой по этому полю.
	 */
	public function sort()
	{
		$r = array();

		$model_class = $this->get_model_class($this->_object[0]['type']);

		if ($model_class)
		{
			$model = new $model_class($this->_object[0]['id']);

			$r['join'] = $model->db_table();
			$r['on'] = 'element_id';
		}

		return $r;
	}

    /**
     * Возвращает тип выбранного свойства.
     */
    public function type()
    {
        return $this->_object[0]['type'];
    }

	/**
	 * Обновляет meta данные выбранных свойств указанным значением.
	 * Например,
	 *
	 *		$ipm = new fcIblockProperty;
	 *
	 *		$p = $ipm->get( array(
	 *			array('element_id', '=', '5'),
	 *			array('symbolcode', '=', 'property#1')
	 *		));
	 *
	 *		$p->update(array('value' => 'new_value');
	 *
	 *		value - это поле из таблицы значений.
	 *
	 *
	 * @param array $value массив вида array('field_name' => 'value');
	 */
	public function update($value)
	{
		$q = fcDB::update($this->db_table);

		// we update can't update primary key
		unset($value['id']);

		foreach($this->_object as $property)
		{
			$q = $q->set($value)->where('id', '=', $property['id']);
			$q->execute();
		}

		return $this;
	}


	/**
	 * Устанавливает или возвращает значение свойства.
	 * 
	 * @param mixed $value
	 * @param int $element_id
	 * @param int $property_id
	 * @return fcIBlockProperty
	 * @throws fcException
	 */
	public function value($value = NULL, $element_id = NULL, $property_id = NULL)
	{
		$this->property_id($property_id);
		$this->element_id($element_id);

		if ($value === NULL)
		{
			$result = array();
			foreach($this->_object as $i => $property)
			{
				$model_class = $this->get_model_class($property['type']);

				if ($model_class)
				{
					$model = new $model_class($property['id']);

					$conditions = isset($this->_conditions)? $this->_conditions: NULL;
					$pm = $model->get($conditions);

					$index = !empty($property['symbolcode'])? $property['symbolcode']: $property['id'];

					$result[$index] = $property;
					//сократим ненужную информацию
					$value = $pm->value();
					$result[$index]['value'] = $value['value'];
				}
				else
				{
					throw new fcException('Unknown type of property :type', array(':type', $property['type']));
				}
			}
			return $result;
		}
		else
		{
			//установлены переменные $element_id, $property_id, значит ищем это свойство
			if ($property_id !== NULL)
			{
				$properties = fcDB::select()->from($this->db_table)->where('id', '=', $this->property_id())->execute()->as_array();
			}
			else
			{
				//если нет, то используем выбранные заранее свойства и сохраненные в $this->_object
				$properties = $this->_object;
			}

			foreach($properties as $property)
			{

				$model_class = $this->get_model_class($property['type']);

				if ($model_class)
				{
					$model = new $model_class;

					$model->value($value, $property['id'], $this->element_id(), $property['multiple']);
				}
				else
				{
					throw new fcException('Unknown type of property :type', array(':type', $property['type']));
				}
			}
			return $this;
		}
	}

}
?>