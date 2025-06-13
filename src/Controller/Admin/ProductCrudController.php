<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductVariant;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
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
            AssociationField::new('category')
                ->setLabel('Categorie'),
            CollectionField::new('variants')
                ->useEntryCrudForm() // This will use the ProductVariant CRUD
                ->allowAdd()
                ->allowDelete()
                ->setEntryIsComplex(true), // Required for nested entities,
            ImageField::new('imageName')
                ->setLabel('Imagine produs')
                ->setUploadDir('public/images/products')
                ->setBasePath('/images/products')
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
