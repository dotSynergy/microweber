# AI Module Architecture Plan

## 1. Current State Analysis

### Existing AI Module Structure
- **Agents**: ContentAgent, ShopAgent, BaseAgent
- **Services**: AiService, AiServiceImages, AgentFactory
- **Drivers**: OpenAI, Gemini, Ollama, OpenRouter, Replicate
- **Tools**: Web routes, API endpoints, Filament admin interface

## 2. Module Analysis & Agent Opportunities

### All Available Modules (78 modules analyzed)
**Content & Media**: Accordion, Audio, Background, BeforeAfter, Breadcrumb, Btn, Content, ContentData, ContentDataVariant, ContentField, Elements, Embed, FacebookLike, FacebookPage, HighlightCode, ImageRollover, Logo, Marquee, Media, MediaLibrary, Menu, Page, Pictures, Post, Slider, SocialLinks, Spacer, Tabs, TextType, TweetEmbed, Video

**E-commerce**: Billing, Cart, Checkout, Company, Coupons, Currency, Invoice, Offer, Order, Payment, Product, ProductScraper, Shipping, Shop, Tax

**Customer & Communication**: Address, Captcha, Comments, ContactForm, Country, Customer, Form, Newsletter, Profile

**Analytics & SEO**: GoogleAnalytics, GoogleMaps, Posthog, RssFeed, Search, Sitemap, SiteStats

**System & Admin**: Ai, AiWizard, Attributes, Backup, Cloudflare, CookieNotice, CustomFields, Export, FileManager, LayoutContent, Layouts, Log, LoginWithToken, MailTemplate, Marketplace, Multilanguage, OpenApi, Pdf, Restore, Settings, Updater, WhiteLabel

**UI & Presentation**: Faq, Pagination, Rating, Skills, Teamcard, Testimonials

## 3. Proposed General Agent Architecture

### Base Agent Pattern (Following OrderAgent Example)
```php
declare(strict_types=1);

namespace Microweber\Modules\Ai\Agents;

use NeuronAI\SystemPrompt;
use NeuronAI\Workflow\WorkflowState;

abstract class BaseAgent
{
    protected array $tools = [];
    protected string $domain;
    protected int $maxTries = 5;
    
    public function __construct(protected array $dependencies = [])
    {
        $this->setupTools();
    }
    
    abstract public function instructions(): string;
    abstract protected function setupTools(): void;
    
    public function setState(WorkflowState $state): void
    {
        foreach ($this->tools as $tool) {
            if (method_exists($tool, 'setState')) {
                $tool->setState($state);
            }
        }
    }
    
    protected function tools(): array
    {
        return $this->tools;
    }
}
```

### Specialized Agents

#### 1. ContentAgent (Enhanced)
**Domain**: Content Management & Publishing
**Modules**: Content, Post, Page, Blog, Menu, Sitemap, Breadcrumb
**Tools**:
- `CreateContentTool` - Create pages, posts, blog entries
- `UpdateContentTool` - Edit existing content
- `SearchContentTool` - Find content by criteria
- `PublishContentTool` - Manage publication status
- `MenuManagementTool` - Create/update navigation menus
- `SitemapGenerationTool` - Generate XML sitemaps
- `SEOOptimizationTool` - Optimize content for search engines

#### 2. ShopAgent (Enhanced)
**Domain**: E-commerce Operations
**Modules**: Product, Shop, Cart, Checkout, Order, Invoice, Payment, Shipping, Tax, Coupons
**Tools**:
- `ProductSearchTool` - Find products by criteria
- `OrderLookupTool` - Retrieve order information
- `InventoryCheckTool` - Check product availability
- `PriceCalculationTool` - Calculate prices with taxes/discounts
- `ShippingEstimateTool` - Calculate shipping costs
- `CouponManagementTool` - Create/validate coupons
- `InvoiceGenerationTool` - Generate invoices
- `PaymentProcessingTool` - Handle payment operations

