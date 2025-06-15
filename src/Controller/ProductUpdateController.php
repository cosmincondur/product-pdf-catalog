<?php

namespace App\Controller;

use App\Service\ProductUpdateService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class ProductUpdateController extends AbstractController
{
    private ProductUpdateService $updateService;

    public function __construct(ProductUpdateService $updateService)
    {
        $this->updateService = $updateService;
    }

    #[Route('/admin/products/update', name: 'product_update_form', methods: ['GET'])]
    public function updateForm(): Response
    {
        return $this->render('admin/product/update.html.twig');
    }

    #[Route('/admin/products/update', name: 'product_update_process', methods: ['POST'])]
    public function processUpdate(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('update_file');

        if (!$file) {
            return new JsonResponse([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);
        }

        $allowedTypes = ['xlsx', 'xls', 'csv'];
        $extension = strtolower($file->getClientOriginalExtension());

        if (!in_array($extension, $allowedTypes)) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes)
            ], 400);
        }

        try {
            $results = $this->updateService->updateFromFile($file);

            if ($results['success']) {
                return new JsonResponse([
                    'success' => true,
                    'message' => sprintf(
                        'Update completed successfully! Updated %d variants.',
                        $results['variants_updated']
                    ),
                    'details' => $results
                ]);
            } else {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'Update failed',
                    'errors' => $results['errors']
                ], 400);
            }

        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/admin/products/update/template', name: 'product_update_template', methods: ['GET'])]
    public function downloadTemplate(): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers for update template
        $headers = [
            'variant_sku',        // Required for updating
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
            ['TSHIRT-RED-S', 'Red Small T-Shirt', 21.99, 'Color', 'Red', 'Size', 'S'],
            ['TSHIRT-RED-M', 'Red Medium T-Shirt', 22.99, 'Color', 'Red', 'Size', 'M'],
            ['JEANS-32', 'Jeans 32 Inch', 64.99, 'Waist', '32', '', ''],
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Auto-size columns
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Add notes about required fields
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Instructions');
        $sheet->setCellValue('A1', 'Instructions for updating variants:');
        $sheet->setCellValue('A2', '1. variant_sku is required for updating existing variants');
        $sheet->setCellValue('A3', '2. Leave fields empty if you don\'t want to update them');
        $sheet->setCellValue('A4', '3. All changes will be applied to existing variants only');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment; filename="variant_update_template.xlsx"');

        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        $response->setContent($content);
        return $response;
    }
}
