<?php

namespace FlexModel\FlexModelElasticsearchBundle\Form\Type;

use FlexModel\FlexModel;
use ReflectionClass;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * FilterFormType.
 *
 * @author Niels Nijens <niels@connectholland.nl>
 */
class FilterFormType extends AbstractType
{
    /**
     * The FlexModel instance.
     *
     * @var FlexModel
     */
    private $flexModel;

    /**
     * Constructs a new FlexModelFormType instance.
     *
     * @param FlexModel $flexModel
     */
    public function __construct(FlexModel $flexModel)
    {
        $this->flexModel = $flexModel;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data_class']) && isset($options['form_name'])) {
            $reflectionClass = new ReflectionClass($options['data_class']);
            $objectName = $reflectionClass->getShortName();

            $formConfiguration = $this->flexModel->getFormConfiguration($objectName, $options['form_name']);
            if (is_array($formConfiguration)) {
                foreach ($formConfiguration['fields'] as $formFieldConfiguration) {
                    $fieldConfiguration = $this->flexModel->getField($objectName, $formFieldConfiguration['name']);

                    $fieldType = ChoiceType::class;
                    $fieldOptions = array(
                        'label' => $fieldConfiguration['label'],
                        'required' => false,
                    );

                    if (isset($options['aggregation_results'][$formFieldConfiguration['name']])) {
                        $fieldOptions['choices'] = array();
                        $fieldOptions['multiple'] = true;
                        $fieldOptions['expanded'] = true;

                        foreach ($options['aggregation_results'][$formFieldConfiguration['name']] as $option) {
                            $fieldOptions['choices'][sprintf('%s (%d)', $option->getLabel(), $option->getCount())] = $option->getValue();
                        }
                    } else {
                        $fieldType = IntegerType::class;
                    }

                    $builder->add($fieldConfiguration['name'], $fieldType, $fieldOptions);
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefault('validation_groups', false);
        $resolver->setDefault('form_name', null);
        $resolver->setDefault('aggregation_results', array());
    }
}
