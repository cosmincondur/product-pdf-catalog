<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\AttributeName;
use App\Entity\AttributeValue;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductUpdateService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateFromFile(UploadedFile $file): array
    {
        $results = [
            'success' => false,
            'errors' => [],
            'variants_updated' => 0
        ];

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            array_shift($rows);

            $rowIndex = 2;
            foreach ($rows as $row) {
                try {
                    $this->processUpdateRow($row, $results);
                } catch (\Exception $e) {
                    $results['errors'][] = "Row {$rowIndex}: " . $e->getMessage();
                }
                $rowIndex++;
            }

            $this->entityManager->flush();
            $results['success'] = empty($results['errors']);

        } catch (\Exception $e) {
            $results['errors'][] = 'File processing error: ' . $e->getMessage();
        }

        return $results;
    }

    private function processUpdateRow(array $row, array &$results): void
    {
        $variantSku = trim($row[0] ?? '');
        $rawPrice = $row[1] ?? null;
        $variantPrice = null;

        if ($rawPrice !== null && $rawPrice !== '') {
            // Multiply by 100 to counteract the division happening somewhere
            $variantPrice = (float)$rawPrice * 100;

            error_log(sprintf(
                'Price debug: Original=%s, Type=%s, Converted=%f',
                $rawPrice,
                gettype($rawPrice),
                $variantPrice
            ));
        }

        if (empty($variantSku)) {
            throw new \Exception('Variant SKU is required for updates');
        }

        $variant = $this->entityManager->getRepository(ProductVariant::class)->findOneBy(['sku' => $variantSku]);
        if (!$variant) {
            throw new \Exception("Variant with SKU '{$variantSku}' not found");
        }

        if ($variantPrice !== null) {
            $oldPrice = $variant->getPrice();
            $variant->setPrice($variantPrice);
            error_log(sprintf('Price update: SKU=%s, Old=%f, New=%f', $variantSku, $oldPrice, $variantPrice));
            $results['variants_updated']++;
        }

        $this->entityManager->persist($variant);
    }

    private function updateVariantAttributes(ProductVariant $variant, array $attributeData): void
    {
        $attributeNameRepo = $this->entityManager->getRepository(AttributeName::class);
        $attributeValueRepo = $this->entityManager->getRepository(AttributeValue::class);

        // Process attributes in pairs (name, value)
        for ($i = 0; $i < count($attributeData); $i += 2) {
            if (empty($attributeData[$i]) || !isset($attributeData[$i + 1])) {
                continue;
            }

            $attributeNameText = trim($attributeData[$i]);
            $attributeValueText = trim($attributeData[$i + 1]);

            if (empty($attributeNameText) || empty($attributeValueText)) {
                continue;
            }

            // Find or create attribute name
            $attributeName = $attributeNameRepo->findOneBy(['name' => $attributeNameText]);
            if (!$attributeName) {
                $attributeName = new AttributeName();
                $attributeName->setName($attributeNameText);
                $this->entityManager->persist($attributeName);
            }

            // Find or create attribute value
            $attributeValue = $attributeValueRepo->findOneBy(['value' => $attributeValueText]);
            if (!$attributeValue) {
                $attributeValue = new AttributeValue();
                $attributeValue->setValue($attributeValueText);
                $this->entityManager->persist($attributeValue);
            }

            // Update variant attributes
            if (!$variant->hasAttribute($attributeValue)) {
                // Remove existing attributes with the same name
                foreach ($variant->getAttributes() as $existingAttribute) {
                    if ($existingAttribute->getAttributeNames()->contains($attributeName)) {
                        $variant->removeAttribute($existingAttribute);
                    }
                }

                // Add new attribute value
                $attributeValue->addAttributeName($attributeName);
                $variant->addAttribute($attributeValue);
            }
        }
    }
}