#### 3. CustomerAgent (New)
**Domain**: Customer Service & Management
**Modules**: Customer, Profile, Address, Country, Comments, Rating
**Tools**:
- `CustomerLookupTool` - Find customer information
- `AddressValidationTool` - Validate and format addresses
- `OrderHistoryTool` - Retrieve customer order history
- `ProfileUpdateTool` - Update customer profiles
- `CommentModerationTool` - Manage customer comments
- `RatingAnalysisTool` - Analyze customer ratings

#### 4. MediaAgent (New)
**Domain**: Media & File Management
**Modules**: Media, MediaLibrary, Pictures, Video, Audio, FileManager
**Tools**:
- `MediaUploadTool` - Upload and organize media files
- `ImageOptimizationTool` - Optimize images for web
- `VideoProcessingTool` - Process video files
- `AudioProcessingTool` - Handle audio files
- `FileOrganizationTool` - Organize file structure
- `MediaSearchTool` - Search media library

#### 5. MarketingAgent (New)
**Domain**: Marketing & Analytics
**Modules**: Newsletter, SocialLinks, GoogleAnalytics, Posthog, FacebookLike, FacebookPage
**Tools**:
- `NewsletterTool` - Manage email campaigns
- `AnalyticsReportTool` - Generate analytics reports
- `SocialMediaTool` - Manage social media integration
- `CampaignTrackingTool` - Track marketing campaigns
- `FacebookIntegrationTool` - Manage Facebook features

#### 6. SystemAgent (New)
**Domain**: System Administration
**Modules**: Settings, Backup, Restore, Updater, Log, Cloudflare, OpenApi
**Tools**:
- `BackupTool` - Create system backups
- `RestoreTool` - Restore from backups
- `UpdateTool` - Manage system updates
- `SettingsManagementTool` - Configure system settings
- `LogAnalysisTool` - Analyze system logs
- `CloudflareManagementTool` - Manage Cloudflare settings

#### 7. ComponentAgent (New)
**Domain**: UI Components & Layout
**Modules**: Slider, Tabs, Accordion, Elements, Components, LayoutContent, Layouts
**Tools**:
- `ComponentBuilderTool` - Create UI components
- `LayoutManagementTool` - Manage page layouts
- `SliderConfigurationTool` - Configure image sliders
- `TabsManagementTool` - Create tabbed content
- `AccordionBuilderTool` - Build accordion components

#### 8. FormAgent (New)
**Domain**: Forms & Data Collection
**Modules**: Form, ContactForm, Captcha, CustomFields, Survey
**Tools**:
- `FormBuilderTool` - Create custom forms
- `ContactFormTool` - Manage contact forms
- `CaptchaValidationTool` - Implement captcha validation
- `CustomFieldsTool` - Manage custom field definitions
- `FormAnalyticsTool` - Analyze form submissions

## 4. Tool Architecture

### Base Tool Pattern (Following GetOrderTool Example)
```php
declare(strict_types=1);

namespace Microweber\Modules\Ai\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Workflow\WorkflowState;

abstract class BaseTool extends Tool
{
    protected string $domain;
    protected array $requiredPermissions = [];
    protected WorkflowState $state;
    
    public function __construct(
        string $name,
        string $description,
        protected array $dependencies = []
    ) {
        parent::__construct($name, $description);
    }
    
    abstract protected function properties(): array;
    abstract public function __invoke(...$args): string;
    
    public function setState(WorkflowState $state): void
    {
        $this->state = $state;
    }
    
    protected function authorize(): bool
    {
        foreach ($this->requiredPermissions as $permission) {
            if (!auth()->user()?->can($permission)) {
                return false;
            }
        }
        return true;
    }
    
    protected function validateInput(array $input): bool
    {
        // Common validation logic
        return true;
    }
    
    protected function formatAsHtmlTable(array $data, array $headers = []): string
    {
        if (empty($data)) {
            return '<p class="text-muted">No results found.</p>';
        }
        
        $html = '<div class="table-responsive">';
        $html .= '<table class="table table-striped table-bordered table-sm">';
        
        if (!empty($headers)) {
            $html .= '<thead class="table-light"><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        
        $html .= '<tbody>';
        foreach ($data as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars((string)$cell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody></table></div>';
        
        return $html;
    }
    
    protected function formatAsCard(array $data): string
    {
        $html = '<div class="card mb-3">';
        $html .= '<div class="card-body">';
        
        foreach ($data as $key => $value) {
            $html .= '<p><strong>' . htmlspecialchars(ucfirst($key)) . ':</strong> ';
            $html .= htmlspecialchars((string)$value) . '</p>';
        }
        
        $html .= '</div></div>';
        return $html;
    }
}
```

