# Commerce Lifepay для Drupal 8 Commerce 2

## Модуль предназначен для интеграции магазина на Drupal 8 и Commerce 2 с системой оплаты Lifepay (сайты www.life-pay.ru)

1. Для использования модуля на вашем сайте его необходимо вам установить. Можно установить через меню установки модулей Drupal, а можно путем записи каталога uc_lifepay в директорию предназначенную для ваших сайтов "sites/all/modules" или "sites/default/modules"

2. После этого вы можете переходить в меню настройки методов оплаты "/admin/store/config/payment". 

3. Создаете новый способ оплаты в качестве типа выбираете "lifepay". 

4. Переходите к настройке созданного метода оплаты, выставляете:
- Метка - это то, что будет видеть пользователь в момент оформления заказа и совершения оплаты.
- Service ID - номер сервиса в системе Lifepay (можно взять из личного кабинета)
- Ключ (key) - также можно получить из личного кабинета продавца
- Секретный ключ (skey) - секретный ключ - также берется из личного кабинета продавца
- Метод оплаты - устанавливаете в соответствии с вашим договором (обычно 100% предоплата);
- НДС для продуктов (если у вас несколько типов товаров, то для каждого типа может быть свой НДС);
- НДС для доставки;
- Тип товара для продуктов;
- Тип товара для доставки - обычно, service (сервис);
- Объект для продуктов;
- Объект для доставки - обычно, service (сервис);
- Задействовать ли email для отправки;
- Order status after successfull payment - статус заказа после его выполнения



# Commerce Lifepay for Drupal 8 Commerce 2

## The module is designed to integrate a store on Drupal 8 and Commerce 2 with the Lifepay payment system (www.life-pay.ru)

1. To use the module on your site you need to install it. You can install it through the installation menu of Drupal modules, or you can by writing the uc_lifepay directory to a directory intended for your sites: "sites/all/modules" or "sites/default/modules"

2. After that, you can go to the configuration menu of payment methods "/admin/store/config/payment".

3. Create a new payment method as the type choose "lifepay".

4. Go to setting up the created payment method, set:
- The label is what the user will see at the time of placing an order and making a payment.
- Service ID - service number in the Lifepay system (can be taken from your personal account)
- Key (key) - can also be obtained from the personal account of the seller
- Secret key (skey) - secret key - also taken from the seller's personal account
- Payment method - set in accordance with your contract (usually 100% prepayment);
- VAT for products (if you have several types of goods, then each type may have its own VAT);
- VAT for delivery;
- Product type for products;
- Type of goods for delivery - usually, service (service);
- Object for products;
- Object for delivery - usually, service (service);
- Whether to use email for sending;
- Order status after successfull payment - order status after completion