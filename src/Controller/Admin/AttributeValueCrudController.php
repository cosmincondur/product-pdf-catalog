<?php

namespace App\Controller\Admin;

use App\Entity\AttributeValue;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use function Sodium\add;

class AttributeValueCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AttributeValue::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('value'),
            TextField::new('unit'),
            AssociationField::new('attributeNames')->autocomplete(),
            AssociationField::new('productVariants')->autocomplete(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('value')
            ->add('unit')
            ->add('attributeNames')
            ->add('productVariants');
    }
}
