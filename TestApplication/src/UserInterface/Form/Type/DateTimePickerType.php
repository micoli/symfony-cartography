<?php

declare(strict_types=1);

namespace App\UserInterface\Form\Type;

use App\Infrastructure\Utils\MomentFormatConverter;
use Locale;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\String\u;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
final class DateTimePickerType extends AbstractType
{
    public function __construct(
        private MomentFormatConverter $formatConverter,
    ) {
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /** @var string $format */
        $format = $options['format'];

        /**
         * @var array{attr:array{data-date-format:string, data-date-locale: string}} $view->vars
         */
        $view->vars['attr']['data-date-format'] = $this->formatConverter->convert($format);
        $view->vars['attr']['data-date-locale'] = u(Locale::getDefault())->replace('_', '-')->lower();
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'widget' => 'single_text',
            'html5' => false,
        ]);
    }

    public function getParent(): ?string
    {
        return DateTimeType::class;
    }
}
