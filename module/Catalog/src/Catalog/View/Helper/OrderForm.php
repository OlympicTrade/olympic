<?php
namespace Catalog\View\Helper;

use Application\Model\Region;
use Aptero\String\Date;
use Delivery\Model\Delivery;
use Zend\View\Helper\AbstractHelper;

class OrderForm extends AbstractHelper
{
    public function __invoke($price)
    {
        if(!$price) {
            return '';
        }

        $timeOptions = '';
        for($i = 10; $i <= 21; $i++) {
            $timeOptions .= '<option>' . $i . ':00</option>';
        }

        $region = Region::getInstance();

        $pickupDate = $this->pickupDate();

        $html =
            '<div class="order-form order-box">'
                .'<form action="/order/add-order/" method="post" class="form-box">'
                    .'<div class="row">'
                        /*
                        <div class="element">
                            <div class="label">ФИО</div>
                            <input type="text" name="country" class="std-input" value="">
                        </div>
                        */

                        .'<input type="text" class="std-input" placeholder="ФИО" name="attrs-name">'
                    .'</div>'
                    .'<div class="row">'
                        /*
                        <div class="element">
                            <div class="label">Телефон</div>
                            <input type="text" name="country" class="std-input" value="">
                        </div>
                         */
                        .'<input type="text" class="std-input phone" placeholder="Телефон" name="phone">'
                    .'</div>'

                    .'<div class="delivery">'
                        .'<div class="header">Доставка</div>'

                        .'<div class="row">'
                            .'<div class="trigger">'
                                .'<div class="tr" data-type="courier"><i class="fa fa-truck"></i> Курьер</div>'
                                .'<div class="tr" data-type="pickup"><i class="fa fa-home"></i> Самовывоз</div>'

                                .'<div class="clear"></div>'

                                .'<div><input type="hidden" name="attrs-delivery" value=""></div>'
                            .'</div>'
                        .'</div>'

                        .'<div class="box" data-type="courier">'
                            .'<div class="row">'
                                .'<input type="text" class="std-input" placeholder="Адрес доставки" name="attrs-address">'
                            .'</div>'
                            .'<div class="row cols-wide">'
                                .'<div class="col-3-3">'
                                    .'<input type="text" class="std-input datepicker" name="attrs-date" placeholder="Дата">'
                                .'</div>'
                                .'<div class="col-3-3">'
                                    .'<select class="std-select" name="attrs-time_from">'
                                        .'<option value="" class="placeholder">время с</option>'
                                        . $timeOptions
                                    .'</select>'
                                .'</div>'
                                .'<div class="col-3-3">'
                                    .'<select class="std-select" name="attrs-time_to">'
                                        .'<option value="" class="placeholder">по</option>'
                                        . $timeOptions
                                    .'</select>'
                                .'</div>'
                            .'</div>'
                            .'<div class="clear"></div>'
                        .'</div>'

                        .'<div class="box" data-type="pickup">'
                            .'<div class="row notice">'
                                .'Дата доставки: 17.01.2017 (Вторник) 15:30'
                            .'</div>'

                            .'<div class="row">'
                                .'<input type="hidden" name="attrs-point" value="">'
                                .'<div href="/delivery/points/" class="chose-pickup popup btn">Выбрать точку самовывоза</div>'
                            .'</div>'

                            .'<div class="clear"></div>'
                        .'</div>'
                    .'</div>'

                    .'<div class="summary">'
                        .'<div class="header">Итого</div>'

                        .'<div class="row">'
                            .'<div class="label">Товары:</div>'
                            .'<span class="cart-price"></span> <i class="fa fa-ruble-sign"></i>'
                        .'</div>'

                        .'<div class="row">'
                            .'<div class="label">Доставка:</div>'
                            .'<span class="cart-delivery"></span>'
                        .'</div>'

                        .'<div class="row sum">'
                            .'<div class="label">Всего к оплате:</div>'
                            .'<span><span class="cart-full-price"></span> <i class="fa fa-ruble-sign"></i></span>'
                        .'</div>'

                        .'<div class="row btns">'
                            .'<input type="submit" class="btn orange order-btn" value="Оформить заказ">'
                            .'<div class="cart-error">Мин. стоимость заказа 400 <i class="fa fa-ruble-sign"></i></div>'
                        .'</div>'
                    .'</div>'
                .'</form>'

                .'<div class="phone-verification">'
                    .'<div class="title">Подверждение номера</div>'
                    .'<div class="notice">Введите код из SMS</div>'
                    .'<input class="std-input code" placeholder="••••" maxlength="4">'
                    .'<div class="wrong-code"></div>'

                    .'<div class="error-box">'
                        .'<div class="trigger">Не приходит SMS?</div>'
                        .'<div class="help">'
                            .'<div class="text">Если вы не получили код подтверждения, наш сотрудник перезвонит вам в течении часа и подтвердит заказ.</div>'
                            .'<span class="btn continue">Продолжить без кода</span>'
                        .'</div>'
                    .'</div>'
                .'</div>'

                .'<div class="order-processed">'
                    .'<div class="title">Заказ оформлен</div>'
                    .'<div class="body"></div>'
                .'</div>'
            .'</div>';

        $html .= $this->datepicker();

        return $html;
    }

    protected function pickupDate()
    {
        $date = new \DateTime();

        $region = Region::getInstance();
        $weekDay = $date->format('N');

        $deliveryDelay = 0;
        switch ($weekDay) {
            case 1: $deliveryDelay = 2; break;
            case 2: $deliveryDelay = 1; break;
            case 3: $deliveryDelay = 2; break;
            case 4: $deliveryDelay = 1; break;
            case 5: $deliveryDelay = 3; break;
            case 6: $deliveryDelay = 2; break;
            case 7: $deliveryDelay = 1; break;
        }

        $delivery = Delivery::getInstance();
        $deliveryDelay += $delivery->get('delay');

        $date->modify('+ ' . $deliveryDelay . ' days');

        return $date;
    }

    protected function datepicker()
    {
        $region = Region::getInstance();
        if($region->get('name') != 'Санкт-Петербург') {
            $weekDay = (new \DateTime())->format('N');

            switch ($weekDay) {
                case 1: $deliveryDelay = 2; break;
                case 2: $deliveryDelay = 1; break;
                case 3: $deliveryDelay = 2; break;
                case 4: $deliveryDelay = 1; break;
                case 5: $deliveryDelay = 3; break;
                case 6: $deliveryDelay = 2; break;
                case 7: $deliveryDelay = 1; break;
            }

            $delivery = Delivery::getInstance();
            $deliveryDelay += $delivery->get('delay');

            $start = (new \DateTime());
            $start->modify('-5 hours');

            $interval = new \DateInterval('P1D');

            $end = clone $start;
            $end->modify('+1 month');

            $period = new \DatePeriod($start, $interval, $end);
            $excludDates = [];

            foreach ($period as $day) {
                if ($day->format('N') == 7) {
                    $excludDates[] = $day->format('d.m.Y');
                }
            }
        } else {
            $excludDates = [];
            $deliveryDelay = 1;
        }

        $html =
            '<script>
                $.getScript(libs.jqueryUi, function() {
                var options = $.config.datepicker;
                
                options.minDate = ' . $deliveryDelay . ';
                
                var dates = ["' . implode('", "', $excludDates) . '"];
                
                options.beforeShowDay = function(date){
                    var string = jQuery.datepicker.formatDate(\'dd.mm.yy\', date);
                    return [dates.indexOf(string) == -1]
                }
                
                $(".of-datepicker").datepicker(options);
            });
            </script>';

        return $html;
    }
}