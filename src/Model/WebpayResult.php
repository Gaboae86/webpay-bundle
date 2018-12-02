<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 02-12-18
 * Time: 18:03
 */

namespace GabrielCorrea\WebpayBundle\Model;


use Freshwork\Transbank\WebpayStandard\transactionResultOutput;
use MiguelAlcaino\PaymentGateway\Interfaces\Entity\TransactionRecordInterface;

class WebpayResult
{
    /**
     * @var TransactionRecordInterface
     */
    private $transactionRecord;

    /**
     * @var transactionResultOutput
     */
    private $transactionResponse;


    /**
     * Fecha contable de la autorización de la transacción, la cual más el desfase de abono indica al comercio la fecha
     * en que Transbank abonará al comercio.
     *
     * Largo: 4, formato MMDD
     *
     * @var string
     */
    private $accountingDate;

    /**
     * Orden de compra de la tienda.
     *
     * @var string
     */
    private $buyOrder;

    /**
     * Identificador de sesión, uso interno de comercio, este valor es devuelto al final de la transacción. Un uso
     * posible puede ser la representación del intento de pago.
     *
     * @var string
     */
    private $sessionId;

    /**
     * Fecha y hora de la autorización.
     *
     * @var string
     */
    private $transactionDate;

    /**
     * Resultado de la autenticación para comercios Webpay Plus y/o 3D Secure
     *
     * @var string
     */
    private $VCI;

    //detail Output *************
    /**
     * Código de autorización de la transacción
     *
     * Largo máximo: 6
     *
     * @var string
     */
    private $authorizationCode;

    /**
     * Tipo de pago de la transacción.
     *
     * @var string
     */
    private $paymentTypeCode;

    /**
     * Código de respuesta de la autorización.
     *
     * @var integer
     */
    private $responseCode;

    /**
     * Cantidad de cuotas
     *
     * @var integer
     */
    private $sharesNumber;

    /**
     *  Monto de la transacción. Máximo 2 decimales para USD.
     *
     * Largo máximo: 2
     *
     * @var string
     */
    private $amount;

    /**
     * Código comercio de la tienda
     *
     * Largo: 12
     *
     * @var string
     */
    private $commerceCode;


    // card detail ***************
    /**
     * Fecha de expiración de tarjeta, formato YY/MM.
     *
     * Largo: 5
     *
     * @var string
     */
    private $cardExpirationDate;

    /**
     * Número de la tarjeta.
     *
     * Largo máximo: 16
     *
     * @var string
     */
    private $cardNumber;

    /**
     * @return TransactionRecordInterface
     */
    public function getTransactionRecord(): TransactionRecordInterface
    {
        return $this->transactionRecord;
    }

    /**
     * @param TransactionRecordInterface $transactionRecord
     * @return WebpayResult
     */
    public function setTransactionRecord(TransactionRecordInterface $transactionRecord): WebpayResult
    {
        $this->transactionRecord = $transactionRecord;
        return $this;
    }

    /**
     * @return transactionResultOutput
     */
    public function getTransactionResponse(): transactionResultOutput
    {
        return $this->transactionResponse;
    }

    /**
     * @param transactionResultOutput $transactionResponse
     * @return WebpayResult
     */
    public function setTransactionResponse(transactionResultOutput $transactionResponse): WebpayResult
    {
        $this->transactionResponse = $transactionResponse;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccountingDate(): string
    {
        return $this->accountingDate;
    }

    /**
     * @param string $accountingDate
     * @return WebpayResult
     */
    public function setAccountingDate(string $accountingDate): WebpayResult
    {
        $this->accountingDate = $accountingDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getBuyOrder(): string
    {
        return $this->buyOrder;
    }

    /**
     * @param string $buyOrder
     * @return WebpayResult
     */
    public function setBuyOrder(string $buyOrder): WebpayResult
    {
        $this->buyOrder = $buyOrder;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return WebpayResult
     */
    public function setSessionId(string $sessionId): WebpayResult
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return string
     */
    public function getTransactionDate(): string
    {
        return $this->transactionDate;
    }

    /**
     * @param string $transactionDate
     * @return WebpayResult
     */
    public function setTransactionDate(string $transactionDate): WebpayResult
    {
        $this->transactionDate = $transactionDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getVCI(): string
    {
        return $this->VCI;
    }

    /**
     * @param string $VCI
     * @return WebpayResult
     */
    public function setVCI(string $VCI): WebpayResult
    {
        $this->VCI = $VCI;
        return $this;
    }

    /**
     * @return string
     */
    public function getAuthorizationCode(): string
    {
        return $this->authorizationCode;
    }

    /**
     * @param string $authorizationCode
     * @return WebpayResult
     */
    public function setAuthorizationCode(string $authorizationCode): WebpayResult
    {
        $this->authorizationCode = $authorizationCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getPaymentTypeCode(): string
    {
        return $this->paymentTypeCode;
    }

    /**
     * @param string $paymentTypeCode
     * @return WebpayResult
     */
    public function setPaymentTypeCode(string $paymentTypeCode): WebpayResult
    {
        $this->paymentTypeCode = $paymentTypeCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getResponseCode(): int
    {
        return $this->responseCode;
    }

    /**
     * @param int $responseCode
     * @return WebpayResult
     */
    public function setResponseCode(int $responseCode): WebpayResult
    {
        $this->responseCode = $responseCode;
        return $this;
    }

    /**
     * @return int
     */
    public function getSharesNumber(): int
    {
        return $this->sharesNumber;
    }

    /**
     * @param int $sharesNumber
     * @return WebpayResult
     */
    public function setSharesNumber(int $sharesNumber): WebpayResult
    {
        $this->sharesNumber = $sharesNumber;
        return $this;
    }

    /**
     * @return string
     */
    public function getAmount(): string
    {
        return $this->amount;
    }

    /**
     * @param string $amount
     * @return WebpayResult
     */
    public function setAmount(string $amount): WebpayResult
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string
     */
    public function getCommerceCode(): string
    {
        return $this->commerceCode;
    }

    /**
     * @param string $commerceCode
     * @return WebpayResult
     */
    public function setCommerceCode(string $commerceCode): WebpayResult
    {
        $this->commerceCode = $commerceCode;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardExpirationDate(): string
    {
        return $this->cardExpirationDate;
    }

    /**
     * @param string $cardExpirationDate
     * @return WebpayResult
     */
    public function setCardExpirationDate(string $cardExpirationDate): WebpayResult
    {
        $this->cardExpirationDate = $cardExpirationDate;
        return $this;
    }

    /**
     * @return string
     */
    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    /**
     * @param string $cardNumber
     * @return WebpayResult
     */
    public function setCardNumber(string $cardNumber): WebpayResult
    {
        $this->cardNumber = $cardNumber;
        return $this;
    }

}