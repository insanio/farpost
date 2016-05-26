<?
namespace Falsecode;
require_once($_SERVER['DOCUMENT_ROOT'] . "/local/modules/falsecode.atc25/tools/tools.php");

\CModule::IncludeModule('iblock');
\CModule::IncludeModule('catalog');

class UsedSpares
{
	static $VALIDATION = array(
		'REQUIRED' => array('NAME')
	);

	static $CFG = array(
		'NAME' => 0,
		'BRAND' => 1,
		'MODEL' => 2,
		'BODY' => 3,
		'ENGINE' => 4,
		'YEAR' => 5,
		'FRONT_REAR' => 6,
		'LEFT_RIGHT' => 7,
		'DESCRIPTION' => 8,
		'KOD' => 9,
		'COLOR' => 10,
		'PRICE' => 11,
		'OEM' => 12,
		'COMMENT' => 13,
		'OPTIKA' => 14,
		'DOP_INFO' => 15,
		'TIRE_SIZE' => 16,
		'TIRE_HEIGHT' => 17,
		'TIRE_WIDTH' => 18,
		'TIRE_INFO' => 19,
		'TIRE_SEASON' => 20,
		'TIRE_COMPLEKT' => 21,
		'ARTIKUL' => 22,
		'AMOUNT' => 23,
		'NOMENKLATURA' => 24,
		'FOTO' => 25,
		'ANALOG' => 26,
		'STATUS' => 27,
		'XML_ID' => 28,
        'TIP_SHASSI' => 29,
        'PROPERTY' => 30,
        'UP_DOWN' => 31,
		'TARIFF_DELIVERY' => 32,
		'TARIFF_DELIVERY_ONE_ITEM' => 33,
		'TEST_DVS' => 34
	);

	static $cache;

