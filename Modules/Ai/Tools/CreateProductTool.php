<?php

namespace Modules\Ai\Tools;

use NeuronAI\Tools\ToolProperty;
use NeuronAI\Tools\PropertyType;
use Modules\Product\Models\Product;

class CreateProductTool extends CreateContentTool
{
    public function __construct(protected array $dependencies = [])
    {
        parent::__construct($dependencies);
        $this->name = 'create_product';
        $this->description = 'Create new product for e-commerce';
    }

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'title',
                type: PropertyType::STRING,
                description: 'The product name/title',
                required: true
            ),
            new ToolProperty(
                name: 'description',
                type: PropertyType::STRING,
                description: 'Product description',
                required: true
            ),
            new ToolProperty(
                name: 'price',
                type: PropertyType::NUMBER,
                description: 'Product price',
                required: false
            ),
            new ToolProperty(
                name: 'url',
                type: PropertyType::STRING,
                description: 'Product URL slug for the product page (if not provided, it will be generated from the title)',
                required: false
            ),
            new ToolProperty(
                name: 'original_url',
                type: PropertyType::STRING,
                description: 'Product original URL',
                required: false
            ),
        ];
    }

    public function __invoke(...$args): string
    {
        // Extract parameters from args array using keys
        $title = $args['title'] ?? null;
        $description = $args['description'] ?? null;
        $price = $args['price'] ?? null;
        $url = $args['url'] ?? null;


        // Validate required parameters
        if (empty($title)) {
            return $this->handleError('Title is required for product creation.');
        }
        if (empty($description)) {
            return $this->handleError('Description is required for product creation.');
        }

        // Generate URL if not provided
        if (empty($url) && !empty($title)) {
            $url = $this->generateSlug($title);
        }

        // Create the product data
        $productData = [
            'title' => $title,
            'content' => $description,
            'description' => $description,
            'url' => $url,
            'content_type' => 'product',
            'subtype' => 'product',
            'is_active' => 1,

            'created_by' => user_id()
        ];

        $product = Product::create($productData);

//        // Handle price as custom field if provided
        if ($price !== null && function_exists('save_custom_field')) {
            save_custom_field([
                'field' => 'price',
                'value' => $price,
                'rel_type' => 'content',
                'rel_id' => $product->id,
                'type' => 'price'
            ]);
        }



        return $this->handleSuccess("Product created successfully with ID: {$product->id}") .
               $this->formatProductDetails($product, $price);
    }

    private function formatProductDetails($product, $price): string
    {
        return '
        <div class="card mt-3">
            <div class="card-header">
                <h5>Product Details</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>ID:</strong> ' . $product->id . '</p>
                        <p><strong>Title:</strong> ' . htmlspecialchars($product->title) . '</p>
                        <p><strong>URL:</strong> ' . htmlspecialchars($product->url) . '</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Price:</strong> ' . ($price ? '€' . number_format($price, 2) : 'N/A') . '</p>
                        <p><strong>Status:</strong> ' . ($product->is_active ? 'Published' : 'Draft') . '</p>
                        <p><strong>Created:</strong> ' . $product->created_at->format('Y-m-d H:i:s') . '</p>
                    </div>
                </div>
            </div>
        </div>';
    }
}
