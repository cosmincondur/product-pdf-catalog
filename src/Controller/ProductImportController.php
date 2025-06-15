<?php

namespace App\Controller;

use App\Service\ProductImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductImportController extends AbstractController
{
    private ProductImportService $importService;

    public function __construct(ProductImportService $importService)
    {
        $this->importService = $importService;
    }

    #[Route('/admin/products/import', name: 'product_import_form', methods: ['GET'])]
    public function importForm(): Response
    {
        return $this->render('admin/product/import.html.twig');
    }

    #[Route('/admin/products/import', name: 'product_import_process', methods: ['POST'])]
    public function processImport(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('import_file');

        if (!$file) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        }

        // Validate file type
        $allowedTypes = ['xlsx', 'xls', 'csv'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedTypes)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)
            ], 400);
        }

        try {
            $results = $this->importService->importFromFile($file);

            if ($results['success']) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf(
                        'Import completed successfully! Created %d products and %d variants.',
                        $results['products_created'],
                        $results['variants_created']
                    ),
                    'details' => $results
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Import failed',
                    'errors' => $results['errors']
                ], 400);
            }

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/admin/products/import/template', name: 'product_import_template', methods: ['GET'])]
    public function downloadTemplate(): Response
    {
        // Create a sample template
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'product_name',
            'category_name',
            'variant_sku',
            'variant_name',
            'variant_price',
            'color_name',
            'color_value',
            'size_name',
            'size_value'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        // Add sample data
        $sampleData = [
            ['T-Shirt Basic', 'Clothing', 'TSHIRT-RED-S', 'Red Small T-Shirt', 19.99, 'Color', 'Red', 'Size', 'S'],
            ['T-Shirt Basic', 'Clothing', 'TSHIRT-RED-M', 'Red Medium T-Shirt', 19.99, 'Color', 'Red', 'Size', 'M'],
            ['T-Shirt Basic', 'Clothing', 'TSHIRT-BLUE-S', 'Blue Small T-Shirt', 19.99, 'Color', 'Blue', 'Size', 'S'],
            ['Jeans Premium', 'Clothing', 'JEANS-32', 'Jeans 32 Inch', 59.99, 'Waist', '32', '', ''],
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Auto-size columns
        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="product_import_template.xlsx"');

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $response->setContent($content);
        return $response;
    }
}
