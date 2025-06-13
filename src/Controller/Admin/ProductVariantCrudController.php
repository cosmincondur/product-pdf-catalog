<?php

namespace App\Controller\Admin;

use App\Entity\ProductVariant;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductVariantCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ProductVariant::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextField::new('sku'),
            MoneyField::new('price')->setCurrency('EUR'),
            AssociationField::new('parent')->autocomplete(),
            AssociationField::new('attributes')->autocomplete(),
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        // return parent::configureFilters($filters); // initial
        return $filters
            ->add('name')
            ->add('sku')
            ->add('price')
            ->add('parent')
            ->add('attributes');
    }
}
