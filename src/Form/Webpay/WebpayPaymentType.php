<?php
/**
 * Created by PhpStorm.
 * User: gabo
 * Date: 07-12-18
 * Time: 7:41
 */

namespace GabrielCorrea\WebpayBundle\Form\Webpay;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class WebpayPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'El monto de la transacción es inválido.'
                    ])]
            ])
            ->add('buyOrder', HiddenType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'El identificador de la transacción es inválido.'
                    ]),
                    new Length([
                        'minMessage' => 'El identificador de la transacción es inválido.',
                        'min' => 1,
                        'max' => 26
                    ])]
            ]);
    }
}