## 5. Example Tool Implementations

### CustomerLookupTool
```php
class CustomerLookupTool extends BaseTool
{
    protected string $domain = 'customer';
    protected array $requiredPermissions = ['view_customers'];
    
    protected function properties(): array
    {
        return [
            new ToolProperty('email', PropertyType::STRING, 'Customer email address', required: false),
            new ToolProperty('customer_id', PropertyType::INTEGER, 'Customer ID number', required: false),
            new ToolProperty('name', PropertyType::STRING, 'Customer name or partial name', required: false),
            new ToolProperty('limit', PropertyType::INTEGER, 'Maximum number of results', required: false, default: 10)
        ];
    }
    
    public function __invoke(?string $email = null, ?int $customer_id = null, ?string $name = null, int $limit = 10): string
    {
        if (!$this->authorize()) {
            return '<div class="alert alert-danger">Insufficient permissions to view customer data.</div>';
        }
        
        if (!$email && !$customer_id && !$name) {
            return '<div class="alert alert-warning">Please provide at least one search criteria (email, ID, or name).</div>';
        }
        
        $customerModel = app('\\Microweber\\Modules\\Customer\\Models\\Customer');
        $query = $customerModel::query();
        
        if ($email) $query->where('email', 'like', "%{$email}%");
        if ($customer_id) $query->where('id', $customer_id);
        if ($name) $query->where('name', 'like', "%{$name}%");
        
        $customers = $query->with(['orders', 'addresses'])->limit($limit)->get();
        
        if ($customers->isEmpty()) {
            return '<div class="alert alert-info">No customers found matching your search criteria.</div>';
        }
        
        return $this->formatCustomersAsHtml($customers);
    }
    
    protected function formatCustomersAsHtml($customers): string
    {
        $html = '<div class="customers-list">';
        
        foreach ($customers as $customer) {
            $html .= '<div class="card mb-3">';
            $html .= '<div class="card-header"><h5>Customer: ' . htmlspecialchars($customer->name) . '</h5></div>';
            $html .= '<div class="card-body">';
            
            // Customer details table
            $html .= '<table class="table table-sm mb-3">';
            $html .= '<tr><th>ID</th><td>' . $customer->id . '</td></tr>';
            $html .= '<tr><th>Email</th><td>' . htmlspecialchars($customer->email) . '</td></tr>';
            $html .= '<tr><th>Phone</th><td>' . htmlspecialchars($customer->phone ?? 'N/A') . '</td></tr>';
            $html .= '<tr><th>Registered</th><td>' . $customer->created_at->format('Y-m-d H:i') . '</td></tr>';
            $html .= '<tr><th>Orders</th><td>' . $customer->orders->count() . '</td></tr>';
            $html .= '</table>';
            
            // Recent orders
            if ($customer->orders->isNotEmpty()) {
                $html .= '<h6>Recent Orders</h6>';
                $html .= '<table class="table table-striped table-sm">';
                $html .= '<thead><tr><th>Order #</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>';
                $html .= '<tbody>';
                
                foreach ($customer->orders->take(5) as $order) {
                    $html .= '<tr>';
                    $html .= '<td>#' . $order->id . '</td>';
                    $html .= '<td>' . $order->created_at->format('Y-m-d') . '</td>';
                    $html .= '<td>$' . number_format($order->total, 2) . '</td>';
                    $html .= '<td><span class="badge bg-' . $this->getStatusColor($order->status) . '">' . $order->status . '</span></td>';
                    $html .= '</tr>';
                }
                
                $html .= '</tbody></table>';
            }
            
            $html .= '</div></div>';
        }
        
        $html .= '</div>';
        return $html;
    }
    
    protected function getStatusColor(string $status): string
    {
        return match(strtolower($status)) {
            'completed' => 'success',
            'pending' => 'warning',
            'cancelled' => 'danger',
            'processing' => 'info',
            default => 'secondary'
        };
    }
}
```

