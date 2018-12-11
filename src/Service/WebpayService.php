<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 18-11-18
 * Time: 22:12
 */

namespace GabrielCorrea\WebpayBundle\Service;


use Freshwork\Transbank\CertificationBagFactory;
use Freshwork\Transbank\RedirectorHelper;
use Freshwork\Transbank\TransbankServiceFactory;
use Freshwork\Transbank\WebpayNormal;
use GabrielCorrea\WebpayBundle\Exception\AcknowledgeTransactionException;
use GabrielCorrea\WebpayBundle\Exception\NotSuccessfulSaveTransactionException;
use GabrielCorrea\WebpayBundle\Exception\TransactionResultException;
use GabrielCorrea\WebpayBundle\Interfaces\SaveTransactionInterface;
use GabrielCorrea\WebpayBundle\Model\WebpayResult;

class WebpayService
{
    /*Código de respuesta de la autorización:*/
    const CODIGO_TRANSACCION_APROBADA = 0;
    const CODIGO_RECHAZO_DE_TRANSACCION_1 = -1;
    const CODIGO_TRANSACCION_DEBE_REINTENTARSE = -2;
    const CODIGO_ERROR_EN_TRANSACCION = -3;
    const CODIGO_RECHAZO_DE_TRANSACCION_4 = -4;
    const CODIGO_RECHAZO_POR_ERROR_DE_TASA = -5;
    const CODIGO_EXCEDE_CUPO_MAXIMO_MENSUAL = -6;
    const CODIGO_EXCEDE_LIMITE_DIARIO_POR_TRANSACCION = -7;
    const CODIGO_RUBRO_NO_AUTORIZADO = -8;

    /*Tipo de pago de la transacción.*/
    const CODIGO_TIPO_PAGO_VENTA_DEBITO = "VD";
    const CODIGO_TIPO_PAGO_VENTA_NORMAL = "VN";
    const CODIGO_TIPO_PAGO_VENTA_EN_CUOTAS = "VC";
    const CODIGO_TIPO_PAGO_3_CUOTAS_SIN_INTERES = "SI";
    const CODIGO_TIPO_PAGO_2_CUOTAS_SIN_INTERES = "S2";
    const CODIGO_TIPO_PAGO_N_CUOTAS_SIN_INTERES = "NC";


    /**
     * @var \Twig_Environment
     */
    private $template;

    /**
     * @var SaveTransactionInterface
     */
    private $saveTransactionService;

    /**
     * WebpayService constructor.
     * @param \Twig_Environment $template
     * @param SaveTransactionInterface $saveTransactionService
     */
    public function __construct(\Twig_Environment $template, SaveTransactionInterface $saveTransactionService)
    {
        $this->template = $template;
        $this->saveTransactionService = $saveTransactionService;
    }


    /**
     * Executes the real transaction in Webpay. It won't wait longer than 30 seconds, otherwise it will throw
     * a WebpayException
     *
     * @param WebpayNormal $webpayNormal
     *
     * @return \Freshwork\Transbank\WebpayStandard\acknowledgeTransactionResponse
     * @throws AcknowledgeTransactionException
     */
    public function acknowledgeTransaction(WebpayNormal $webpayNormal)
    {
        try {
            return $webpayNormal->acknowledgeTransaction();
        } catch (\SoapFault $exception) {
            throw new AcknowledgeTransactionException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * Método que se llama desde el controlador al momento de recibir la respuesta de la transacción desde webpay
     * se
     * @param string $tokenWs
     * @return string
     * @throws AcknowledgeTransactionException
     * @throws NotSuccessfulSaveTransactionException
     * @throws TransactionResultException
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function processResponseWebpay(string $tokenWs)
    {
        $certificationBag = CertificationBagFactory::integrationWebpayNormal();
        $webpayNormal = TransbankServiceFactory::normal($certificationBag);

        $transactionResult = null;
        $transactionResult = $this->executeTransactionResult($webpayNormal, $tokenWs);

        $redirectHTML = RedirectorHelper::redirectBackNormal($transactionResult->urlRedirection);

        return $this->template->render('@GabrielCorreaWebpay/Webpay/redirectWebpay.html.twig',
            ['redirect' => $redirectHTML]);
    }

    /**
     * Método que recibe como parametro la respuesta de webpay y el token de la transaccion
     * Dentro del método se llama al handler en la aplicacion que ocupa bundle, esto es para almacenar los
     * datos de la transacción (this->saveTransactionService->saveTransactionResult($webpayResult);)
     *
     * @param WebpayNormal $webpayNormal
     * @param string $tokenWs
     * @return \Freshwork\Transbank\WebpayStandard\transactionResultOutput
     * @throws AcknowledgeTransactionException
     * @throws NotSuccessfulSaveTransactionException
     * @throws TransactionResultException
     */
    private function executeTransactionResult(WebpayNormal $webpayNormal, string $tokenWs)
    {
        /*Se verifica con el token el estado de la transaccion*/
        try {
            $transactionResult = $webpayNormal->getTransactionResult($tokenWs);//obtengo los campos que retorno la transaccion

            $webpayResult = new WebpayResult();
            $webpayResult
                ->setBuyOrder($transactionResult->buyOrder)
                ->setAmount($transactionResult->detailOutput->amount)
                ->setToken($tokenWs)
                ->setAccountingDate($transactionResult->accountingDate)
                ->setAuthorizationCode($transactionResult->detailOutput->authorizationCode)
                ->setCardExpirationDate($transactionResult->cardDetail->cardExpirationDate)
                ->setCardNumber($transactionResult->cardDetail->cardNumber)
                ->setCommerceCode($transactionResult->detailOutput->commerceCode)
                ->setPaymentTypeCode($transactionResult->detailOutput->paymentTypeCode)
                ->setResponseCode($transactionResult->detailOutput->responseCode)
                ->setSessionId($transactionResult->sessionId)
                ->setSharesNumber($transactionResult->detailOutput->sharesNumber)
                ->setTransactionDate($transactionResult->transactionDate)
                ->setVCI($transactionResult->VCI);

            try {

                $this->saveTransactionService->saveTransactionResult($webpayResult); //guarda resultado en la aplicacion
                $this->acknowledgeTransaction($webpayNormal); // se informa a transbank que cierre la transaccion (aprovada o rechazada)

            } catch (NotSuccessfulSaveTransactionException $exception) {
                throw $exception;
            } catch (AcknowledgeTransactionException $exception) {
                throw $exception;
            }

            return $transactionResult;

        } catch (\SoapFault $exception) {// error en webpay
            throw new TransactionResultException($exception->getMessage(), $exception->getCode());
        }
    }
}