	public static function ImportItem(&$item)
	{
		global $cache;
		global $processed_ids;
        global $oElement;
        global $oSection;
        global $processed_xml_ids;
		global $opts;

		$error = array();
		$empty_values = array('-', '.');

		foreach ($item as $k => $v) {
			$item[$k] = iconv('windows-1251', 'utf-8', $v);

			if ($k != self::$CFG['FOTO'] && $k != self::$CFG['TEST_DVS']) {
				$item[$k] = trim($item[$k]);
				$item[$k] = strtolower($item[$k]);
			}

			if(in_array($item[$k], $empty_values))
				unset($item[$k]);
		}

        //fcAddMessage2Log("Импортируем позицию: " . print_r($item, true), 'falsecode.price');

		//заглушка на некорректный csv
		//if($item[self::$CFG['XML_ID']] == 'нет' || $item[self::$CFG['XML_ID']] == 'да' || $item[self::$CFG['XML_ID']] == '')
		if (!preg_match('/[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}/', $item[self::$CFG['XML_ID']])) {
			$error[] = "Некорретный формат XML_ID ({$item[self::$CFG['XML_ID']]})";
			return $error;
		}

        if ($processed_xml_ids->findOne(array('xml_id' => $item[self::$CFG['XML_ID']]))) {
            $error[] = "Этот XML_ID ({$item[self::$CFG['XML_ID']]}) уже обрабатывался.";
            return $error;
        }

        $processed_xml_ids->update(
            array('xml_id' => $item[self::$CFG['XML_ID']]),
            array('xml_id' => $item[self::$CFG['XML_ID']]),
            array('upsert' => true)
        );

		$cd = $cache->findOne(array('xml_id' => $item[self::$CFG['XML_ID']]));
		$md5 = md5(implode('|', $item));
		if($cd['md5'] == $md5) {

            //$error[] = "Выставляем для позиции с ID = {$cd['id']} свойство ARCHIVE = ''.";
            \CIBlockElement::SetPropertyValues($cd['id'], 8, '', 'ARCHIVE');

			$processed_ids->update(
				array('id' => $cd['id']),
				array('id' => $cd['id']),
				array('upsert' => true)
			);

			if (!isset($opts['force'])) {
                $error[] = "Попадание в кэш";
                return $error;
            }
		}

		foreach (self::$VALIDATION['REQUIRED'] as $v) {
			if(strlen($item[self::$CFG[$v]]) == 0)
				$error[] = "{$v}: обязательное поле";
		}

        if (count($error) > 0)
			return $error;

        $PRICE = false;

		$item[self::$CFG['NAME']] = strtolower($item[self::$CFG['NAME']]);

		$arName = \CIBlockELement::GetList(array(), array('IBLOCK_ID' => 7, 'NAME' => $item[self::$CFG['NAME']]))->Fetch();

		if (!$arName) {
			$arFields = array(
				"ACTIVE" => "Y",
				"IBLOCK_ID" => 7,
				"NAME" => $item[self::$CFG['NAME']]
			);
			$oElement->Add($arFields, false, false, false);

			$arName['NAME'] = $item[self::$CFG['NAME']];
		}

		$props = array();

		if ($item[self::$CFG['STATUS']]) {
			if($item[self::$CFG['STATUS']] == 'да')
				$props['STATUS'] = 'new';
			else
				$props['STATUS'] = 'used';
		}

		if ($item[self::$CFG['BRAND']]) {
			$arBrand = \CIBlockSection::GetList(array(), array('IBLOCK_ID' => 6, 'NAME' => $item[self::$CFG['BRAND']], 'DEPTH_LEVEL' => 1))->Fetch();
			if ($arBrand) {
				$props['BRAND'] = $arBrand['ID'];
				$BRAND_ID = $arBrand['ID'];
			} else {
				$arFields = array(
					'ACTIVE' => 'Y',
					'IBLOCK_ID' => 6,
					'NAME' => strtoupper($item[self::$CFG['BRAND']])
				);

				$BRAND_ID = $oSection->Add($arFields);
			}
			$props['BRAND'] = $BRAND_ID;
		}

		if ($item[self::$CFG['MODEL']]) {
			$arModel = \CIBlockSection::GetList(array(), array('IBLOCK_ID' => 6, 'NAME' => $item[self::$CFG['MODEL']], 'DEPTH_LEVEL' => 2))->Fetch();
			if ($arModel) {
				$MODEL_ID = $arModel['ID'];
			} else {
				$arFields = array(
					'ACTIVE' => 'Y',
					'IBLOCK_ID' => 6,
					'IBLOCK_SECTION_ID' => $BRAND_ID,
					'NAME' => strtoupper($item[self::$CFG['MODEL']])
				);

				$MODEL_ID = $oSection->Add($arFields);
			}
			$props['MODEL'] = $MODEL_ID;
		}

		if ($item[self::$CFG['BODY']]) {
			$props['BODY'] = $item[self::$CFG['BODY']];
		}


		if ($item[self::$CFG['LEFT_RIGHT']]) {
			$props['LEFT_RIGHT'] = $item[self::$CFG['LEFT_RIGHT']];
		}

		if ($item[self::$CFG['FRONT_REAR']]) {
			$props['FRONT_REAR'] = $item[self::$CFG['FRONT_REAR']];
		}

        if ($item[self::$CFG['UP_DOWN']]) {
            $props['UP_DOWN'] = $item[self::$CFG['UP_DOWN']];
        }

        if ($item[self::$CFG['KOD']]) {
            $props['NOMER'] = $item[self::$CFG['KOD']];
        }

        if ($item[self::$CFG['TIP_SHASSI']]) {
            $props['TIP_SHASSI'] = $item[self::$CFG['TIP_SHASSI']];
        }

        if ($item[self::$CFG['PROPERTY']]) {
            $props['PROPERTY'] = $item[self::$CFG['PROPERTY']];
        }

		if ($item[self::$CFG['YEAR']]) {
			$props['YEAR'] = str_replace(array(" ", ',', "\xC2\xA0"), array("", '.', ""), $item[self::$CFG['YEAR']]);
		}

		if ($item[self::$CFG['ENGINE']]) {
			$props['ENGINE'] = $item[self::$CFG['ENGINE']];
		}

		if ($item[self::$CFG['ARTIKUL']]) {
			$props['ARTIKUL'] = str_replace("-", "", $item[self::$CFG['ARTIKUL']]);
		}

        if ($item[self::$CFG['OEM']]) {
            $props['OEM'] = str_replace("-", "", $item[self::$CFG['OEM']]);
        }

        if ($item[self::$CFG['OPTIKA']]) {
            $props['OPTIKA'] = str_replace("-", "", $item[self::$CFG['OPTIKA']]);
        }

		if ($item[self::$CFG['UP_DOWN']]) {
			$props['UP_DOWN'] = $item[self::$CFG['UP_DOWN']];
		}

		if ($item[self::$CFG['COLOR']]) {
			$props['COLOR'] = $item[self::$CFG['COLOR']];
		}

		if ($item[self::$CFG['TARIFF_DELIVERY_ONE_ITEM']]) {
			$props['TARIFF_DELIVERY_ONE_ITEM'] = str_replace(array(" ", ',', "\xC2\xA0"), '', $item[self::$CFG['TARIFF_DELIVERY_ONE_ITEM']]);
		}

		if ($item[self::$CFG['TARIFF_DELIVERY']]) {
			$props['TARIFF_DELIVERY'] = str_replace(array(" ", ',', "\xC2\xA0"), '', $item[self::$CFG['TARIFF_DELIVERY']]);
		}

        if ($item[self::$CFG['TEST_DVS']]) {
            $props['TEST_DVS'] = $item[self::$CFG['TEST_DVS']];
        }

		if ($item[self::$CFG['XML_ID']] == "") {
			$item[self::$CFG['XML_ID']] = $md5;
		}

        if ($item[self::$CFG['FOTO']]) {
            $props['MORE_PHOTO'] = explode('|', $item[self::$CFG['FOTO']]);
        }

        $props['ARCHIVE'] = '';

		$arFields = array(
			'IBLOCK_ID' => 8,
			'ACTIVE' => 'Y',
			'NAME' => $arName['NAME'],
			//'PREVIEW_TEXT' => $item[self::$CFG['DESCRIPTION']],
			'PROPERTY_VALUES' => $props,
			'XML_ID' => $item[self::$CFG['XML_ID']]
		);

        $sep = '';
        if (strlen($item[self::$CFG['DESCRIPTION']]) > 0) {
            $arFields['PREVIEW_TEXT'] = "{$item[self::$CFG['DESCRIPTION']]}";
            $sep = ', ';
        }

		if (strlen($item[self::$CFG['COMMENT']]) > 0) {
			$arFields['PREVIEW_TEXT'] .= "{$sep} {$item[self::$CFG['COMMENT']]}";
            $sep = ', ';
		}

		if (strlen($item[self::$CFG['DOP_INFO']]) > 0) {
			$arFields['PREVIEW_TEXT'] .= "{$sep} {$item[self::$CFG['DOP_INFO']]}";
		}

        if ($item[self::$CFG['PRICE']]) {
            $PRICE = str_replace(array(" ", ',', "\xC2\xA0"), array("", '.', ""), $item[self::$CFG['PRICE']]);
        }

		$arItem = \CIBlockElement::GetList(array(), array('XML_ID' => $item[self::$CFG['XML_ID']]))->Fetch();
		$r = false;

		if ($arItem) {
			$ID = $arItem['ID'];

			if ($oElement->Update($ID, $arFields)) {
				$r = true;
			}
        } else {
			$r = $oElement->Add($arFields);
			$ID = $r;
		}

        UsedSpares::UpdateItemQuantity($ID, trim($item[self::$CFG['AMOUNT']]));
        UsedSpares::UpdateItemPrice($ID, $PRICE);

		if ($r) {
			$cache->update(
				array('xml_id' => $item[self::$CFG['XML_ID']]),
				array(
					'xml_id' => $item[self::$CFG['XML_ID']],
					'md5' => $md5,
					'id' => $ID
				), array('upsert' => true));

            $processed_ids->update(
                array('id' => $ID),
                array('id' => $ID),
                array('upsert' => true)
            );
		} else {
			$error[] = $oElement->LAST_ERROR;
		}

		return $error;
	}