### ProductSearchTool
```php
class ProductSearchTool extends BaseTool
{
    protected string $domain = 'shop';
    protected array $requiredPermissions = ['view_products'];
    
    protected function properties(): array
    {
        return [
            new ToolProperty('search_term', PropertyType::STRING, 'Product name or description search', required: false),
            new ToolProperty('category_id', PropertyType::INTEGER, 'Filter by category ID', required: false),
            new ToolProperty('min_price', PropertyType::NUMBER, 'Minimum price filter', required: false),
            new ToolProperty('max_price', PropertyType::NUMBER, 'Maximum price filter', required: false),
            new ToolProperty('in_stock', PropertyType::BOOLEAN, 'Only show products in stock', required: false),
            new ToolProperty('limit', PropertyType::INTEGER, 'Maximum number of results', required: false, default: 20)
        ];
    }
    
    public function __invoke(
        ?string $search_term = null,
        ?int $category_id = null,
        ?float $min_price = null,
        ?float $max_price = null,
        ?bool $in_stock = null,
        int $limit = 20
    ): string {
        if (!$this->authorize()) {
            return '<div class="alert alert-danger">Insufficient permissions to view products.</div>';
        }
        
        $productModel = app('\\Microweber\\Modules\\Product\\Models\\Product');
        $query = $productModel::query()->with(['category', 'media']);
        
        if ($search_term) {
            $query->where(function($q) use ($search_term) {
                $q->where('title', 'like', "%{$search_term}%")
                  ->orWhere('description', 'like', "%{$search_term}%");
            });
        }
        
        if ($category_id) $query->where('category_id', $category_id);
        if ($min_price) $query->where('price', '>=', $min_price);
        if ($max_price) $query->where('price', '<=', $max_price);
        if ($in_stock) $query->where('quantity', '>', 0);
        
        $products = $query->limit($limit)->get();
        
        if ($products->isEmpty()) {
            return '<div class="alert alert-info">No products found matching your criteria.</div>';
        }
        
        return $this->formatProductsAsHtml($products);
    }
    
    protected function formatProductsAsHtml($products): string
    {
        $html = '<div class="row">';
        
        foreach ($products as $product) {
            $html .= '<div class="col-md-6 col-lg-4 mb-4">';
            $html .= '<div class="card h-100">';
            
            // Product image
            if ($product->media->isNotEmpty()) {
                $html .= '<img src="' . $product->media->first()->url . '" class="card-img-top" style="height: 200px; object-fit: cover;">';
            }
            
            $html .= '<div class="card-body d-flex flex-column">';
            $html .= '<h6 class="card-title">' . htmlspecialchars($product->title) . '</h6>';
            $html .= '<p class="card-text flex-grow-1">' . htmlspecialchars(Str::limit($product->description, 100)) . '</p>';
            $html .= '<div class="mt-auto">';
            $html .= '<p class="h5 text-primary">$' . number_format($product->price, 2) . '</p>';
            $html .= '<small class="text-muted">Stock: ' . $product->quantity . '</small>';
            $html .= '</div></div></div></div>';
        }
        
        $html .= '</div>';
        return $html;
    }
}
```

## 6. Agent Factory Enhancement

