Instalación
========
`composer require gabrielcorrea/webpay-bundle`

Configuración
--------------
En `.env` deben agregar los siguientes parametros de configuración:
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY=XXXXXXX
GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT=YYYYYYY
GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END=WWWWWWW
GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL=ZZZZZZZ
```
#### Descripción y ejemplo de los parametros de configuración:

##### GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY: 
###### Descripción: corresponde al path donde esta almacenado la key de webpay
###### Ejemplo: 
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY=/home/user_account/webpay/certs//597020000541.key
``` 
##### GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT: 
###### Descripción: 
corresponde al path donde esta almacenado la parte pública de un certificado de webpay
###### Ejemplo:
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT=/home/user_account/webpay/certs/597020000541.crt
```
                 
##### GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END:
###### Descripción: 
Indica si el bundle debe funcionar ocn el SOAP productivo de webpay. true o false
###### Ejemplo:
```
GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END=false
```                    
##### GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL:
###### Descripción: 
Corresponde a la url a la cual webpay debe redirigir al final de la compra para ver el resumen. A esta url webpay le 
envia por POST el token de la transacción, por eso es importante guardarlo (de preferencia indexado en la base de datos)
######  Ejemplo:
```
GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL=http://www.mipaginaweb.com/descripcion-compra-producto
```

Crear archivo de configuracion llamado `webpay.yaml` dentro de `config/packages`, y agregar webpay handler:
```
gabriel_correa_webpay:
    handler:
        save_transaction_handler: App\Service\PaymentHandler
```
En la aplicación se debe crear un servicio que implemente la Interface ***GabrielCorrea/WebpayBundle/src/Interfaces/SaveTransactionInterface***.
Dentro de la clase de este servicio se deberá crear dos métodos: ***saveTransactionResult*** y ***errorHandlingWebpayBundle***(los cuales los exige la intefaz).
* ***saveTransactionResult:*** este metodo sera llamado desde WebpayBundle, enviando como parametro los datos del 
resultado de la respuesta de WebPay, los cuales deberan ser guardados dentro de la aplicación.
* ***errorHandlingWebpayBundle:*** este metodo sera llamado desde WebpayBundle cuando ocurra un error dentro del bundle. 
De esta forma podra ser controlado dentro de la aplicación 
######  Ejemplo:
```
public function saveTransactionResult(WebpayResult $webpayResult): ?TransactionRecordInterface
{
    // TODO: Implement saveTransactionResult() method.
    // Acá va todo el codigo necesario para guardar los datos de la respuesta de la transacción    
}
```
##### Campos objeto 'WebpayResult'
* ***accountingDate:*** Fecha contable de la autorización de la transacción, la cual más el desfase de abono indica al comercio la fecha en que Transbank abonará al comercio. Largo: 4, formato MMDD. Tipo: string
* ***buyOrder:*** Codigo único de la compra en la tienda. (Id de la nueva transacción generada desde la aplicación) . Tipo: string
* ***sessionId:*** Identificador de sesión, uso interno de comercio, este valor es devuelto al final de la transacción. Un uso posible puede ser la representación del intento de pago. Tipo: string
* ***transactionDate:*** Fecha y hora de la autorización. Tipo: string 
* ***VCI:*** Resultado de la autenticación para comercios Webpay Plus y/o 3D Secure. Tipo string
* ***authorizationCode:*** Código de autorización de la transacción. Tipo: string, Largo máximo: 6
* ***paymentTypeCode:*** Tipo de pago de la transacción. Tipo: string
* ***responseCode:*** Código de respuesta de la autorización. tipo: integer
* ***sharesNumber:*** Cantidad de cuotas. tipo: integer
* ***amount:*** Monto de la transacción. Máximo 2 decimales para USD. Tipo: string
* ***commerceCode:*** Código comercio de la tienda: Tipo: string. Largo: 12
* ***cardExpirationDate:*** Fecha de expiración de tarjeta, formato YY/MM. Tipo: string. Largo:5
* ***cardNumber*** ultimos 4 número de la tarjeta.
* ***token*** Token generado por webpay para la transacción. es importante guardar este token junto a la transacción para identificar la compra en la ruta final


Rutas
--------------
##### Configuración de las rutas:
En su aplicación debe crear un archivo webpay.yaml dentro de la carpeta bin/routes y agregar la configuración para las rutas del bundle:
```
webpay_router:
    resource: '@GabrielCorreaWebpayBundle/Controller/'
    type: annotation
```
#### Rutas disponibles:
##### webpay_process_payment: 
En esta ruta se envia la petición para procesar el pago en webpay.
Antes de llamar esta ruta se deben crear dos variables de sesión:
* amount: Corresponde al monto de la transacción.  
* buyorder: Corresponde a un identificador unico , generado en la palicación para la transacción en curso

###### Excepciónes: 
***NotSuccessfulSaveTransactionException:*** Esta excepción es por si hay algun problema al guardar la información de la transacción dentro de 
la aplicacion. Esto hara que no se mande la confirmación a webpay, haciendo que no finalice la transacción

Servicios
------------
#### WebpayService






 Interpretación Respuesta Webpay 
--------------------------------

#### Respuesta Webpay
##### Códigos respuesta autorización.
````
responseCode
````
* ***0:*** Transacción aprobada.
* ***-1:*** Rechazo de transacción.
* ***-2:*** Transacción debe reintentarse.
* ***-3:*** Error en transacción.
* ***-4:*** Rechazo de transacción.
* ***-5:*** Rechazo por error de tasa.
* ***-6:*** Excede cupo máximo mensual.
* ***-7:*** Excede límite diario por transacción.
* ***-8:*** Rubro no autorizado.

````
paymentTypeCode
````
###### Tipo de pago de la transacción.
* ***VD:*** Venta Debito
* ***VN:*** Venta Normal
* ***VC:*** Venta en cuotas
* ***SI:*** 3 cuotas sin interés
* ***S2:*** 2 cuotas sin interés
* ***NC:*** N Cuotas sin interés


#### Requisitos ruta final ***GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL***
La información a presentar dependerá de si la transacción fue autorizada o no.

###### Se recomienda, como mínimo, que posea:

* Número de orden de Pedido
* Nombre del comercio (Tienda de Mall)
* Monto y moneda de la transacción
* Código de autorización de la transacción
* Fecha de la transacción
* Tipo de pago realizado (Débito o Crédito)
* Tipo de cuota
* Cantidad de cuotas
* 4 últimos dígitos de la tarjeta bancaria
* Descripción de los bienes y/o servicios

###### Cuando la transacción no sea autorizada, se recomienda informar al tarjetahabiente al respecto. Puede presentar un texto explicativo como:

Orden de Compra XXXXXXX rechazada
Las posibles causas de este rechazo son:
* Error en el ingreso de los datos de su tarjeta de Crédito o Débito (fecha y/o código de seguridad).
* Su tarjeta de Crédito o Débito no cuenta con saldo suficiente.
* Tarjeta aún no habilitada en el sistema financiero.