    public static function UpdateItemPrice($PRODUCT_ID, $PRICE)
    {
//        $arCatalogProduct = \CCatalogProduct::GetByID($PRODUCT_ID);
//        if(!$arCatalogProduct)
//        {
//            $arCatalogProductFields = [
//                'ID' => $PRODUCT_ID
//            ];
//
//            \CCatalogProduct::Add($arCatalogProductFields);
//        }

        //получаем базовую (BASE) цену
        //$arPriceType = \CCatalogGroup::GetList([], ['BASE' => 'Y'])->Fetch();
        $arPrice = \CPrice::GetList([], ['PRODUCT_ID' => $PRODUCT_ID, 'CATALOG_GROUP_ID' => 1])->Fetch();

        if($arPrice)
        {
            $arPriceFields = [
                'PRICE' => $PRICE,
                'CURRENCY' => 'RUB'
            ];

            \CPrice::Update($arPrice['ID'], $arPriceFields);
        }
        else
        {
            $arPriceFields = [
                'PRODUCT_ID' => $PRODUCT_ID,
                'CATALOG_GROUP_ID' => 1,
                'PRICE' => $PRICE,
                'CURRENCY' => 'RUB'
            ];

            \CPrice::Add($arPriceFields);
        }
    }

    public static function UpdateItemQuantity($PRODUCT_ID, $AMOUNT)
    {
        $arCatalogProductFields = [
            'ID' => $PRODUCT_ID,
            'QUANTITY' => $AMOUNT
        ];
        $arCatalogProduct = \CCatalogProduct::GetByID($PRODUCT_ID);
        if(!$arCatalogProduct)
            \CCatalogProduct::Add($arCatalogProductFields);
        else
        {
            unset($arCatalogProductFields['ID']);
            \CCatalogProduct::Update($PRODUCT_ID, $arCatalogProductFields);
        }
    }

