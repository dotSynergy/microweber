<?php

declare(strict_types=1);

namespace Modules\Ai\Tools;

use Modules\Product\Models\Product;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class ProductListTool extends BaseTool
{
    protected string $domain = 'shop';
    protected array $requiredPermissions = ['view products'];

    public function __construct(protected array $dependencies = [])
    {
        parent::__construct(
            'product_list',
            'List products with comprehensive filtering options including price range, stock status, and categories.'
        );
    }

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'is_active',
                type: PropertyType::STRING,
                description: 'Filter by publication status. Options: "1" for published, "0" for unpublished, or "all" for both.',
                required: false,
            ),
            new ToolProperty(
                name: 'shop_id',
                type: PropertyType::INTEGER,
                description: 'Filter products by parent shop page ID.',
                required: false,
            ),
            new ToolProperty(
                name: 'search_term',
                type: PropertyType::STRING,
                description: 'Search term to find in product title, description, or SKU.',
                required: false,
            ),
            new ToolProperty(
                name: 'price_min',
                type: PropertyType::STRING,
                description: 'Minimum price filter (decimal number).',
                required: false,
            ),
            new ToolProperty(
                name: 'price_max',
                type: PropertyType::STRING,
                description: 'Maximum price filter (decimal number).',
                required: false,
            ),
            new ToolProperty(
                name: 'in_stock',
                type: PropertyType::STRING,
                description: 'Filter by stock status. Options: "yes" for in stock only, "no" for out of stock only, or "all" for both.',
                required: false,
            ),
            new ToolProperty(
                name: 'sort_by',
                type: PropertyType::STRING,
                description: 'Sort products by field. Options: "title", "price", "created_at", "position". Default is "position".',
                required: false,
            ),
            new ToolProperty(
                name: 'limit',
                type: PropertyType::INTEGER,
                description: 'Maximum number of results to return (1-50). Default is 20.',
                required: false,
            ),
        ];
    }

    public function __invoke(...$args): string
    {
        // Extract parameters from variadic args
        $is_active = $args[0] ?? 'all';
        $shop_id = $args[1] ?? null;
        $search_term = $args[2] ?? '';
        $price_min = $args[3] ?? '';
        $price_max = $args[4] ?? '';
        $in_stock = $args[5] ?? 'all';
        $sort_by = $args[6] ?? 'position';
        $limit = $args[7] ?? 20;

        if (!$this->authorize()) {
            return $this->handleError('You do not have permission to list products.');
        }

        // Validate limit
        $limit = max(1, min(50, $limit));

        try {
            $query = Product::query()
                ->where('is_deleted', 0);

            // Filter by active status
            if ($is_active !== 'all') {
                $query->where('is_active', (int)$is_active);
            }

            // Filter by shop page
            if ($shop_id !== null) {
                $query->where('parent', $shop_id);
            }

            // Search in content
            if (!empty($search_term)) {
                $query->where(function ($q) use ($search_term) {
                    $q->where('title', 'LIKE', '%' . $search_term . '%')
                      ->orWhere('content_body', 'LIKE', '%' . $search_term . '%')
                      ->orWhere('description', 'LIKE', '%' . $search_term . '%');
                    
                    // Search in content_data for SKU
                    $q->orWhereHas('contentData', function ($subQ) use ($search_term) {
                        $subQ->where('field_name', 'sku')
                             ->where('field_value', 'LIKE', '%' . $search_term . '%');
                    });
                });
            }

            // Price filtering
            if (!empty($price_min) && is_numeric($price_min)) {
                $query->where('price', '>=', (float)$price_min);
            }
            if (!empty($price_max) && is_numeric($price_max)) {
                $query->where('price', '<=', (float)$price_max);
            }

            // Stock filtering - this would depend on how stock is tracked in your system
            if ($in_stock !== 'all') {
                if ($in_stock === 'yes') {
                    // Assuming products without quantity tracking are always in stock
                    $query->where(function ($q) {
                        $q->whereDoesntHave('contentData', function ($subQ) {
                            $subQ->where('field_name', 'track_quantity');
                        })->orWhereHas('contentData', function ($subQ) {
                            $subQ->where('field_name', 'quantity')
                                 ->where('field_value', '>', 0);
                        });
                    });
                } elseif ($in_stock === 'no') {
                    $query->whereHas('contentData', function ($subQ) {
                        $subQ->where('field_name', 'quantity')
                             ->where('field_value', '<=', 0);
                    });
                }
            }

            // Sorting
            $validSortFields = ['title', 'price', 'created_at', 'position'];
            if (in_array($sort_by, $validSortFields)) {
                $query->orderBy($sort_by, $sort_by === 'created_at' ? 'desc' : 'asc');
            } else {
                $query->orderBy('position', 'asc');
            }

            $products = $query->limit($limit)->get();

            if ($products->isEmpty()) {
                $statusInfo = $is_active !== 'all' ? " with status '" . ($is_active ? 'published' : 'unpublished') . "'" : '';
                $searchInfo = !empty($search_term) ? " matching '{$search_term}'" : '';
                $priceInfo = '';
                if (!empty($price_min) || !empty($price_max)) {
                    $priceInfo = " in price range" . 
                        (!empty($price_min) ? " from €{$price_min}" : '') .
                        (!empty($price_max) ? " to €{$price_max}" : '');
                }
                
                return $this->formatAsHtmlTable(
                    [],
                    ['title' => 'Title', 'price' => 'Price', 'status' => 'Status'],
                    "No products found{$statusInfo}{$searchInfo}{$priceInfo}.",
                    'product-list-empty'
                );
            }

            return $this->formatProductsAsHtml($products, $is_active, $sort_by, $limit);

        } catch (\Exception $e) {
            return $this->handleError('Error listing products: ' . $e->getMessage());
        }
    }

    protected function formatProductsAsHtml($products, string $is_active, string $sort_by, int $limit): string
    {
        $totalFound = $products->count();
        
        $statusInfo = $is_active !== 'all' ? "Status: " . ($is_active ? 'Published' : 'Unpublished') . " " : '';
        $sortInfo = "Sorted by: " . ucfirst($sort_by) . " ";
        
        $header = "
        <div class='product-list-header mb-3'>
            <h4><i class='fas fa-box text-primary me-2'></i>Product Catalog</h4>
            <p class='mb-2'>
                {$statusInfo}{$sortInfo}
                <strong>Found:</strong> {$totalFound} product(s)" . 
                ($totalFound >= $limit ? " (showing first {$limit})" : '') . "
            </p>
        </div>";

        $cards = "<div class='row'>";
        
        foreach ($products as $product) {
            $statusBadge = $product->is_active ? 
                "<span class='badge bg-success'>Published</span>" : 
                "<span class='badge bg-warning'>Unpublished</span>";

            $price = $product->price ?? 0;
            $formattedPrice = "€" . number_format($price, 2);

            // Get special price if exists
            $specialPrice = '';
            try {
                if (method_exists($product, 'getSpecialPriceAttribute')) {
                    $special = $product->getSpecialPriceAttribute();
                    if ($special && $special < $price) {
                        $specialPrice = "<br><span class='text-danger'><s>€" . number_format($price, 2) . "</s></span>";
                        $formattedPrice = "<strong class='text-success'>€" . number_format($special, 2) . "</strong>";
                    }
                }
            } catch (\Exception $e) {
                // Ignore if special price method doesn't exist
            }

            $excerpt = $product->description ?: 
                       ($product->content_body ? \Str::limit(strip_tags($product->content_body), 100) : 'No description available');

            // Get SKU if available
            $sku = '';
            try {
                $skuData = $product->contentData()->where('field_name', 'sku')->first();
                $sku = $skuData ? 
                    "<small class='text-muted'>SKU: {$skuData->field_value}</small><br>" : '';
            } catch (\Exception $e) {
                // Ignore if contentData relationship doesn't work
            }

            // Get stock information
            $stockInfo = '';
            try {
                $quantityData = $product->contentData()->where('field_name', 'quantity')->first();
                if ($quantityData) {
                    $quantity = (int)$quantityData->field_value;
                    if ($quantity > 0) {
                        $stockInfo = "<small class='text-success'><i class='fas fa-check'></i> In Stock ({$quantity})</small>";
                    } else {
                        $stockInfo = "<small class='text-danger'><i class='fas fa-times'></i> Out of Stock</small>";
                    }
                } else {
                    $stockInfo = "<small class='text-info'><i class='fas fa-infinity'></i> Available</small>";
                }
            } catch (\Exception $e) {
                $stockInfo = "<small class='text-muted'>Stock info unavailable</small>";
            }

            $createdDate = $product->created_at ? 
                $product->created_at->format('M j, Y') : 
                'Unknown date';

            $cards .= "
            <div class='col-md-6 col-lg-4 mb-3'>
                <div class='card h-100 product-card'>
                    <div class='card-body'>
                        <div class='d-flex justify-content-between align-items-start mb-2'>
                            <h6 class='card-title mb-0'>{$product->title}</h6>
                            {$statusBadge}
                        </div>
                        <div class='mb-2'>
                            <span class='h6'>{$formattedPrice}</span>{$specialPrice}
                        </div>
                        <p class='card-text small'>{$excerpt}</p>
                    </div>
                    <div class='card-footer bg-transparent'>
                        {$sku}
                        {$stockInfo}<br>
                        <small class='text-muted'><i class='fas fa-calendar'></i> {$createdDate}</small>
                    </div>
                </div>
            </div>";
        }
        
        $cards .= "</div>";

        return $header . $cards;
    }
}
