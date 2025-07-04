<?php

namespace App\Controller\Admin;

use App\Entity\AttributeName;
use App\Entity\AttributeValue;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\ProductVariant;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
//        return parent::index();

        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        return $this->redirectToRoute('admin_product_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Catalog Etis Ro');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToCrud('Produse', 'fas fa-list', Product::class);
        yield MenuItem::linkToCrud('Variatii', 'fas fa-list', ProductVariant::class);
        yield MenuItem::linkToCrud('Nume atribute', 'fas fa-list', AttributeName::class);
        yield MenuItem::linkToCrud('Valori atribute', 'fas fa-list', AttributeValue::class);
        yield MenuItem::linkToCrud('Categorii', 'fas fa-list', Category::class);

        // Product management section
        yield MenuItem::section('Product Management');
        yield MenuItem::linkToRoute('Import Products', 'fas fa-file-import', 'product_import_form');
        yield MenuItem::linkToRoute('Update Products', 'fas fa-file-upload', 'product_update_form');

    }
}