```php
class AgentFactory
{
    protected array $agentRegistry = [
        'content' => ContentAgent::class,
        'shop' => ShopAgent::class,
        'customer' => CustomerAgent::class,
        'media' => MediaAgent::class,
        'marketing' => MarketingAgent::class,
        'system' => SystemAgent::class,
        'component' => ComponentAgent::class,
        'form' => FormAgent::class,
    ];
    
    public function createAgent(string $domain, array $dependencies = []): BaseAgent
    {
        if (!isset($this->agentRegistry[$domain])) {
            throw new InvalidArgumentException("Unknown agent domain: $domain");
        }
        
        $agentClass = $this->agentRegistry[$domain];
        $tools = $this->getToolsForDomain($domain);
        
        return new $agentClass([...$dependencies, 'tools' => $tools]);
    }
    
    protected function getToolsForDomain(string $domain): array
    {
        $toolsPath = __DIR__ . "/Tools/{$domain}";
        $tools = [];
        
        if (is_dir($toolsPath)) {
            foreach (glob("{$toolsPath}/*Tool.php") as $file) {
                $className = basename($file, '.php');
                $fullClassName = "\\Microweber\\Modules\\Ai\\Tools\\{$domain}\\{$className}";
                
                if (class_exists($fullClassName)) {
                    $tools[] = app($fullClassName);
                }
            }
        }
        
        return $tools;
    }
    
    public function getAvailableDomains(): array
    {
        return array_keys($this->agentRegistry);
    }
}
```

## 7. Agent Instructions Templates

### General Instruction Pattern
```php
public function instructions(): string
{
    return (string)new SystemPrompt(
        background: [
            'You are a specialized AI agent for [DOMAIN] management in Microweber CMS.',
            'You have access to tools for [SPECIFIC_CAPABILITIES].',
            'You work with the following modules: [MODULE_LIST].'
        ],
        steps: [
            'Understand the user\'s request and identify the appropriate tool(s) to use',
            'Validate input parameters and check permissions',
            'Execute the requested operation using available tools',
            'Format results in user-friendly HTML format',
            'Provide helpful suggestions for follow-up actions'
        ],
        output: [
            'Always respond with properly formatted HTML',
            'Use Bootstrap classes for styling',
            'Include relevant action buttons when appropriate',
            'Provide clear error messages when operations fail',
            'Suggest alternative actions when no results are found'
        ]
    );
}
```

## 8. Security & Permissions Framework

### Permission Mapping by Domain
```php
protected array $domainPermissions = [
    'content' => ['view_content', 'create_content', 'edit_content', 'delete_content'],
    'shop' => ['view_products', 'manage_orders', 'view_customers', 'manage_inventory'],
    'customer' => ['view_customers', 'edit_customers', 'view_orders'],
    'media' => ['view_media', 'upload_media', 'edit_media', 'delete_media'],
    'marketing' => ['view_analytics', 'manage_campaigns', 'send_newsletters'],
    'system' => ['manage_settings', 'view_logs', 'manage_backups', 'system_admin'],
    'component' => ['edit_layouts', 'manage_components'],
    'form' => ['view_forms', 'create_forms', 'view_submissions']
];
```

## 9. Development Roadmap

### Phase 1: Foundation (Sprint 1-2)
1. Implement BaseTool and BaseAgent abstractions
2. Create CustomerAgent with CustomerLookupTool
3. Enhance existing ShopAgent with ProductSearchTool
4. Set up AgentFactory with domain registration

### Phase 2: Core Agents (Sprint 3-4)
1. MediaAgent with file management tools
2. SystemAgent with backup/settings tools
3. ComponentAgent for UI management
4. FormAgent for form building

### Phase 3: Advanced Features (Sprint 5-6)
1. Multi-agent workflows
2. Tool result caching
3. Advanced formatting options
4. Real-time collaboration

### Phase 4: Optimization (Sprint 7+)
1. Performance monitoring
2. Auto-documentation generation
3. Machine learning recommendations
4. Advanced security features

This comprehensive architecture provides a scalable foundation for creating specialized AI agents that can work with all 78 Microweber modules while maintaining consistency, security, and reusability.