	public static function DeletePriceItems($arPrice)
	{
		global $DB;

		//$DB->StartTransaction();
		$dbItems = CIBlockElement::GetList(array(), array('IBLOCK_ID' => 2, 'PROPERTY_PRICELIST' => $arPrice['ID']));

		while($arItem = $dbItems->Fetch())
		{
			CIBlockElement::Delete($arItem['ID']);
		}
		//$DB->Commit();
	}

	/**
	 * Импортирует прайс.
	 *
	 * @param $arPrice array массив с данными о прайсе из таблицы
	 * @param int $start_row номер строки, с которой начинать
	 * @param int $finish_row номер строки, на которой заканчивать
	 *
	 * @throws \Bitrix\Main\SystemException
	 */
	public static function import($arPrice, $start_row = 0, $finish_row = 0)
	{
		if($arPrice['PROPERTIES']['CSV']['VALUE'])
		{
			$path = $_SERVER['DOCUMENT_ROOT'] . $arPrice['PROPERTIES']['CSV']['VALUE'];
			$pid = fcSpares::importCSV($path, $arPrice, $start_row, $finish_row);

			fcAddMessage2Log("Поток (pid={$pid}) для прайслиста {$arPrice['NAME']}[{$arPrice['ID']}] закончил работу.", 'falsecode.usedspares');
		}
	}

    public static function getRemoteImage($path)
    {
        $tmpdir = "{$_SERVER['DOCUMENT_ROOT']}/upload/atc25/images/";
        $pi = pathinfo($path);
        $tmpname = $tmpdir.$pi['basename'];
        if(@copy($path, $tmpname))
        {
            $r = \CFile::MakeFileArray($tmpname);
            if ($r['type'] != 'unknown')
            {
                \CFile::ResizeImage($r, array('width' => 1200, 'height' => 1200), BX_RESIZE_IMAGE_PROPORTIONAL_ALT);
                //$props['MORE_PHOTO']["n{$i}"] = $r;
                return $r;
            }
        }
        return false;
    }

    public static function updateItemFromXml($item)
    {
        $dbItem = \CIBlockElement::GetList(['ID' => 'ASC'], ['XML_ID' => $item['guid']], false, false, ['ID', 'IBLOCK_ID']);
        while ($arItem = $dbItem->Fetch()) {
			fcAddMessage2Log("Устанавливаем количество для позиции с guid: {$item['guid']}, CATALOG_QUANTITY = {$item['stock']}", 'falsecode.price');
            UsedSpares::UpdateItemQuantity($arItem['ID'], $item['stock']);
            if($item['stock'] <= 0) {
                \CIBlockElement::SetPropertyValues($arItem['ID'], $arItem['IBLOCK_ID'], 'Y', 'ARCHIVE');
                fcAddMessage2Log("Обновляем свойство ARCHIVE для позиции с guid: {$item['guid']}", 'falsecode.price');
            }
			//удаляем кэш этого элемента
			fcAddMessage2Log("Удаляем кэш для позиции с guid: {$item['guid']}", 'falsecode.price');
			//fcAddMessage2Log("1 " . print_r(UsedSpares::$cache->findOne(['xml_id' => $item['guid']]), true), 'falsecode.price');
			$r = UsedSpares::$cache->remove(['xml_id' => $item['guid']]);
			//fcAddMessage2Log("2 " . print_r($r, true), 'falsecode.price');
			//fcAddMessage2Log("3 " . print_r(UsedSpares::$cache->findOne(['xml_id' => $item['guid']]), true), 'falsecode.price');
        }
        return [];
    }
}
?>