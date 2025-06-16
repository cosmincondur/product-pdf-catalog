<?php

namespace App\Service;

use App\Entity\Product;
use App\Entity\ProductVariant;
use App\Entity\AttributeName;
use App\Entity\AttributeValue;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ProductImportService
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function importFromFile(UploadedFile $file): array
    {
        $results = [
            'success' => 0,
            'errors' => [],
            'products_created' => 0,
            'variants_created' => 0
        ];

        try {
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Skip header row
            array_shift($rows);

            $products = [];
            $rowIndex = 2; // Starting from row 2 (after header)

            foreach ($rows as $row) {
                try {
                    $this->processRow($row, $products, $results);
                } catch (\Exception $e) {
                    $results['errors'][] = "Row {$rowIndex}: " . $e->getMessage();
                }
                $rowIndex++;
            }

            // Save all products and variants
            foreach ($products as $product) {
                $this->entityManager->persist($product);
                $results['products_created']++;

                foreach ($product->getVariants() as $variant) {
                    $results['variants_created']++;
                }
            }

            $this->entityManager->flush();
            $results['success'] = 1;

        } catch (\Exception $e) {
            $results['errors'][] = 'File processing error: ' . $e->getMessage();
        }

        return $results;
    }

    private function processRow(array $row, array &$products, array &$results): void
    {
        // Expected columns: product_name, category_name, variant_sku, variant_name,
        // variant_price, attribute_name_1, attribute_value_1, attribute_name_2, attribute_value_2, etc.

        if (empty($row[0]) || empty($row[2])) {
            throw new \Exception('Product name and Variant SKU are required');
        }

        $productName = trim($row[0]);
        $categoryName = trim($row[1] ?? '');
        $variantSku = trim($row[2]);
        $variantName = trim($row[3] ?? '');
        $variantPrice = floatval($row[4] ?? 0);

        // Find or create product
        $product = $this->findOrCreateProduct($productName, $categoryName, $products);

        // Create variant
        $variant = new ProductVariant();
        $variant->setSku($variantSku);
        $variant->setName($variantName);
        $variant->setPrice($variantPrice);
        $variant->setParent($product);

        // Process attributes (columns 5+ should be pairs of attribute_name, attribute_value)
        $this->processVariantAttributes($variant, $row, 5);

        $product->addVariant($variant);
    }

    private function findOrCreateProduct(string $name, string $categoryName, array &$products): Product
    {
        // Use product name as key since there's no SKU in your Product entity
        $productKey = $name;

        // Check if product already exists in our current batch
        if (isset($products[$productKey])) {
            return $products[$productKey];
        }

        // Check if product exists in database
        $existingProduct = $this->entityManager
            ->getRepository(Product::class)
            ->findOneBy(['name' => $name]);

        if ($existingProduct) {
            $products[$productKey] = $existingProduct;
            return $existingProduct;
        }

        // Create new product
        $product = new Product();
        $product->setName($name);

        // Find or create category
        if (!empty($categoryName)) {
            $category = $this->findOrCreateCategory($categoryName);
            $product->setCategory($category);
        }

        $products[$productKey] = $product;
        return $product;
    }

    private function findOrCreateCategory(string $categoryName): Category
    {
        $category = $this->entityManager
            ->getRepository(Category::class)
            ->findOneBy(['name' => $categoryName]);

        if (!$category) {
            $category = new Category();
            $category->setName($categoryName);
            $this->entityManager->persist($category);
        }

        return $category;
    }

    private function processVariantAttributes(ProductVariant $variant, array $row, int $startIndex): void
    {
        $attributeNameRepo = $this->entityManager->getRepository(AttributeName::class);
        $attributeValueRepo = $this->entityManager->getRepository(AttributeValue::class);

        // Process attributes in pairs (name, value)
        for ($i = $startIndex; $i < count($row); $i += 2) {
            if (empty($row[$i]) || !isset($row[$i + 1])) {
                continue;
            }

            $attributeNameText = trim($row[$i]);
            $attributeValueText = trim($row[$i + 1]);

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

            // Link attribute name and value (ManyToMany relationship)
            $attributeValue->addAttributeName($attributeName);
            $attributeName->addValue($attributeValue);

            // Associate attribute value with variant
            $variant->addAttribute($attributeValue);
        }
    }
}
