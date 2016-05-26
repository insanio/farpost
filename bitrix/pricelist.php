<?

namespace Falsecode\PriceLists;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

\CModule::IncludeModule('iblock');

require_once(__DIR__.'/../tools/tools.php');

/**
 * Class PricelistTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> NAME string mandatory
 * </ul>
 *
 * @package Falsecode\Pricelists
 **/

class PricelistTable extends Entity\DataManager
{
	public static $catalogModuleInstalled = false;
	public static $processedIDS = array();

	public static $STATUS = array(
		'N' => 'Новый',
		'P' => 'В процессе',
		'S' => 'Успех',
		'E' => 'Ошибка',
		'U' => 'Обновлен'
	);

	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'fc_pricelist';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
				'title' => "ID",
			),
			'ACTIVE' => array(
				'data_type' => 'enum',
				'required' => false,
				//'validation' => array(__CLASS__, 'validateDataValue'),
				'title' => "Активность",
				'values' => array('N', 'Y'),
			),
			'NAME' => array(
				'data_type' => 'string',
				'required' => true,
				'title' => "Название",
			),
			'CHARGE' => array(
				'data_type' => 'integer',
				'required' => false,
				'title' => "Наценка",
			),
			'FILE' => array(
				'data_type' => 'integer',
				'required' => false,
				'title' => "Файл",
			),
			'STATUS' => array(
				'data_type' => 'enum',
				'required' => false,
				//'validation' => array(__CLASS__, 'validateDataValue'),
				'title' => "Статус",
				'values' => array('N', 'P', 'S', 'E', 'U'),
			),
			'COMMENT' => array(
				'data_type' => 'text',
				'required' => false,
				'title' => "Комментарий",
			),
			'PRICETYPE' => array(
				'data_type' => 'integer',
				'required' => false,
				'title' => "Цена",
			),
			'CATALOG_IBLOCK_ID' => array(
				'data_type' => 'integer',
				'required' => false,
				'title' => "Инф. блок каталога",
			),
			'SOURCE' => array(
				'data_type' => 'enum',
				'required' => false,
				'title' => "Источник",
				'values' => array('none', 'ftp', 'http', 'email')
			),
			'PERIOD' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => "Период загрузки",
			),
			'URL' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => "URL",
			),
//			'LOGIN' => array(
//				'data_type' => 'string',
//				'required' => false,
//				'title' => "Логин",
//			),
//			'PASSWORD' => array(
//				'data_type' => 'string',
//				'required' => false,
//				'title' => "Пароль",
//			),
			'CHARSET' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => "Кодировка",
			),
