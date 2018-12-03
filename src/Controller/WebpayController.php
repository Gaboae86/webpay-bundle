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
     * @param Request $request
     * @param WebpayService $webpayService
     * @param ParameterBagInterface $params
     * @Route("/process-payment", name="webpay_process_payment")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Freshwork\Transbank\Exceptions\EmptyTransactionException
     */
    public function processPaymentAction(Request $request, WebpayService $webpayService, ParameterBagInterface $params)
    {
        $amount = $request->getSession()->get('amount');
        $buyOrder = $request->getSession()->get('buyorder');

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

        $webpayRedirectHTML = RedirectorHelper::redirectHTML($webpayResponse->url, $webpayResponse->token);

        return $this->render('@GabrielCorreaWebpay/redirectWebpay.html.twig', ['redirect' => $webpayRedirectHTML]);


        //
        /*
         * Yo a esta ruta la voy a llamar desde afuera, ya cuando tenga tu bundle instalado y todo el webeo
         * Entonces el flujo seria
         * Power peralta app:
         * - seleccion del paquete a comprar
         * - login con mindbody
         * - login exitoso ->
         * Bundle webpay:
         * - esta ruta webpay_process_payment
         * - se hacen todas las redirecciones que haya que hacer hasta que el pago sea exitoso o fallido
         *
         *
         * A esta ruta yo la podria llamar por post y mandarte las rutas por post, las rutas para los callbacks
         * que entiendo que son dos. O en la misma session te las mando.... o, mejor aun, las seteamos desde el .env
         * y desde aca lees un parametro que tenga su valor desde el .env
         * en el .env tendriamos:
         * PRIMER_CALLBACK_URL=http://picopalquelee.com/wea_entremedio
         * SECUNDO_CALLBACK_URL=http://picopalquelee.com/wea_final
         *
         * Esas rutas que acabamos de definir van a estar feura de tu bundle.... quizas la primera no. porque es la que
         * recibe la transaccion antes de esa wea que estabamos probando el otro dia... asi que esa ruta deberia ser dentro
         * de este bundle porque no es algo que el usuario va a ver. Solamente va a servir para guardar mierdas en la
         * base de datos antes de llamar a ese metodo acknowledge...
         * Una vez que todo salga bien se llama a la url final que esa si que va a estar fuera de tu bundle.
         * --cuando se llega la primera respuesta de webpay.... como te devolveria los parametros para que los alamcceanras
         * en la bd? y luego seguir el flujo?
         *
         * (PP: powerperalta)
         * (WPB: Webpaybundle)
         * PP: login exitoso -> WPB: llamas a webpay, redireccionamiento y mierdas -> primer callback donde recibes el codigo 0
         * y esas mierdas. Ahi creas una instancia de TransactionRecord y la empiezas a llenar y para pasarmela a mi, lo mas
         * simple es que la metas a la session: $request->getSession()->set('transactionRecord', $transactionRecord)
         *
            y como sabrias cuando esta lista la consulta y puedes leer los campos?

        Porque desde tu mierda vas a invocar a la url 2, que es el callback final, y ese callback va a ser una rua dentro de
        la app de powerperalta. Ahi es donde yo voy a leer el transactionrecord desde la session y ver si es que viene algun codigo de error
        o no. Pero en ese punto tu ya deberias haber preparado el transacionrecordy haber guardado todo lo pertinente
         *
         * cachai? si, todo
         * entonces aca yo creo que tienes que hacer dos rutas
         * la wea para llegar, que podria ser esta, desde aca invokas a webpay y redireccionas y blah.
         * y otra que sea donde guardas las weas.
         *
         *
         *
         *
         */
    }

    /**
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
            return $this->render(
                '@GabrielCorreaWebpay/redirectWebpay.html.twig',
                [
                    'redirect' => 'Tu pago fue rechazado',
                ]
            );
        } catch (TransactionResultException $exception) {
            return $this->render(
                '@GabrielCorreaWebpay/redirectWebpay.html.twig',
                [
                    'redirect' => 'Hubo un error tratando de construir tu transaction',
                ]
            );
        } catch (AcknowledgeTransactionException $exception) {
            return $this->render(
                '@GabrielCorreaWebpay/redirectWebpay.html.twig',
                [
                    'redirect' => 'Hubo un error al registrar tu transaction en webpay',
                ]
            );
        }

        return new Response($response);

        //$request->getSession()->set('transactionRecord', $transactionRecord);

        // aca haces la wea del acknowledge. tay claro ? si super

        //tenia dudas de la interaccion a traves de los controladores contigo, no cachab como leerias las respuestas
        /*
         * A webpay le tienes que pasar dos rutas cierto? si , la final y la qe uno recibe la informaicon de la transaccion
         * Donde le seteas esas rutas?
         */
    }

}