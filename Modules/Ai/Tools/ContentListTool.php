<?php

declare(strict_types=1);

namespace Modules\Ai\Tools;

use Modules\Content\Models\Content;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class ContentListTool extends BaseTool
{
    protected string $domain = 'content';
    protected array $requiredPermissions = ['view content'];

    public function __construct(protected array $dependencies = [])
    {
        parent::__construct(
            'content_list',
            'List and filter content items including pages, posts, products and other content types in Microweber CMS.'
        );
    }

    protected function properties(): array
    {
        return [
            new ToolProperty(
                name: 'content_type',
                type: PropertyType::STRING,
                description: 'Type of content to list. Options: "page", "post", "product", or "all" for all types.',
                required: false,
            ),
            new ToolProperty(
                name: 'is_active',
                type: PropertyType::STRING,
                description: 'Filter by publication status. Options: "1" for published, "0" for unpublished, or "all" for both.',
                required: false,
            ),
            new ToolProperty(
                name: 'parent_id',
                type: PropertyType::INTEGER,
                description: 'Filter by parent page ID. Use 0 for top-level content.',
                required: false,
            ),
            new ToolProperty(
                name: 'search_term',
                type: PropertyType::STRING,
                description: 'Search term to find in title, content, or description.',
                required: false,
            ),
            new ToolProperty(
                name: 'limit',
                type: PropertyType::INTEGER,
                description: 'Maximum number of results to return (1-100). Default is 20.',
                required: false,
            ),
            new ToolProperty(
                name: 'sort_by',
                type: PropertyType::STRING,
                description: 'Sort results by field. Options: "title", "created_at", "updated_at", "position". Default is "position".',
                required: false,
            ),
        ];
    }

    public function __invoke(...$args): string
    {
        // Extract parameters from variadic args
        $content_type = $args[0] ?? 'all';
        $is_active = $args[1] ?? 'all';
        $parent_id = $args[2] ?? null;
        $search_term = $args[3] ?? '';
        $limit = $args[4] ?? 20;
        $sort_by = $args[5] ?? 'position';

        if (!$this->authorize()) {
            return $this->handleError('You do not have permission to list content.');
        }

        // Validate limit
        $limit = max(1, min(100, $limit));

        try {
            $query = Content::query()
                ->where('is_deleted', 0); // Exclude deleted content

            // Filter by content type
            if ($content_type !== 'all') {
                $query->where('content_type', $content_type);
            }

            // Filter by active status
            if ($is_active !== 'all') {
                $query->where('is_active', (int)$is_active);
            }

            // Filter by parent ID
            if ($parent_id !== null) {
                $query->where('parent', $parent_id);
            }

            // Search in content
            if (!empty($search_term)) {
                $query->where(function ($q) use ($search_term) {
                    $q->where('title', 'LIKE', '%' . $search_term . '%')
                      ->orWhere('content_body', 'LIKE', '%' . $search_term . '%')
                      ->orWhere('description', 'LIKE', '%' . $search_term . '%')
                      ->orWhere('url', 'LIKE', '%' . $search_term . '%');
                });
            }

            // Sort results
            $validSortFields = ['title', 'created_at', 'updated_at', 'position'];
            if (in_array($sort_by, $validSortFields)) {
                $query->orderBy($sort_by, $sort_by === 'created_at' ? 'desc' : 'asc');
            } else {
                $query->orderBy('position', 'asc');
            }

            $content = $query->limit($limit)->get();

            if ($content->isEmpty()) {
                $typeInfo = $content_type !== 'all' ? " of type '{$content_type}'" : '';
                $statusInfo = $is_active !== 'all' ? " with status '" . ($is_active ? 'published' : 'unpublished') . "'" : '';
                $searchInfo = !empty($search_term) ? " matching '{$search_term}'" : '';
                
                return $this->formatAsHtmlTable(
                    [],
                    ['title' => 'Title', 'type' => 'Type', 'status' => 'Status'],
                    "No content found{$typeInfo}{$statusInfo}{$searchInfo}.",
                    'content-list-empty'
                );
            }

            return $this->formatContentListAsHtml($content, $content_type, $is_active, $limit);

        } catch (\Exception $e) {
            return $this->handleError('Error listing content: ' . $e->getMessage());
        }
    }

    protected function formatContentListAsHtml($content, string $content_type, string $is_active, int $limit): string
    {
        $totalFound = $content->count();
        
        $typeInfo = $content_type !== 'all' ? "Type: {$content_type} " : '';
        $statusInfo = $is_active !== 'all' ? "Status: " . ($is_active ? 'Published' : 'Unpublished') . " " : '';
        
        $header = "
        <div class='content-list-header mb-3'>
            <h4><i class='fas fa-file-alt text-primary me-2'></i>Content List</h4>
            <p class='mb-2'>
                {$typeInfo}{$statusInfo}
                <strong>Found:</strong> {$totalFound} item(s)" . 
                ($totalFound >= $limit ? " (showing first {$limit})" : '') . "
            </p>
        </div>";

        $tableData = [];
        foreach ($content as $item) {
            $statusBadge = $this->getContentStatusBadge($item->is_active ?? 0);
            $typeBadge = $this->getContentTypeBadge($item->content_type ?? 'content');
            
            $title = $item->title ?: 'Untitled';
            $excerpt = $item->description ?: 
                       ($item->content_body ? \Str::limit(strip_tags($item->content_body), 100) : 'No description');
            
            $createdAt = $item->created_at ? 
                $item->created_at->format('M j, Y H:i') : 
                'Unknown';

            $url = $item->url ? 
                "<small class='text-muted'>{$item->url}</small>" : 
                '<small class="text-muted">No URL</small>';

            $parent = $item->parent ? 
                "<small class='text-muted'>Parent: {$item->parent}</small>" : 
                '<small class="text-muted">Top level</small>';

            $tableData[] = [
                'id' => "<strong>#{$item->id}</strong>",
                'title' => "<strong>{$title}</strong><br>{$url}",
                'type' => $typeBadge,
                'status' => $statusBadge,
                'excerpt' => $excerpt,
                'created' => $createdAt,
                'hierarchy' => $parent,
            ];
        }

        $table = $this->formatAsHtmlTable(
            $tableData,
            [
                'id' => 'ID',
                'title' => 'Title & URL',
                'type' => 'Type',
                'status' => 'Status',
                'excerpt' => 'Description',
                'created' => 'Created',
                'hierarchy' => 'Hierarchy',
            ],
            '',
            'content-list-results'
        );

        return $header . $table;
    }

    protected function getContentStatusBadge($isActive): string
    {
        return $isActive ? 
            "<span class='badge bg-success'>Published</span>" : 
            "<span class='badge bg-warning'>Unpublished</span>";
    }

    protected function getContentTypeBadge(string $contentType): string
    {
        $badgeClass = match($contentType) {
            'page' => 'bg-primary',
            'post' => 'bg-info',
            'product' => 'bg-success',
            default => 'bg-secondary'
        };

        return "<span class='badge {$badgeClass}'>" . ucfirst($contentType) . "</span>";
    }
}
