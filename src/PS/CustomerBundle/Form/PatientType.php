<?php

namespace PS\CustomerBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormError;



class PatientType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        PersonFormUtils::addPrimaryInfo($builder);
        $builder 
            ->add('sex',ChoiceType::class,
                array(
                    'label' => 'sex',
                    'choices' => array(
                        'male' => 'male',
                        'female' => 'female'),
                    'multiple'=>false,'expanded'=>true
                    ))
            ->add('codeSiblings',        TextType::class,  array('required' => false, 'label' => 'patient.code.siblings'))
            ->add('comment',        TextType::class,  array('required' => false, 'label' => 'comment'))
        ;
        
        PersonFormUtils::builParentTypeForm($builder,"mother"); 
        PersonFormUtils::buildAddressTypeForm($builder,"mother"); 
        
        PersonFormUtils::builParentTypeForm($builder,"father");  

         $builder->add('save', SubmitType::class, array('label' => 'save'));
     
        
        // VALIDATING NON MAPPED FIELD Symfony 2.1.2 way (and forward)
        // http://stackoverflow.com/questions/12911686/symfony-validate-form-with-mapped-false-form-fields
        /** @var \closure $myExtraFieldValidator **/
        $myNonMappedFieldsValidator = function(FormEvent $event){
            $form = $event->getForm();
            $motherNewField = $form->get('mother_new')->getData();
            $fatherNewField = $form->get('father_new')->getData();

            if (isset($motherNewField) and !$motherNewField->isPersonValid()) {
              $form['mother_new']->addError(new FormError("Lors de la création d'une nouvelle Mère, son Nom* doit obligatoirement être renseigné"));
            }
            if (isset($fatherNewField) and !$fatherNewField->isPersonValid()) {
              $form['father_new']->addError(new FormError("Lors de la création d'un nouveau Père, son Nom* doit obligatoirement être renseigné"));
            }
        };

        // adding the validator to the FormBuilderInterface
        $builder->addEventListener(FormEvents::POST_SUBMIT, $myNonMappedFieldsValidator);
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'PS\CustomerBundle\Entity\Patient'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ps_customerbundle_patient';
    }


}
