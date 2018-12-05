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
use GabrielCorrea\WebpayBundle\Exception\RejectedPaymentException;
use GabrielCorrea\WebpayBundle\Exception\TransactionResultException;
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
    public function processPaymentAction(Request $request, ParameterBagInterface $params, SaveTransactionInterface $saveTransactionInterface)
    {
        $amount = $request->getSession()->get('amount');
        $buyOrder = $request->getSession()->get('buyorder');

        $webpay_final_url = $params->get('webpay_final_url');
        $private_key = $params->get('webpay_path_key');
        $client_certificate = $params->get('webpay_path_crt');
        $is_dev_end = $params->get('webpay_is_dev_end');

        $saveTransactionInterface->errorHandlingWebpayBundle("prueba");

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

        $webpayRedirectHTML = RedirectorHelper::redirectHTML($webpayResponse->url, $webpayResponse->token);

        return $this->render('@GabrielCorreaWebpay/redirectWebpay.html.twig', ['redirect' => $webpayRedirectHTML]);


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
     * @return Response|\Symfony\Component\HttpFoundation\Response
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function webpayResponseAction(Request $request, WebpayService $webpayService)
    {
        $token_ws = $request->request->get('token_ws');

        try {
            $response = $webpayService->processResponseWebpay($token_ws);
        } catch (RejectedPaymentException $exception) {
            //dentro de estos catch debe ser llamado un metodo de una interfaz definida para el manejo de errores. Así
            //pueda ser procesado en la aplicacion
            return $this->render('@GabrielCorreaWebpay/redirectWebpay.html.twig', ['redirect' => 'Tu pago fue rechazado',]);
        } catch (TransactionResultException $exception) {
            return $this->render('@GabrielCorreaWebpay/redirectWebpay.html.twig',
                ['redirect' => 'Hubo un error tratando de construir tu transaction',]);
        } catch (AcknowledgeTransactionException $exception) {
            return $this->render('@GabrielCorreaWebpay/redirectWebpay.html.twig',
                ['redirect' => 'Hubo un error al registrar tu transaction en webpay',]
            );
        }

        return new Response($response);
    }
}