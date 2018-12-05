<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 27-11-18
 * Time: 22:59
 */

namespace GabrielCorrea\WebpayBundle\Interfaces;

use GabrielCorrea\WebpayBundle\Exception\NotSuccessfulSaveTransactionException;
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
     * Esta funcion será llamada si es que hay algun error en el proceso, enviando como parametro el mensaje de error
     *
     * @param String $errorMessage
     * @return TransactionRecordInterface|null
     */
    public function errorHandlingWebpayBundle(String $errorMessage);
}