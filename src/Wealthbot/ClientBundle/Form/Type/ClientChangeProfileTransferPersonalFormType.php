<?php

namespace Wealthbot\ClientBundle\Form\Type;

use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Wealthbot\ClientBundle\Form\Validator\ClientSpouseFormValidator;
use Wealthbot\ClientBundle\Model\AccountOwnerInterface;
use Wealthbot\ClientBundle\Model\UserAccountOwnerAdapter;
use Wealthbot\UserBundle\Entity\Profile;

class ClientChangeProfileTransferPersonalFormType extends AccountOwnerPersonalInformationFormType
{
    private $em;

    public function __construct(EntityManager $em, AccountOwnerInterface $owner, $isPreSaved = false, $withMaritalStatus = false)
    {
        parent::__construct($owner, $isPreSaved, $withMaritalStatus);
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        $data = $builder->getData();

        $isExist = $data->getId();

        $builder
            ->remove('citezen')
            ->remove('ssn_tin_1')
            ->remove('ssn_tin_2')
            ->remove('ssn_tin_3')
            ->remove('is_senior_political_figure')
            ->remove('senior_spf_name')
            ->remove('senior_political_title')
            ->remove('senior_account_owner_relationship')
            ->remove('senior_country_office')
            ->remove('is_publicly_traded_company')
            ->remove('publicle_company_name')
            ->remove('publicle_address')
            ->remove('publicle_city')
            ->remove('publicleState')
            ->remove('is_broker_security_exchange_person')
            ->remove('broker_security_exchange_company_name')
            ->remove('compliance_letter_file')
            ->add('email', 'email')
            ->add('first_name', 'text', [
                'required' => false,
                'disabled' => true,
            ])
            ->add('middle_name', 'text', [
                'required' => false,
                'disabled' => true,
            ])
            ->add('last_name', 'text', [
                'required' => false,
                'disabled' => true,
            ])
            ->add('birth_date', 'date', [
                    'widget' => 'single_text',
                    'format' => 'MM-dd-yyyy',
                    'required' => true,
                    'disabled' => true,
                    'attr' => ['class' => 'jq-date input-small'],
                ])
            ->add('citizenship', 'choice', [
                'choices' => [
                    1 => 'Yes',
                    0 => 'No',
                ],
                'expanded' => true,
                'multiple' => false,
                'required' => false,
                'mapped' => false,
                'data' => $isExist ? 1 : null,
                'label' => ($data && $data->getMaritalStatus() === 'Married' ? 'Are you and your spouse both U.S. citizens?' : 'Are you a U.S. citizen?'),
                'disabled' => true,
            ])
            ->add('marital_status', 'choice', [
                'choices' => Profile::getMaritalStatusChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('phone_number', 'text', ['required' => false])
            ->add('spouse', new ClientSpouseFormType())
            ->add('annual_income', 'choice', [
                'choices' => Profile::getAnnualIncomeChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
            ->add('estimated_income_tax', 'percent', [
                'precision' => 0,
                'required' => false,
                'label' => 'What is your estimated income tax bracket?',
            ])
            ->add('liquid_net_worth', 'choice', [
                'choices' => Profile::getLiquidNetWorthChoices(),
                'placeholder' => 'Choose an Option',
                'required' => false,
            ])
        ;

        $formFactory = $builder->getFormFactory();

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($formFactory) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data === null) {
                return;
            }

            if (isset($data['marital_status'])) {
                $form->remove('citizenship');
                $form->add($formFactory->createNamed(
                    'citizenship',
                    'choice',
                    null,
                    [
                        'choices' => [
                            1 => 'Yes',
                            0 => 'No',
                        ],
                        'expanded' => true,
                        'multiple' => false,
                        'required' => false,
                        'mapped' => false,
                        'data' => (isset($data['id']) ? 1 : null),
                        'label' => ($data['marital_status'] === 'Married' ? 'Are you and your spouse both U.S. citizens?' : 'Are you a U.S. citizen?'),
                        'disabled' => true,
                        'auto_initialize' => false,
                    ])
                );
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, [$this, 'changeProfileValidate']);
    }

    public function changeProfileValidate(FormEvent $event)
    {
        /** @var UserAccountOwnerAdapter $data */
        $data = $event->getData();
        $form = $event->getForm();

        if ($form->has('spouse') && $data->getMaritalStatus() === Profile::CLIENT_MARITAL_STATUS_MARRIED) {
            $spouseValidator = new ClientSpouseFormValidator($form->get('spouse'), $data->getSpouse());
            $spouseValidator->validate();
        }

        $phoneDigits = 10;
        $phoneNum = str_replace([' ', '-', '(', ')'], '', $data->getPhoneNumber());

        if ($form->has('phone_number') && !is_numeric($phoneNum)) {
            $form->get('phone_number')->addError(new FormError('Enter correct phone number.'));
        } elseif ($form->has('phone_number') && strlen($phoneNum) !== $phoneDigits) {
            $form->get('phone_number')->addError(new FormError("Phone number must be {$phoneDigits} digits."));
        }

        if ($form->has('email')) {
            if (!filter_var($data->getEmail(), FILTER_VALIDATE_EMAIL)) {
                $form->get('email')->addError(new FormError('Invalid email address.'));
            }

            $exist = $this->em->getRepository('WealthbotUserBundle:User')->findOneBy(['email' => $data->getEmail()]);
            if ($exist && $exist->getId() !== $data->getId()) {
                $form->get('email')->addError(new FormError('Email address already exist.'));
            }
        }
    }
}
