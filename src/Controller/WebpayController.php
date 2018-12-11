<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 19-11-18
 * Time: 7:59
 */

namespace GabrielCorrea\WebpayBundle\Controller;

use Freshwork\Transbank\CertificationBag;
use Freshwork\Transbank\CertificationBagFactory;
use Freshwork\Transbank\RedirectorHelper;
use Freshwork\Transbank\TransbankServiceFactory;
use GabrielCorrea\WebpayBundle\Exception\AcknowledgeTransactionException;
use GabrielCorrea\WebpayBundle\Exception\NotSuccessfulSaveTransactionException;
use GabrielCorrea\WebpayBundle\Exception\TransactionResultException;
use GabrielCorrea\WebpayBundle\Exception\WebpayException;
use GabrielCorrea\WebpayBundle\Form\Webpay\WebpayPaymentType;
use GabrielCorrea\WebpayBundle\Interfaces\SaveTransactionInterface;
use GabrielCorrea\WebpayBundle\Service\WebpayService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class WebpayController
 * @package GabrielCorrea\WebpayBundle\Controller
 * @Route("webpay")
 */
class WebpayController extends AbstractController
{

    /**
     * Esta ruta es llamada para procesar el pago. Antes de llamarse, debe de crearse dos variables de sesión:
     * amount: que contiene el precio del producto a procesar
     * buyorder: contiene el dentificador unico de la transacción dentro del sistema
     *
     * @param Request $request
     * @param ParameterBagInterface $params
     * @param SaveTransactionInterface $saveTransactionInterface
     * @Route("/process-payment", name="webpay_process_payment")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Freshwork\Transbank\Exceptions\EmptyTransactionException
     */
    public function processPaymentAction(Request $request, ParameterBagInterface $params,
                                         SaveTransactionInterface $saveTransactionInterface)
    {
        $form = $this->createForm(
            WebpayPaymentType::class,
            null,
            [
                'action' => $this->generateUrl('webpay_process_payment'),
                'method' => 'POST',
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $amount = $form->get('amount')->getData();
                $buyOrder = $form->get('buyOrder')->getData();

                $webpay_final_url = $params->get('webpay_final_url');
                $private_key = $params->get('webpay_path_key');
                $client_certificate = $params->get('webpay_path_crt');
                $is_dev_end = $params->get('webpay_is_dev_end');

                if ($is_dev_end == 'true') {
                    $certificationBag = CertificationBagFactory::create($private_key, $client_certificate, null,
                        CertificationBag::PRODUCTION);
                } else {
                    $certificationBag = CertificationBagFactory::create($private_key, $client_certificate, null,
                        CertificationBag::INTEGRATION);
                }

                $webpayNormal = TransbankServiceFactory::normal($certificationBag);

                $webpayNormal->addTransactionDetail($amount, $buyOrder);

                $webpayResponse = $webpayNormal->initTransaction($this->generateUrl('webpay_response', [],
                    UrlGeneratorInterface::ABSOLUTE_URL), $webpay_final_url);
                //lo ideal seria aca guardar el token aca que retorna webpay

                $webpayRedirectHTML = RedirectorHelper::redirectHTML($webpayResponse->url, $webpayResponse->token);

                return $this->render('@GabrielCorreaWebpay/Webpay/redirectWebpay.html.twig', ['redirect' => $webpayRedirectHTML]);

            } catch (\Exception $exception) {
                $webpayException = new WebpayException($exception->getMessage(), $exception->getCode());
                $saveTransactionInterface->handleProcessPaymentError($webpayException);
            };

        } else {
            return $this->render($params->get('payment_form_view'), array(
                'pay_form' => $form->createView()
            ));
        }


        /* flujo:
         * Aplicacion: - en sesion pone monto y identificador
         *             - llama a la ruta webpay_process_payment
         * Bundle:  - obtiene parametros de sesion y configuracion.
         *          - Verifica si es pruebas o produccion y genera la "bolsa de certificados".
         *          - Crea una instancia de Webpay normal (Webpay Plus).
         *          - Inicializa la transaccion enviando la url que recibira la respuesta de webpay, y la url final
         *              donde sera redireccionado para mostrar el detalle de la compra y transacción del producto.
         *          - Lo inicializacon anterior retorna una url de Webpay a la cual se redireccona
         *
         */
    }

    /**
     * A esta ruta llega la respuesta del procesamiento de la transaccion por parte de webpay. La respuesta viene junto
     * con un token que tambien debe ser almacenado (este token es utilizado para identificar la tansaccion guardada en
     * base de datos y mostrar la información en la "página final de la transacción"  dentro de la aplicación que ocupa
     * este bundle)
     *
     * @param Request $request
     * @param WebpayService $webpayService
     * @Route("/webpay-response", name="webpay_response")
     * @param SaveTransactionInterface $saveTransactionInterface
     * @return Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function webpayResponseAction(Request $request, WebpayService $webpayService,
                                         SaveTransactionInterface $saveTransactionInterface)
    {
        try {

            $token_ws = $request->request->get('token_ws');
            $response = $webpayService->processResponseWebpay($token_ws);
            return new Response($response);

        } catch (NotSuccessfulSaveTransactionException $notSuccessfulSaveTransactionException) {
            $saveTransactionInterface->handleProcessResultWebpayTransactionError($notSuccessfulSaveTransactionException);
        } catch (TransactionResultException $transactionResultException) {
            $saveTransactionInterface->handleProcessResultWebpayTransactionError($transactionResultException);
        } catch (AcknowledgeTransactionException $acknowledgeTransactionException) {
            $saveTransactionInterface->handleProcessResultWebpayTransactionError($acknowledgeTransactionException);
        }
    }
}