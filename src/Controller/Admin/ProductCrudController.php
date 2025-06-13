<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ProductCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Product::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            AssociationField::new('category'),
            CollectionField::new('variants'),
            ImageField::new('imageFile')
                ->setFormTypeOptions([
                    'allow_delete' => true,          // NEW: Enables delete functionality
                    'delete_label' => 'Delete photo', // NEW: Custom delete label
                ])
                ->setLabel('Imagine produs')
        ];
    }

    public function configureFilters(Filters $filters): Filters
    {
        //return parent::configureFilters($filters);
        return $filters
            ->add('name')
            ->add('category')
            ->add('variants');
    }
}