//			'EMAIL' => array(
//				'data_type' => 'string',
//				'required' => false,
//				'title' => "Почта",
//			),
//			'WORD_ATTACHMENT' => array(
//				'data_type' => 'string',
//				'required' => false,
//				'title' => "Вложение содержит слово",
//			),
//			'WORD_SUBJECT' => array(
//				'data_type' => 'string',
//				'required' => false,
//				'title' => "Тема содержит слово",
//			),
			'ROW_START' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => "Начинать со строки №",
			),
			'SEPARATOR' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => "Разделитель (';', '\\t')",
			),
			new Entity\StringField('CONFIG', array('size' => 9999)),
			'CURRENCY' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => 'Валюта'
			),
			'LAST_EXEC_TIME' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => 'Последнее время импорта (окончание)'
			),
			'CSV' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => 'Файл CSV (автоматическое)'
			),
			'EXCLUDE_VALUES' => array(
				'data_type' => 'string',
				'required' => false,
				'title' => 'Исключить позиции, поля которых содержат'
			),
			new Entity\StringField('NO_IN_PRICE_ACTION', array('size' => 15)),
		    new Entity\IntegerField('PRICE_INDEX'),
			new Entity\IntegerField('IBLOCK_SECTION_ID'),
			new Entity\IntegerField('CATALOG_SECTION_ID')
		);
	}

	/**
	 * Импортирует прайс.
	 *
	 * @param $arPrice array массив с данными о прайсе из таблицы
	 * @param int $start_row номер строки, с которой начинать
	 * @param int $finish_row номер строки, на которой заканчивать
	 *
	 * @throws PricelistException
	 * @return array
	 */
	public static function import($arPrice, $start_row = 0, $finish_row = 0)
	{
		$path = $_SERVER['DOCUMENT_ROOT'] . $arPrice['CSV'];
		$count = PricelistTable::importCSV($path, $arPrice, $start_row, $finish_row);
		PricelistTable::postProcessAction($arPrice);
		return $count;
	}

	public static function importCSV($source, $arPrice, $start_row = 0, $limit = 0)
	{
		$count = array('total' => 0, 'error' => 0, 'skip' => 0);
		$pid = getmypid();
		$pid_fh = fopen("{$_SERVER['DOCUMENT_ROOT']}/upload/pricelists/logs/{$arPrice['ID']}/{$pid}.pid", 'w+');

		$start_row = $start_row > $arPrice['ROW_START']? $start_row: $arPrice['ROW_START'];
		$separator = $arPrice['SEPARATOR'];
		$filetotal = filesize($source);

		$arPrice['IBLOCK_ID'] = PricelistTable::getIBlockID($arPrice);
		PricelistTable::checkProperties($arPrice['CATALOG_IBLOCK_ID'], $arPrice);

		if (($fh = fopen($source, 'r')) !== FALSE)
		{
			$line = 0;

			while($item = fgetcsv($fh, 0, $separator))
			{
				$filepos  = ftell($fh);
				$c = $filepos * 100 / $filetotal;
				fseek($pid_fh, 0);
				fputs($pid_fh, sprintf('%0.2f', $c));

				$line ++;
				if ($line < $start_row)
					continue;

				if( ($limit - $start_row) > 0)
				{
					$progress = (($line - $start_row) * 100) / ($limit - $start_row);
					fputs($pid_fh, sprintf(' (%0.2f)%%', $progress));
				}

				//limit
				if ($limit > 0 && $line > $limit)
				{
					fcAddMessage2Log("{$arPrice['NAME']}[{$arPrice['ID']}]: достигнут лимит ({$line}>{$limit}).", 'falsecode.pricelists');
					break;
				}

				$item_errors = PricelistTable::ImportItem($item, $arPrice);

				if(count($item_errors) > 0)
				{
					$count['error'] ++;
					fcAddMessage2Log("{$arPrice['NAME']}[{$arPrice['ID']}] ({$line}): ошибки при импорте позиции: " . implode(', ', $item_errors) . PHP_EOL . print_r($item, true), 'falsecode.pricelists');
				}

				$count['total'] ++;
			}
		}

		fclose($pid_fh);
		unlink("{$_SERVER['DOCUMENT_ROOT']}/upload/pricelists/logs/{$arPrice['ID']}/{$pid}.pid");

		fcAddMessage2Log("Поток (pid={$pid}) для прайслиста {$arPrice['NAME']}[{$arPrice['ID']}] закончил работу.", 'falsecode.pricelists');
		return $count;
	}

	public static function ImportItem($item, $arPrice)
	{
		$event = new \Bitrix\Main\Event("falsecode.pricelists", "OnBeforeImportItem", array('item' => $item, 'arPrice' => $arPrice));
		$event->send();

		$error = array();

		$IBLOCK_ID = $arPrice['IBLOCK_ID'];
		$ID = false;

		$exclude_values = explode(",", $arPrice['EXCLUDE_VALUES']);
		foreach($exclude_values as $i => $v)
			$exclude_values[$i] = trim($v);

		foreach($item as $k => $v)
		{
			if ($arPrice['CHARSET'] != strtolower(SITE_CHARSET))
			{
				$v = iconv($arPrice['CHARSET'], SITE_CHARSET, $v);
			}

			$v = trim($v);
			if(strlen($v) > 0)
			{
				if (strpos_arr($v, $exclude_values) !== false)
				{
					$error[] = "{$v}: содержит стоп слово";
				}
			}

			$item[$k] = $v;
		}

		//Поле NAME (индекс 1) обязательно к заполнению
		if(strlen($item[$arPrice['CONFIG']["~1"]['COLUMN']]) == 0)
			$error[] = "NAME: обязательное поле";

		//Обработка цены
		if($arPrice['PRICE_INDEX'] != 0)
		{
			$pi = $arPrice['PRICE_INDEX'];
			$pc = $arPrice['CONFIG']["~{$pi}"]['COLUMN'];
			//удаляем странные символы, которые приходят как разделители разрядов из 1С
			$item[$pc] = str_replace(array(" ", ',', "\xC2\xA0"), array("", '.', ""), $item[$pc]);
			$item[$pc] = $item[$pc] + (($item[$pc] / 100) * $arPrice['CHARGE']);
		}

		if (count($error) > 0)
			return $error;

		$arFields = array(
			'ACTIVE' => 'Y',
			'NAME' => $item[$arPrice['CONFIG']["~1"]['COLUMN']],
			'PROPERTY_VALUES' => array(
				'PRICELIST' => $arPrice['ID']
			)
		);

		$arCatalogFields = array();
		$arPriceFields = array();

		foreach($arPrice['CONFIG'] as $k => $c)
		{
			$v = $item[$arPrice['CONFIG']["~{$k}"]['COLUMN']];
			if(strlen($v) > 0)
			{
				if (strpos($c['IBLOCK_FIELD'], 'PROPERTY_') !== false)
				{
					$arFields['PROPERTY_VALUES'][str_replace('PROPERTY_', '', $c['IBLOCK_FIELD'])] = $v;
				}
				elseif (strpos($c['IBLOCK_FIELD'], 'CATALOG_') !== false)
				{
					$code = str_replace('CATALOG_', '', $c['IBLOCK_FIELD']);
					if($code == 'PRICE')
						$arPriceFields['PRICE'] = $v;
					else
						$arCatalogFields[$code] = $v;
				}
				else
				{
					$arFields[$c['IBLOCK_FIELD']] = $v;
				}
			}
		}

		$el = new \CIBlockElement;

		$sku_info = false;

		if(PricelistTable::$catalogModuleInstalled)
			$sku_info = \CCatalogSKU::GetInfoByProductIBlock($arPrice['CATALOG_IBLOCK_ID']);

		foreach($arPrice['CONFIG'] as $k => $c)
		{
			if($c['USE_UNIQUE'] == 'Y')
			{
				$arUniqueFilter[$c['IBLOCK_FIELD']] = $item[$arPrice['CONFIG']["~{$k}"]['COLUMN']];
			}
		}

		if($sku_info === false)
		{
			//простой каталог без торг. предложений
			$arFields['IBLOCK_ID'] = $IBLOCK_ID;
			if($arPrice['CATALOG_SECTION_ID'] > 0)
				$arFields['IBLOCK_SECTION_ID'] = $arPrice['CATALOG_SECTION_ID'];

			$arItem = \CIBlockElement::GetList(array(), $arUniqueFilter)->Fetch();
			if($arItem)
			{
				//у нас уже есть такой элемент, сохраним его $ID, чтобы обновить
				$ID = $arItem['ID'];
			}
		}
		else
		{
			//каталог с торг. предложениями
			//ищем номенклатуру по названию и артикулу
			$arProductUniqueFilter = array(
				$arPrice['CONFIG'][1]['IBLOCK_FIELD'] => $item[$arPrice['CONFIG']["~1"]['COLUMN']],
				$arPrice['CONFIG'][2]['IBLOCK_FIELD'] => $item[$arPrice['CONFIG']["~2"]['COLUMN']],
				'IBLOCK_ID' => $sku_info['PRODUCT_IBLOCK_ID']
			);

			$product = \CIBlockElement::GetList(array(), $arProductUniqueFilter)->Fetch();

			if(!$product)
			{
				//не нашли номенклатуру
				$arProductFields = array(
					'ACTIVE' => 'Y',
					'IBLOCK_ID' => $sku_info['PRODUCT_IBLOCK_ID'],
					'NAME' => $item[$arPrice['CONFIG']["~1"]['COLUMN']],
					'PROPERTY_VALUES' => array(
						'PRICELIST' => $arPrice['ID'],
						$arPrice['CONFIG'][2]['PROPERTY'] => $item[$arPrice['CONFIG']["~2"]['COLUMN']]
					)
				);

				if($arPrice['CATALOG_SECTION_ID'] > 0)
					$arProductFields['IBLOCK_SECTION_ID'] = $arPrice['CATALOG_SECTION_ID'];

                $event = new \Bitrix\Main\Event("falsecode.pricelists", "OnBeforeAddProduct", array('arProductFields' => $arProductFields));
                $event->send();
                foreach ($event->getResults() as $eventResult)
                {
                    if($eventResult->getType() != \Bitrix\Main\EventResult::ERROR)
                    {
                        $data = $eventResult->getParameters();
                        $arProductFields = $data['arProductFields'];
                    }
                }

				$PRODUCT_ID = $el->Add($arProductFields);
				if(!$PRODUCT_ID)
				{
					throw new PricelistException($el->LAST_ERROR);
				}
				$product = array('ID' => $PRODUCT_ID);
				$ID = false;

                $event = new \Bitrix\Main\Event("falsecode.pricelists", "OnAfterAddProduct", array('ID' => $PRODUCT_ID));
                $event->send();
			}
			else
			{
				$arItem = \CIBlockElement::GetList(array(), $arUniqueFilter)->Fetch();
				if($arItem)
				{
					//у нас уже есть такой элемент, сохраним его $ID, чтобы обновить
					$ID = $arItem['ID'];
				}
			}
			$arFields['IBLOCK_ID'] = $sku_info['IBLOCK_ID'];
			$arFields['PROPERTY_VALUES'][$sku_info['SKU_PROPERTY_ID']] = $product['ID'];
		}

        $event = new \Bitrix\Main\Event("falsecode.pricelists", "OnBeforeAddItem", array('arFields' => $arFields, 'ID' => $ID));
        $event->send();
        foreach ($event->getResults() as $eventResult)
        {
            if($eventResult->getType() != \Bitrix\Main\EventResult::ERROR)
            {
                $data = $eventResult->getParameters();
                $arFields = $data['arFields'];
            }
        }

		if($ID)
		{
			$el->Update($ID, $arFields);
		}
		else
		{
			$ID = $el->Add($arFields);
			if(!$ID)
			{
				throw new PricelistException($el->LAST_ERROR);
			}
		}

		PricelistTable::$processedIDS[] = $ID;

		if(PricelistTable::$catalogModuleInstalled)
		{
			$arCatalogFields['ID'] = $ID;
			\CCatalogProduct::Add($arCatalogFields);

			if(!empty($arPriceFields))
			{
				$arPriceFields = array(
					'PRODUCT_ID' => $ID,
					'CATALOG_GROUP_ID' => $arPrice['PRICETYPE'],
					'PRICE' => $arPriceFields['PRICE'],
					'CURRENCY' => $arPrice['CURRENCY']
				);

				$res = \CPrice::GetList(array(), array("PRODUCT_ID" => $ID, "CATALOG_GROUP_ID" => $arPrice['PRICETYPE']));

				if ($arr = $res->Fetch())
					\CPrice::Update($arr["ID"], $arPriceFields);
				else
					\CPrice::Add($arPriceFields);

			}
		}

        $event = new \Bitrix\Main\Event("falsecode.pricelists", "OnAfterAddItem", array('ID' => $ID));
        $event->send();

		$event = new \Bitrix\Main\Event("falsecode.pricelists", "OnAfterImportItem", array('item' => $item, 'arPrice' => $arPrice));
		$event->send();

		return $error;
	}

	/**
	 * @param mixed $arPrice
	 */
	public static function deleteItems($arPrice)
	{
		global $DB;
		$DB->StartTransaction();
		fcAddMessage2Log("{$arPrice['NAME']}[{$arPrice['ID']}]: удаляем старые элементы.", 'falsecode.pricelists');
		$IBLOCK_ID = PricelistTable::getIBlockID($arPrice);
		$dbItems = \CIBlockElement::GetList(array(), array('IBLOCK_ID' => $IBLOCK_ID, 'PROPERTY_PRICELIST' => $arPrice['ID']));
		$c = $dbItems->SelectedRowsCount();
		while($arItem = $dbItems->Fetch())
		{
			\CIBlockElement::Delete($arItem['ID']);
		}
		$DB->Commit();

		fcAddMessage2Log("Удалено {$c} элементов, раннее импортированных из прайслиста {$arPrice['NAME']}[{$arPrice['ID']}].", 'falsecode.pricelists');
	}

	/**
	 * Проверяет наличие необходимых свойств у инф. блока.
	 * Свойства:
	 *  PRICELIST - хранит ID прайслиста, из которого загружен элемент
	 *
	 * @param $IBLOCK_ID
	 * @param $arPrice
	 */
	public static function checkProperties($IBLOCK_ID, $arPrice)
	{
		$ibp = new \CIBlockProperty();
		if(PricelistTable::$catalogModuleInstalled)
		{
			$sku_info = \CCatalogSKU::GetInfoByProductIBlock($IBLOCK_ID);
			if($sku_info)
			{
				$dbProps = \CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $sku_info['IBLOCK_ID'], 'CODE' => $arPrice['CONFIG'][2]['PROPERTY']));
				if($arProp = $dbProps->Fetch())
				{
					$arProductProp = \CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $sku_info['PRODUCT_IBLOCK_ID'], 'CODE' => $arPrice['CONFIG'][2]['PROPERTY']))->Fetch();
					if(!$arProductProp)
					{
						$arFields = array(
							'IBLOCK_ID' => $sku_info['PRODUCT_IBLOCK_ID'],
							'CODE' => $arProp['CODE'],
							'NAME' => $arProp['NAME'],
							'PROPERTY_TYPE' => 'S',
							'ACTIVE' => 'Y',
							'SORT' => 500,
						);
						$ibp->Add($arFields);
					}
				}
				$IBLOCK_ID = $sku_info['IBLOCK_ID'];
			}
		}

		$dbProps = \CIBlockProperty::GetList(array(), array('IBLOCK_ID' => $IBLOCK_ID, 'CODE' => 'PRICELIST'));
		if(!$arProp = $dbProps->Fetch())
		{
			$arFields = array(
				'IBLOCK_ID' => $IBLOCK_ID,
				'CODE' => 'PRICELIST',
				'NAME' => 'Из прайслиста',
				'PROPERTY_TYPE' => 'N',
				'ACTIVE' => 'Y',
				'SORT' => 1000,
			);
			$ibp->Add($arFields);
		}
	}

	/**
	 * Возвращает ID инф. блока, в который мы будем добавлять элементы (торг. предложения)
	 *
	 * @param $arPrice array
	 * @return int
	 */
	public static function getIBlockID($arPrice)
	{
		if(PricelistTable::$catalogModuleInstalled)
		{
			$sku_info = \CCatalogSKU::GetInfoByProductIBlock($arPrice['CATALOG_IBLOCK_ID']);
			return is_array($sku_info)? $sku_info['IBLOCK_ID']: $arPrice['CATALOG_IBLOCK_ID'];
		}
		else
			return $arPrice['CATALOG_IBLOCK_ID'];
	}

	public static function getFileFromSource(&$arPrice)
	{
		$local_file = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/pricelists/tmp/', 'pricelist');

		if($arPrice['SOURCE'] == 'ftp' || $arPrice['SOURCE'] == 'http')
		{
			if(!@copy($arPrice['URL'], $local_file))
			{
				$errors = error_get_last();
				throw new PricelistException("Ошибка при копированиее файла с FTP или HTTP ({$errors['message']}).");
			}
		}
		else if($arPrice['SOURCE'] == 'email')
		{
			//не имплементированно
			throw new PricelistException("Загрузка прайслиста из почтового ящика еще не имплементированна.");
		}
		else
			return false;

		$ar = \CFile::MakeFileArray($local_file);
		if($arPrice['FILE'])
		{
			$ar['old_file'] = $arPrice['FILE'];
			$ar['del'] = 'Y';
		}

		$pi = pathinfo($arPrice['URL']);
		$ar['name'] = $pi['basename'];

		$ID = \CFile::SaveFile($ar, 'pricelists/files');

		$arPrice['FILE'] = $ID;
		PricelistTable::Update($arPrice['ID'], $arPrice);

		@unlink($local_file);

		fcAddMessage2Log("{$arPrice['NAME']}[{$arPrice['ID']}]: загружен новый прайс с {$arPrice['URL']}", 'falsecode.pricelists');

		return $ID;
	}

	public static function getCSV(&$arPrice)
	{
		$arFile = \CFile::GetFileArray($arPrice['FILE']);
		$arFile['~SRC'] = $arFile['SRC'];
		$path = tempnam($_SERVER['DOCUMENT_ROOT'] . '/upload/pricelists/tmp/', 'pricelist');
		$rel_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
		$source = $_SERVER['DOCUMENT_ROOT'] . $arFile['SRC'];
		$csv_source = false;

		if(strpos($arFile['SRC'], '.xlsx') !== false)
		{
			$cwd = realpath(__DIR__. "/../tools");
			exec("python {$cwd}/xlsx2csv.py -d \"tab\" {$source} {$path}");
			$arFile['CONTENT_TYPE'] = 'text/comma-separated-value';
			$arFile['SRC'] = $rel_path;
			$arPrice['~SEPARATOR'] = $arPrice['SEPARATOR'];
			$arPrice['SEPARATOR'] = "\t";
			fcAddMessage2Log("{$arPrice['NAME']}[{$arPrice['ID']}]: xlsx > csv ({$source} > {$path}).", 'falsecode.pricelists');
		}
		else if (strpos($arFile['SRC'], '.xls') !== false)
		{
			$charsets = array(
				'windows-1251' => 'cp1251',
				'utf-8' => 'utf-8'
			);
			exec("xls2csv -x -c \"\t\" -q 0 -d {$charsets[$arPrice['CHARSET']]} {$source} > {$path}");
			$arFile['CONTENT_TYPE'] = 'text/comma-separated-value';
			$arFile['SRC'] = $rel_path;
			$arPrice['~SEPARATOR'] = $arPrice['SEPARATOR'];
			$arPrice['SEPARATOR'] = "\t";
			fcAddMessage2Log("{$arPrice['NAME']}[{$arPrice['ID']}]: xls > csv ({$source} > {$path}).", 'falsecode.pricelists');
		}
		else if(strpos($arFile['SRC'], '.csv') !== false)
		{
			$arFile['CONTENT_TYPE'] = 'text/comma-separated-value';
			$rel_path = $arFile['SRC'];
			$csv_source = true;
			@unlink($path);
			fcAddMessage2Log("{$arPrice['NAME']}[{$arPrice['ID']}]: конвертация не требуется, у нас csv({$rel_path}).", 'falsecode.pricelists');
		}
		else
		{
			throw new PricelistException("неизвестный mime тип у прайслиста: {$arFile['CONTENT_TYPE']} ({$arFile['SRC']})");
		}

		$arPrice['CSV'] = $rel_path;
		$arFields = array(
			'CSV' => $arPrice['CSV'],
		);
		PricelistTable::update($arPrice['ID'], $arFields);

		$arPrice['CSV_SOURCE'] = $csv_source;

		return $arPrice;
	}


	public function postProcessAction($arPrice)
	{
		if($arPrice['NO_IN_PRICE_ACTION'] == 'NOTHING')
			return;

		if($arPrice['NO_IN_PRICE_ACTION'] == 'DEACTIVATE')
		{
			$arFields = array(
				'ACTIVE' => 'N'
			);
			$el = new \CIBlockElement();
			$dbItems = \CIBlockElement::GetList(
				array(),
				array(
					'!ID' => \CIBlockElement::SubQuery("ID", array('ID' => PricelistTable::$processedIDS)),
					'PROPERTY_PRICELIST' => $arPrice['ID']
					//'IBLOCK_ID' => $arPrice['IBLOCK_ID']
				)
			);

			while($arItem = $dbItems->Fetch())
			{
				$el->Update($arItem['ID'], $arFields);
			}
		}
		else if($arPrice['NO_IN_PRICE_ACTION'] == 'DELETE')
		{
			$dbItems = \CIBlockElement::GetList(
				array(),
				array(
					'!ID' => \CIBlockElement::SubQuery("ID", array('ID' => PricelistTable::$processedIDS)),
					'PROPERTY_PRICELIST' => $arPrice['ID']
					//'IBLOCK_ID' => $arPrice['IBLOCK_ID']
				)
			);
			while($arItem = $dbItems->Fetch())
			{
				\CIBlockElement::Delete($arItem['ID']);
			}
		}
	}
}

PricelistTable::$catalogModuleInstalled = \CModule::IncludeModule('catalog');
?>