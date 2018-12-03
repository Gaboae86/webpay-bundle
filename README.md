Installation
========
`composer require gabrielcorrea/webpay-bundle`


Configuration
--------------
En el `.env` sen agregar los siguientes parametros de configuración:
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY=XXXXXXX
GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT=YYYYYYY
GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END=WWWWWWW
GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL=ZZZZZZZ
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY: corresponde al path donde esta almacenado la key de webpay
GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT: corresponde al path donde esta almacenado la parte publica de un certificado de webpay
GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END: indica si el bundle debe funcionar ocn el SOAP prudctivo de webpay. true o false
GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL: corresponde a la url a la cual webpay debe redirigir al final de la compra para ver el resumen

Crear un archivo `webpay.yaml` en `config/packages`, y agregar tu webpay handler:
```
gabriel_correa_webpay:
    handler:
        save_transaction_handler: App\Service\PaymentHandler
```


