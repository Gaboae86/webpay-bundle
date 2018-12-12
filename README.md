#Instalación
`composer require gabrielcorrea/webpay-bundle`

#Configuración
En `.env` se deben agregar los siguientes parámetros de configuración:
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY=XXXXXXX
GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT=YYYYYYY
GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END=WWWWWWW
GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL=ZZZZZZZ
```
#### Descripción y ejemplo de los parámetros de configuración:

**GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY**

######  Descripción: 
Corresponde al path donde esta almacenado la key de webpay.
###### Ejemplo: 
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_KEY=/home/user_account/webpay/certs//597020000541.key
``` 
**GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT**
###### Descripción: 
Corresponde al path donde esta almacenado la parte pública de un certificado de webpay
###### Ejemplo:
```
GABRIELCORREA_WEBPAY_BUNDLE_PATH_CRT=/home/user_account/webpay/certs/597020000541.crt
```
                 
**GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END**
###### Descripción: 
Indica si el ambiene debe ser productivo de webpay.

Valores: true|false
###### Ejemplo:
```
GABRIELCORREA_WEBPAY_BUNDLE_IS_DEV_END=false
```
**GABRIELCORREA_WEBPAY_BUNDLE_FINAL_URL**
###### Descripción: 
Corresponde a la url a la cual webpay debe redirigir al final de la compra los datos asociados a esta. A esta url webpay le 
envia por POST el token de la transacción, con el cual se puede buscar los datos de la compra almacenados en la aplicacíón 
que ocupe este Bunble. Por eso es importante guardar este campo (de preferencia indexado en la base de datos)
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
Dentro de la clase del servicio, se deberá crear 3 métodos: ***handleProcessResultWebpayTransactionError***, 
***saveTransactionResult*** y ***handleProcessPaymentError***(los cuales los exige la intefaz).

* ***saveTransactionResult:*** este metodo sera llamado desde WebpayBundle, enviando como parametro los datos del 
resultado de la respuesta de WebPay, los cuales deberan ser guardados dentro de la aplicación.

* ***handleProcessPaymentError:*** este metodo sera llamado desde WebpayBundle cuando ocurra un error dentro del bundle. 
De esta forma podra ser controlado dentro de la aplicación.

* ***handleProcessResultWebpayTransactionError***: Este método será llamado desde WebpayBundle cuando ocurra un error al 
momento de recibir el resultado de la transacción por parte de Webpay e intentar procesarlo. Se recibirá como 
argumento una excepción, la cual puede ser de 3 tipos: 

    - *TransactionResultException*: Este tipo de error es recibido como parámetro cuando hay algún de problema al momento de solicidar el resultado de la transacción a Webpay.

    - *NotSuccessfulSaveTransactionException*: Este tipo de error es recibido como parámetro cuando hay algún problema al guardar la transacción en la aplicación. 

    - *AcknowledgeTransactionException*: Este tipo de error es recibido como paŕametro cuando hay algún prolema al momento de indicarle a Webpay que concrete la transacción.

######  Ejemplo con servicio de nombre 'PaymentHandler'':
```
class PaymentHandler implements SaveTransactionInterface
{
    public function saveTransactionResult(WebpayResult $webpayResult): ?TransactionRecordInterface
    {
         // TODO: Implement saveTransactionResult() method.
         // Acá va la lógica necesaria para guardar los datos de la respuesta de la transacción    
    }

    public function handleProcessResultWebpayTransactionError(WebpayException $webpayException)
    {
         // TODO: Implement handleProcessResultWebpayTransactionError() method.
         // Acá va la lógica necesaria para manejar si es que ocurriera un error que ocurra al momento de recibir el resultado de la transación desde Webpay e intentar procesarla. 
    }

    public function handleProcessPaymentError(WebpayException $webpayException)
    {
         // TODO: Implement handleProcessPaymentError() method.
         // Acá va la lógica necesaria para manejar si es que ocurriera un error que ocurra al momento inicializar la transación con Webpay. 
    }
}
```
##### Campos objeto 'WebpayResult'
* **accountingDate:** Fecha contable de la autorización de la transacción, la cual más el desfase de abono indica al 
comercio la fecha en que Transbank abonará al comercio. Largo: 4, formato MMDD. Tipo: string.
* **buyOrder:** Codigo único de la compra en la tienda. (Id de la nueva transacción generada desde la aplicación) . Tipo: string
* **sessionId:** Identificador de sesión, uso interno de comercio, este valor es devuelto al final de la transacción. Un uso posible puede ser la representación del intento de pago. Tipo: string
* **transactionDate:** Fecha y hora de la autorización. Tipo: string 
* **VCI:** Resultado de la autenticación para comercios Webpay Plus y/o 3D Secure. Tipo string
* **authorizationCode:** Código de autorización de la transacción. Tipo: string, Largo máximo: 6
* **paymentTypeCode:** Tipo de pago de la transacción. Tipo: string
* **responseCode:** Código de respuesta de la autorización. tipo: integer
* **sharesNumber:** Cantidad de cuotas. tipo: integer
* **amount:** Monto de la transacción. Máximo 2 decimales para USD. Tipo: string
* **commerceCode:** Código comercio de la tienda: Tipo: string. Largo: 12
* **cardExpirationDate:** Fecha de expiración de tarjeta, formato YY/MM. Tipo: string. Largo:5
* **cardNumber** ultimos 4 número de la tarjeta.
* **token** Token generado por webpay para la transacción. es importante guardar este token junto a la transacción para identificar la compra en la ruta final.


#Rutas

#### Configuración de las rutas:
En su aplicación debe crear un archivo `webpay.yaml` dentro de la carpeta **`bin/routes`** y agregar la configuración para las rutas del bundle:
```
webpay_router:
    resource: '@GabrielCorreaWebpayBundle/Controller/'
    type: annotation
```
### Rutas disponibles:
##### webpay_process_payment: 
En esta ruta se envia la petición para procesar el pago en webpay, por medio de un formulario: `WebpayPaymentType`

El formulario cuenta con dos campos:
* **amount**: Corresponde al monto de la transacción. Es un `IntegerType` y es requerido.  
* **buyorder**: Corresponde a un identificador único , generado en la aplicación para la transacción en curso. 
                Es un `HiddenType`, es requerido y debe tener un largo máximo de 26 caracteres.



###### Excepciónes: 
* ***TransactionResultException***: Esta excepción puede ocurrir cuando hay algún de problema al momento de solicidar el resultado de la transacción a Webpay.

* ***NotSuccessfulSaveTransactionException***:  Esta excepción puede ocurrir cuando hay algún problema al guardar la transacción en la aplicación. 
                                                Si ocurre esta excepción, se cortara el flujo normal y no se le indicara a Webpay que cierre la transacción.

* ***AcknowledgeTransactionException***:  Esta excepción puede ocurrir cuando hay algún problema al momento de indicarle a Webpay que concrete la transacción 
                                            depues de que fue guardada en el sistema.



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




