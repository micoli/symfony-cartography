<?php

declare(strict_types=1);

namespace App\UserInterface\Form\Type;

use App\Domain\Entity\Tag;
use App\Domain\Repository\TagRepositoryInterface;
use App\UserInterface\Form\DataTransformer\TagArrayToStringTransformer;
use Symfony\Bridge\Doctrine\Form\DataTransformer\CollectionToArrayTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * @author Yonel Ceruto <yonelceruto@gmail.com>
 */
final class TagsInputType extends AbstractType
{
    public function __construct(
        private TagRepositoryInterface $tags,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addModelTransformer(new CollectionToArrayTransformer(), true)
            ->addModelTransformer(new TagArrayToStringTransformer($this->tags), true)
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        /**
         * @var array{tags: list<Tag>} $view->vars
         */
        $view->vars['tags'] = $this->tags->findAll();
    }

    public function getParent(): ?string
    {
        return TextType::class;
    }
}
