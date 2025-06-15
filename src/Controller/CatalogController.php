<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Nucleos\DompdfBundle\Factory\DompdfFactoryInterface;
use Nucleos\DompdfBundle\Wrapper\DompdfWrapperInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CatalogController extends AbstractController
{
    private DompdfFactoryInterface $dompdfFactory;
    private DompdfWrapperInterface $dompdfWrapper;
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;

    private const EUR_RATE = 5.03;

    public function __construct(
        DompdfFactoryInterface $dompdfFactory,
        DompdfWrapperInterface $dompdfWrapper,
        ProductRepository      $productRepository,
        CategoryRepository     $categoryRepository
    )
    {
        $this->dompdfFactory = $dompdfFactory;
        $this->dompdfWrapper = $dompdfWrapper;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/catalog/pdf', name: 'catalog_full_pdf')]
    public function generateFullCatalog(): Response
    {
        $products = $this->productRepository->findAll();

        $html = $this->renderView('catalog/pdf.html.twig', [
            'products' => $products,
            'title' => 'Catalog Produse - ETIS',
            'EUR_RATE' => self::EUR_RATE
        ]);

        // Using the wrapper for easier PDF generation
        return $this->dompdfWrapper->getStreamResponse($html, 'catalog-full.pdf');
    }

    #[Route('/', name: 'catalogs_index')]
    public function index(): Response
    {
        $categories = $this->categoryRepository->findAll();
        $totalProducts = count($this->productRepository->findAll());

        return $this->render('catalog/index.html.twig', [
            'categories' => $categories,
            'totalProducts' => $totalProducts
        ]);
    }

    #[Route('/catalog/category/{id}/pdf', name: 'catalog_category_pdf')]
    public function generateCategoryCatalog(Category $category): Response
    {
        $products = $this->productRepository->findByCategory($category);

        $html = $this->renderView('catalog/pdf.html.twig', [
            'products' => $products,
            'title' => 'Catalog ' . $category->getName(),
            'category' => $category,
            'EUR_RATE' => self::EUR_RATE
        ]);

        $filename = sprintf('catalog-%s.pdf', $category->getName() ?? $category->getId());

        // Using the wrapper for easier PDF generation
        return $this->dompdfWrapper->getStreamResponse($html, $filename);
    }

    #[Route('/catalog/pdf/advanced', name: 'catalog_advanced_pdf')]
    public function generateAdvancedCatalog(): Response
    {
        $products = $this->productRepository->findAll();

        $html = $this->renderView('catalog/pdf.html.twig', [
            'products' => $products,
            'title' => 'Advanced Product Catalog',
            'EUR_RATE' => self::EUR_RATE
        ]);

        // Using the factory for more control over options
        $dompdf = $this->dompdfFactory->create([
            'isRemoteEnabled' => true,
            'chroot' => $this->getParameter('kernel.project_dir') . '/public',
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait'
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="catalog-advanced.pdf"'
            ]
        );
    }
}
