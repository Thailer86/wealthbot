<?php
/**
 * Created by JetBrains PhpStorm.
 * User: amalyuhin
 * Date: 27.03.13
 * Time: 14:31
 * To change this template use File | Settings | File Templates.
 */

namespace Wealthbot\ClientBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Type;
use Wealthbot\ClientBundle\Entity\BankInformation;
use Wealthbot\ClientBundle\Form\Validator\BankInformationFormValidator;

class BankInformationFormType extends AbstractType
{
    private $isPreSaved;

    public function __construct($isPreSaved = false)
    {
        $this->isPreSaved = $isPreSaved;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('account_owner_first_name', TextType::class, ['required' => false])
            ->add('account_owner_middle_name', TextType::class, ['required' => false])
            ->add('account_owner_last_name', TextType::class, ['required' => false])
            ->add('joint_account_owner_first_name', TextType::class, ['required' => false])
            ->add('joint_account_owner_middle_name', TextType::class, ['required' => false])
            ->add('joint_account_owner_last_name', TextType::class, ['required' => false])
            ->add('name', TextType::class, ['required' => false])
            ->add('account_title', TextType::class, ['required' => false])
            ->add('phone_number', TextType::class, ['required' => false])
            ->add('routing_number', TextType::class, [
                'constraints' => [
                    new Type(['type' => 'numeric']),
                ],
                'required' => false,
            ])
            ->add('account_number', TextType::class, [
                'constraints' => [
                    new Type(['type' => 'numeric']),
                ],
                'required' => false,
            ])
            ->add('account_type', ChoiceType::class, [
                'choices' => BankInformation::getAccountTypeChoices(),
                'expanded' => true,
                'multiple' => false,
                'required' => false,
            ])
            ->add('pdfDocument', new PdfDocumentFormType());

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'onSubmit']);
    }

    public function onSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();

        $cleanedPhoneNumber = str_replace([' ', '-', '(', ')'], '', $data->getPhoneNumber());
        $data->setPhoneNumber($cleanedPhoneNumber);

        $bankInformationValidator = new BankInformationFormValidator($form, $data);
        $bankInformationValidator->validate();
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'Wealthbot\ClientBundle\Entity\BankInformation',
        ]);
    }

    public function getBlockPrefix()
    {
        return 'bank_information';
    }
}
