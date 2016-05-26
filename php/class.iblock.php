<?php
defined('SYSPATH') or die('No direct script access.');
/**
 * #Модуль Яблок
 *
 * Нужно немного переписать, для более прозрачной идиологии.
 *
 * Сейчас:
 *
 *  $em = new CIblockElement($iblock_id);
 *  $conditions = array( ['id', '=', '1'] );
 *  $limit = 10;
 *
 *  $el = $em->get($conditions, $limit);
 *
 *  А правильнее:
 *
 *  $el = $em->conditions($conditions)->limit($limit)->get();
 *
 *
 *
 *
 * @version 0.2
 *
 * @changelog fixed error, when deleting property of the element deleted properties of all elements
 *
 * ##Работа с иблоками.
 *
 * Создадим менеджер иблоков.
 *
 *		$iblock = new fcIblock;
 *
 * Чтобы добавить иблок, нужно
 *
 *		$iblock->add(array('name' => 'имя иблока'));
 *
 *	Массив параметров, содержит поля:
 *		array(
 *			'name',
 *			'symbolcode',
 *			'active',
 *			'sort',
 *			'description',
 *			'date_modification'
 *		)
 *
 *	Обязательно только name, т.е. достаточно так
 *
 *		$iblock->add(array('name' => 'iblock name'));
 *
 *	Чтобы получить iblock
 *
 *		$iblock->get($conditions);
 *
 *	$conditions - это массив условий (как и везде далее), например
 *
 *		$conditions = array(array('id', '=', '1'), array('name', '>=', 'iblock name'));
 *		$iblocks = $iblock->get($conditions);
 *
 *
 *	## Работа с элементами
 *
 *	Сначала создадим менеджер элементов.
 *	Первый параметр - это ИД иблока, но он не обязателен. Если он указан, то он сохраняется в менеджере,
 *	для дальнейшей работы. Но если он не указан, его можно принудительно указать во всех методах менеджера.
 *
 *		$element = new fcIblockElement($iblock['id']);
 *
 *	Добавим элемент
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
 *  Удалим элемент. Вместе с элементом удаляется и все его дочерние узлы. То есть, удаляется вся ветка.
 *
 *		$element->delete($conditions);
 *
 *  ## Работа со свойствами и их значениями
 *
 *  Каждый элемент имеет набор свойств. Есть свойства по-умолчанию. Они добавляются к каждому элементу всегда.
 *	Это:
 *		 @todo перечислить дефолтные свойства.
 *
 *	###"Админские" функции
 *
 *	Добавим свойство иблоку.
 *
 *		//менеджер свойств
 *		$pm = new fcIblockProperty($iblock_id);
 *
 *		//свойства свойства. То есть параметры свойства, которые описывают его. Имеет сл. поля.
 *		//обязательные $meta[name] и $meta[iblock_id]
 *		$meta = array(
 *					'name' => 'property name',
 *					'symbolcode',
 *					'active',
 *					'sort',
 *					'multiple',
 *					'required',
 *					'type',
 *					'iblock_id' => 2
 *				);
 *
 *		$pm->add($meta);
 *
 *	Удалим свойство иблока
 *
 *		$conditions = array('symbolcode', '=', 'old symbolcode');
 *		$pm->delete($conditions);
 *
 *	###"Пользовательские" функции
 *
 *  Установим значение свойства.
 *
 *  Либо
 *
 *		$pm->value('value', $element_id, $property_id);
 *
 *	Либо
 *		$conditions = array('symbolcode', '=', 'title');
 *		$pm->get($conditions)->value('value');
 *
 *
 *  Получим значение свойства для элемента с ИД = 1.
 *
 *		$conditions = array(array('symbolcode', '=', 'title'), array('element_id', '=', '1'));
 *		$properties = $pm->get($conditions)->value();
 *
 *	Или
 *		$pm->element_id(1);
 *		$conditions = array(array('symbolcode', '=', 'title'));
 *		$properties = $pm->get($conditions)->value();
 *
 *
 */
require_once dirname(__FILE__).DIR_SEP.'class.iblock.model.php';

class fcIblock
{

	public $db_table = 'iblock';

	public function __construct()
	{
		;
	}

	/**
	 *
	 */
	public function add($iblock)
	{
		fcModel::factory('iblock',$this)->add(
			$iblock['name'],
			$iblock['symbolcode'],
			$iblock['active'],
			$iblock['sort'],
			$iblock['description'],
			$iblock['date_modification']
		);

		return $this;
	}

	public function delete($conditions)
	{
		return fcModel::factory('iblock', $this)->delete($conditions);
	}

	/**
	 * Устанавливает яблоки в систему:
	 *
	 *  - Создает таблицу яблока
	 *  - Создает таблицу элементов
	 *  - Создает таблицу свойств
	 *  - Создает таблицы значений свойств
	 */
	public function install()
	{
		// таблица яблока
		fcModel::factory('iblock', $this)->create_tables();

		$iem = new fcIblockElement;
		$iem->install();

		$ipm = new fcIBlockProperty;

		$ipm->install();
	}

	/**
	 *
	 * Должно быть так:
	 *
	 *		$iblock->get(array(
	 *			'name', '=', 'test'
	 *		));
	 *
	 * @param array $condition
	 * @return array
	 */
	public function get($conditions = array())
	{
		return fcModel::factory('iblock', $this)->get($conditions);
	}

}