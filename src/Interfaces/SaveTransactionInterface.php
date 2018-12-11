<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 27-11-18
 * Time: 22:59
 */

namespace GabrielCorrea\WebpayBundle\Interfaces;

use GabrielCorrea\WebpayBundle\Exception\NotSuccessfulSaveTransactionException;
use GabrielCorrea\WebpayBundle\Exception\WebpayException;
use GabrielCorrea\WebpayBundle\Model\WebpayResult;
use MiguelAlcaino\PaymentGateway\Interfaces\Entity\TransactionRecordInterface;


interface SaveTransactionInterface
{
    /**
     * Se debe guardar los datos que retorna la transacción de webpay
     *
     * @param WebpayResult $webpayResult
     * @return mixed
     * @throws NotSuccessfulSaveTransactionException
     */
    public function saveTransactionResult(WebpayResult $webpayResult): ?TransactionRecordInterface;

    /**
     * @param WebpayException $webpayException
     * @return mixed
     */
    public function handleProcessPaymentError(WebpayException $webpayException);

    /**
     * Este se llamara si es que falla el proceso que recibe la transacción desde webpay la guarda en la aplicacion:
     * TransactionResultException: Problemas al consultar la respuesta a webpay
     * NotSuccessfulSaveTransactionException: Problemas al guardar en la aplicación
     * AcknowledgeTransactionException: problemas al cerrar la transacción en webpay
     *
     * @param WebpayException $webpayException
     * @return mixed
     */
    public function handleProcessResultWebpayTransactionError(WebpayException $webpayException);
}