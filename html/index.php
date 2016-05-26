<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Запчасти в Новосибирске");
\Bitrix\Main\Page\Asset::getInstance()->addCss('/contacts/novosibirsk/style.css');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/js/parallax.min.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/js/scrollmagic/greensock/TweenMax.min.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/js/scrollmagic/ScrollMagic.min.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/js/scrollmagic/plugins/animation.gsap.min.js');
\Bitrix\Main\Page\Asset::getInstance()->addJs('/js/scrollmagic/plugins/debug.addIndicators.min.js');

\Bitrix\Main\Page\Asset::getInstance()->addJs('/contacts/novosibirsk/script.js');
?>
<div class="landing">
    <div class="block1" data-parallax="scroll" data-position="top" data-bleed="-1" data-image-src="/contacts/images/bg1.jpg" data-natural-width="1920" data-natural-height="690">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-5">
                    <div class="frame"><h1 title="Купить автозапчасти оптом и в розницу в Новосибирске">Автозапчасти<br/><span>в Новосибирске</span></h1></div>
                    <div class="text">Более 50 миллионов оригинальных автозапчастей</div>
                    <div class="tel"><a href="tel:+73832146176" title="Позвонить: купить автозапчасти в Новосибирске">+7 383 <span>214 61 76</span></a></div>
                </div>
                <div class="col-md-6 col-lg-6 col-lg-offset-1 col-xs-12 col-sm-12">
                    <?$APPLICATION->IncludeComponent(
	"informunity:feedback", 
	"landing", 
	array(
		"USE_CAPTCHA" => "N",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"USE_IU_PAT" => "Y",
		"USE_IU_IB" => "N",
		"USE_ATTACH" => "N",
		"EMAIL_TO" => array(
			0 => "",
			1 => "mng@order-24.ru",
			2 => "",
		),
		"EXT_FIELDS" => array(
			0 => "iu_0",
			1 => "iu_1",
			2 => "Телефон",
			3 => "",
		),
		"FIELD_FOR_THEME" => "iu_none",
		"EM_THEME" => "#SITE#: Заявка с лэндинга",
		"AFTER_TEXT" => "",
		"USE_EMAIL_USER" => "Y",
		"REQUIRED_FIELDS" => array(
			0 => "iu_0",
			1 => "iu_1",
		),
		"TEXTAREA_FIELDS" => array(
		),
		"FIELD_FOR_NAME" => "iu_0",
		"FIELD_FOR_EMAIL" => "iu_1",
		"COPY_LETTER" => "N",
		"COMPONENT_TEMPLATE" => "landing"
	),
	false
);?>
                </div>
            </div>
        </div>
    </div>
    <div class="block2">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <h2 title="Продажа автозапчастей оптом и в розницу в Новосибирске">Найти автозапчасть</h2>
                    <?$APPLICATION->IncludeComponent('falsecode:catalog.filter', "tabs", array());?>
                    <div class="brands">
                        <img src="/images/brands.png" />
                        В НАШЕМ АССОРТИМЕНТЕ - ЗАПЧАСТИ ВСЕХ МИРОВЫХ АВТОМОБИЛЬНЫХ БРЕНДОВ
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="block3" data-parallax="scroll" data-position="top" data-bleed="0" data-image-src="/contacts/images/bg3.jpg" data-natural-width="1920" data-natural-height="880">
        <div class="container">
            <div class="row">
                <div class="col-md-3 col-lg-3 col-md-offset-9 col-lg-offset-9 hidden-xs hidden-sm">
                    <div class="layer2"><img src="/contacts/images/bg3-2.png" /></div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <h2 title="Купить автозапчасти оптом и в розницу в Новосибирске">Как мы работаем</h2>
                </div>
                <div class="block3-content-wrapper">
                    <div class="col-md-7 col-lg-6 hidden-sm hidden-xs">
                        <div class="text blue">Подбор запчасти нашим специалистом</div>
                        <div class="step1">
                            <div class="icons">
                                <div class="ico ico-1"><img src="/contacts/images/ico-1.png" /></div>
                                <div class="ico arrow-1"><img src="/contacts/images/ico-a.png" /></div>
                                <div class="ico ico-2"><img src="/contacts/images/ico-2.png" /></div>
                                <div class="ico arrow-2"><img src="/contacts/images/ico-a.png" /></div>
                                <div class="ico ico-3"><img src="/contacts/images/ico-3.png" /></div>
                            </div>
                            <div class="text icons">
                                <div class="text-1">Оставляете заявку<br/>или звоните</div>
                                <div class="text-2">Уточняем детали<br/>заказа</div>
                                <div class="text-3">Подбираем запчасти<br/>для автомобиля</div>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                        <div class="text white">Самостоятельный выбор запчасти на сайте</div>
                        <div class="step2">
                            <div class="icons">
                                <div class="ico ico-4"><img src="/contacts/images/ico-4.png" /></div>
                                <div class="ico arrow-3"><img src="/contacts/images/ico-a1.png" /></div>
                                <div class="ico ico-5"><img src="/contacts/images/ico-5.png" /></div>
                                <div class="ico arrow-4"><img src="/contacts/images/ico-a2.png" /></div>
                                <div class="ico ico-6"><img src="/contacts/images/ico-6.png" /></div>
                            </div>
                            <div class="text icons-white">
                                <div class="text-1">Открываете <a href="/catalog/" title="Найти автозапчасти">форму<br/>поиска</a> на сайте</div>
                                <div class="text-2">Находите запчасти<br/>для автомобиля</div>
                                <div class="text-3">Добавляете запчасти<br/>в корзину</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-3 col-md-offset-2 col-lg-offset-3 hidden-sm hidden-xs">
                        <div class="step3">
                            <div class="ico-1"><img src="/contacts/images/ico-step-1.png" /></div>
                            <div class="text-1">Оформление заказа и оплата</div>
                            <div class="ico-2"><img src="/contacts/images/ico-step-2.png" /></div>
                            <div class="text-2">Доставка к дверям клиента или самовывоз с точки выдачи заказов</div>
                            <div class="ico-3"><img src="/contacts/images/ico-step-3.png" /></div>
                            <div class="text-3">Ваш автомобиль снова на ходу!</div>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 visible-xs-block visible-sm-block">
                        <img src="/contacts/images/b3-sm.png" />
                    </div>
                    <div class="clearfix"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="block4">
        <div class="container">
            <div class="row">
                <div class="col-md-5 col-lg-5 col-md-offset-7 col-lg-offset-7">
                    <div class="right"><h2 title="Купить автозапчасти оптом и в розницу в Новосибирске">Факты о нас</h2></div>
                </div>
                <div class="col-md-1 col-lg-1 hidden-xs hidden-sm">
                    <div class="frame"><img src="/contacts/images/facts-ico-1.png" /></div>
                </div>
                <div class="col-md-2 col-lg-2 hidden-xs hidden-sm">
                    <div class="frame">
                    Пункты<br/>
                    выдачи заказов<br/>
                    в <span>10</span> городах России
                    </div>
                </div>
                <div class="col-md-1 col-lg-1 hidden-xs hidden-sm">
                    <div class="frame"><img src="/contacts/images/facts-ico-2.png" /></div>
                </div>
                <div class="col-md-2 col-lg-2 hidden-xs hidden-sm">
                    <div class="frame">
                    Более <span>5 лет</span><br/>
                    в сфере продаж<br/>
                    автозапчастей
                    </div>
                </div>
                <div class="col-md-1 col-lg-1 hidden-xs hidden-sm">
                    <div class="frame">
                    <img src="/contacts/images/facts-ico-3.png" />
                    </div>
                </div>
                <div class="col-md-2 col-lg-2 hidden-xs hidden-sm">
                    <div class="frame">
                    <span>3500</span> заказов<br/>
                    выполнено в 2015<br/>
                    году
                    </div>
                </div>
                <div class="col-md-1 col-lg-1 hidden-xs hidden-sm">
                    <div class="frame">
                    <img src="/contacts/images/facts-ico-4.png" />
                    </div>
                </div>
                <div class="col-md-2 col-lg-2 hidden-xs hidden-sm">
                    <div class="frame">
                    Более <span>50</span> торговых<br/>
                    марок в электронном<br/>
                    каталоге запчастей
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 visible-sm-block visible-xs-block">
                    <img src="/contacts/images/b4-sm.png" />
                </div>
            </div>
        </div>
    </div>

    <div class="block5" data-parallax="scroll" data-position="top" data-bleed="0" data-image-src="/contacts/images/bg5.jpg" data-natural-width="1920" data-natural-height="690">
        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-5 col-lg-5">
                    <div class="left-text">
                    <h2 title="Купить автозапчасти оптом и в розницу в Новосибирске">Мы экономим ваше время</h2>
                    <p>Всю работу по подбору автозапчастей квалифицированные специалисты берут на себя!</p>
                    </div>
                </div>
                <div class="col-md-1 col-lg-1 hidden-xs hidden-sm">
                    <div class="arrow"><img src="/contacts/images/b5-arrow.png"/></div>
                </div>
                <div class="col-xs-12 col-sm-12 col-md-6 col-lg-6">
                    <?$APPLICATION->IncludeComponent(
                        "informunity:feedback",
                        "landing",
                        array(
                            "USE_CAPTCHA" => "N",
                            "OK_TEXT" => "Спасибо, ваше сообщение принято.",
                            "USE_IU_PAT" => "Y",
                            "USE_IU_IB" => "N",
                            "USE_ATTACH" => "N",
                            "EMAIL_TO" => array(
                                0 => "",
                                1 => "mng@order-24.ru",
                                2 => "",
                            ),
                            "EXT_FIELDS" => array(
                                0 => "iu_0",
                                1 => "iu_1",
                                2 => "Телефон",
                                3 => "",
                            ),
                            "FIELD_FOR_THEME" => "iu_none",
                            "EM_THEME" => "#SITE#: Заявка с лэндинга",
                            "AFTER_TEXT" => "",
                            "USE_EMAIL_USER" => "Y",
                            "REQUIRED_FIELDS" => array(
                                0 => "iu_0",
                                1 => "iu_1",
                            ),
                            "TEXTAREA_FIELDS" => array(
                            ),
                            "FIELD_FOR_NAME" => "iu_0",
                            "FIELD_FOR_EMAIL" => "iu_1",
                            "COPY_LETTER" => "N",
                            "COMPONENT_TEMPLATE" => "landing"
                        ),
                        false
                    );?>
                </div>
            </div>
        </div>
    </div>

    <div class="block6">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12 hidden-xs hidden-sm">
                    <div class="contacts">
                        <h2 title="Купить автозапчасти оптом и в розницу в Новосибирске">Контакты</h2>
                        <address>Адрес: г. Новосибирск, Петухова 67, к.8</address>
                        <div>График работы: Пн-Пт: 09:00-19:00, Сб: 11:00-16:00</div>
                        <div class="tel"><a href="tel:+73832146176" title="Позвонить: купить автозапчасти в Новосибирске">+7 383 <span>214 61 76</span></a></div>
                    </div>
                </div>
                <div class="col-xs-12 col-sm-12 visible-xs-block visible-sm-block">
                    <div class="contacts-sm">
                        <h2 title="Купить автозапчасти оптом и в розницу в Новосибирске">Контакты</h2>
                        <address>Адрес: г. Новосибирск, Петухова 67, к.8</address>
                        <p>График работы: Пн-Пт: 09:00-19:00, Сб: 11:00-16:00</p>
                        <div class="tel"><a href="tel:+73832146176" title="Позвонить: купить автозапчасти в Новосибирске">+7 383 <span>214 61 76</span></a></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="map">
            <script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=jFD1qaFaHbgpjdU5yw92wcC8eHmt1zkw&width=100%&height=600&lang=ru_RU&sourceType=constructor"></script>
        </div>
    </div>

    <div class="block7">
        <div class="container">
            <div class="row">
                <div class="col-md-12 col-lg-12">
                    <h1 title="Купить автозапчасти оптом и в розницу в Новосибирске">&laquo;Order-24&raquo; в Новосибирске<br/>Итернет-магазин автозапчастей</h1>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p class="lead">Любые автозапчасти для Вашего автомобиля в Новосибирске. В ассортименте нашего магазина автозапчастей более 50 миллионов оригинальных запчастей для японских, американских, европейских и корейских автомобилей.</p>
                    <p>В магазине автозапчастей «Order-24» вы сможете купить автозапчасти для таких марок, как Toyota, Mitsubishi, Honda, Nissan, Mazda, Suzuki, Ssang Yong, Kia, Hyundai, Subaru, Lexus, Isuzu, Daihatsu, Infinity, Hino, а также многие другие запчасти для автомобиля.</p>
                </div>
                <div class="col-md-6 col-lg-6">
                    <p>Магазин автозапчастей «Order-24» полностью гарантирует подлинность и качество  автозапчастей, так как закупает их только у официальных региональных дилеров (ОАЭ, Ближний и Средний Восток, Европа, Южная Корея, Япония, США). Обращайтесь в автомагазин «Order-24» - и мы закупим для Вас нужные автозапчасти, сделаем это по разумным ценам и в разумные сроки.</p>
                    <p>В магазине автозапчастей «Order-24» предусмотрена возможность как оптовой, так и розничной покупки. При этом, оформив заказ, вы сможете отследить его состояние в своем личном кабинете. Покупка в нашем магазине осуществляется с максимальным удобством для клиентов.</p>
                    <p>Мы всегда готовы доставить Вам автозапчасти для иномарок в удобное для Вас время. Интернет-магазин автозапчастей &laquo;Order-24&raquo; всегда открыт для Вас.</p>
                </div>
            </div>
        </div>
    </div>
</div>


 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>