//
//namespace App\Service;
//
//use App\Entity\Product;
//use App\Entity\ProductVariant;
//use App\Entity\AttributeName;
//use App\Entity\AttributeValue;
//use App\Entity\Category;
//use Doctrine\ORM\EntityManagerInterface;
//use PhpOffice\PhpSpreadsheet\IOFactory;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
//
//class ProductUpdateService
//{
//    private EntityManagerInterface $entityManager;
//
//    public function __construct(EntityManagerInterface $entityManager)
//    {
//        $this->entityManager = $entityManager;
//    }
//
//    public function updateFromFile(UploadedFile $file): array
//    {
//        $results = [
//            'success' => false,
//            'errors' => [],
//            'products_updated' => 0,
//            'variants_updated' => 0
//        ];
//
//        try {
//            $spreadsheet = IOFactory::load($file->getPathname());
//            $worksheet = $spreadsheet->getActiveSheet();
//            $rows = $worksheet->toArray();
//
//            // Skip header row
//            array_shift($rows);
//
//            $rowIndex = 2; // Starting from row 2 (after header)
//
//            foreach ($rows as $row) {
//                try {
//                    $this->processUpdateRow($row, $results);
//                } catch (\Exception $e) {
//                    $results['errors'][] = "Row {$rowIndex}: " . $e->getMessage();
//                }
//                $rowIndex++;
//            }
//
//            $this->entityManager->flush();
//            $results['success'] = empty($results['errors']);
//
//        } catch (\Exception $e) {
//            $results['errors'][] = 'File processing error: ' . $e->getMessage();
//        }
//
//        return $results;
//    }
//
//    private function processUpdateRow(array $row, array &$results): void
//    {
//        // Expected columns: product_id, product_name, category_name, variant_id, variant_sku,
//        // variant_name, variant_price, color_name, color_value, size_name, size_value
//
//        $productId = (int)($row[0] ?? 0);
//        $productName = trim($row[1] ?? '');
//        $categoryName = trim($row[2] ?? '');
//        $variantId = (int)($row[3] ?? 0);
//        $variantSku = trim($row[4] ?? '');
//        $variantName = trim($row[5] ?? '');
//        $variantPrice = !empty($row[6]) ? floatval($row[6]) : null;
//
//        if (!$productId || !$variantId) {
//            throw new \Exception('Product ID and Variant ID are required for updates');
//        }
//
//        // Find product
//        $product = $this->entityManager->getRepository(Product::class)->find($productId);
//        if (!$product) {
//            throw new \Exception("Product with ID {$productId} not found");
//        }
//
//        // Update product if values provided
//        $productUpdated = false;
//        if (!empty($productName) && $product->getName() !== $productName) {
//            $product->setName($productName);
//            $productUpdated = true;
//        }
//
//        if (!empty($categoryName)) {
//            $category = $this->findOrCreateCategory($categoryName);
//            if ($product->getCategory()?->getName() !== $categoryName) {
//                $product->setCategory($category);
//                $productUpdated = true;
//            }
//        }
//
//        if ($productUpdated) {
//            $results['products_updated']++;
//        }
//
//        // Find and update variant
//        $variant = $this->entityManager->getRepository(ProductVariant::class)->find($variantId);
//        if (!$variant) {
//            throw new \Exception("Variant with ID {$variantId} not found");
//        }
//
//        // Verify variant belongs to product
//        if ($variant->getParent()->getId() !== $product->getId()) {
//            throw new \Exception("Variant {$variantId} does not belong to product {$productId}");
//        }
//
//        // Update variant if values provided
//        $variantUpdated = false;
//        if (!empty($variantSku) && $variant->getSku() !== $variantSku) {
//            $variant->setSku($variantSku);
//            $variantUpdated = true;
//        }
//
//        if (!empty($variantName) && $variant->getName() !== $variantName) {
//            $variant->setName($variantName);
//            $variantUpdated = true;
//        }
//
//        if ($variantPrice !== null && $variant->getPrice() !== $variantPrice) {
//            $variant->setPrice($variantPrice);
//            $variantUpdated = true;
//        }
//
//        // Process attributes if provided (starting from index 7)
//        if (isset($row[7]) && isset($row[8])) {
//            $this->updateVariantAttributes($variant, array_slice($row, 7));
//            $variantUpdated = true;
//        }
//
//        if ($variantUpdated) {
//            $results['variants_updated']++;
//        }
//    }
//
//    private function findOrCreateCategory(string $categoryName): Category
//    {
//        $category = $this->entityManager
//            ->getRepository(Category::class)
//            ->findOneBy(['name' => $categoryName]);
//
//        if (!$category) {
//            $category = new Category();
//            $category->setName($categoryName);
//            $this->entityManager->persist($category);
//        }
//
//        return $category;
//    }
//
//    private function updateVariantAttributes(ProductVariant $variant, array $attributeData): void
//    {
//        $attributeNameRepo = $this->entityManager->getRepository(AttributeName::class);
//        $attributeValueRepo = $this->entityManager->getRepository(AttributeValue::class);
//
//        // Process attributes in pairs (name, value)
//        for ($i = 0; $i < count($attributeData); $i += 2) {
//            if (empty($attributeData[$i]) || !isset($attributeData[$i + 1])) {
//                continue;
//            }
//
//            $attributeNameText = trim($attributeData[$i]);
//            $attributeValueText = trim($attributeData[$i + 1]);
//
//            if (empty($attributeNameText) || empty($attributeValueText)) {
//                continue;
//            }
//
//            // Find or create attribute name
//            $attributeName = $attributeNameRepo->findOneBy(['name' => $attributeNameText]);
//            if (!$attributeName) {
//                $attributeName = new AttributeName();
//                $attributeName->setName($attributeNameText);
//                $this->entityManager->persist($attributeName);
//            }
//
//            // Find or create attribute value
//            $attributeValue = $attributeValueRepo->findOneBy(['value' => $attributeValueText]);
//            if (!$attributeValue) {
//                $attributeValue = new AttributeValue();
//                $attributeValue->setValue($attributeValueText);
//                $this->entityManager->persist($attributeValue);
//            }
//
//            // Update variant attributes
//            if (!$variant->hasAttribute($attributeValue)) {
//                // Remove existing attributes with the same name
//                foreach ($variant->getAttributes() as $existingAttribute) {
//                    if ($existingAttribute->getAttributeNames()->contains($attributeName)) {
//                        $variant->removeAttribute($existingAttribute);
//                    }
//                }
//
//                // Add new attribute value
//                $attributeValue->addAttributeName($attributeName);
//                $variant->addAttribute($attributeValue);
//            }
//        }
//    }
//}
