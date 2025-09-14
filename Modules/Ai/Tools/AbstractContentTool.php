<?php

declare(strict_types=1);

namespace Modules\Ai\Tools;

use Modules\Content\Models\Content;
use Modules\CustomFields\Models\CustomField;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

abstract class AbstractContentTool extends BaseTool
{
    protected string $contentType = 'content';
    protected array $requiredPermissions = ['view content'];

    protected function getBaseProperties(): array
    {
        return [
            new ToolProperty(
                name: 'search_term',
                type: PropertyType::STRING,
                description: 'Search term to find in title, content, or description.',
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
                name: 'category_id',
                type: PropertyType::INTEGER,
                description: 'Filter by category ID.',
                required: false,
            ),
            new ToolProperty(
                name: 'custom_fields',
                type: PropertyType::STRING,
                description: 'Filter by custom fields in format "field_name:value,field_name2:value2".',
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

    protected function buildContentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = Content::query()
            ->where('is_deleted', 0)
            ->where('content_type', $this->contentType);

        return $query;
    }

    protected function applyFilters(\Illuminate\Database\Eloquent\Builder $query, array $params): \Illuminate\Database\Eloquent\Builder
    {
        // Extract parameters
        $search_term = $params['search_term'] ?? '';
        $is_active = $params['is_active'] ?? 'all';
        $parent_id = $params['parent_id'] ?? null;
        $category_id = $params['category_id'] ?? null;
        $custom_fields = $params['custom_fields'] ?? '';
        $sort_by = $params['sort_by'] ?? 'position';

        // Apply content type filter (already applied in buildContentQuery)
        
        // Filter by active status
        if ($is_active !== 'all') {
            $query->where('is_active', (int)$is_active);
        }

        // Filter by parent ID
        if ($parent_id !== null) {
            $query->where('parent', $parent_id);
        }

        // Filter by category
        if ($category_id) {
            $query->whereHas('categories', function ($q) use ($category_id) {
                $q->where('categories.id', $category_id);
            });
        }

        // Search using keyword filter trait
        if (!empty($search_term)) {
            $query->filter(['keyword' => $search_term]);
        }

        // Filter by custom fields
        if (!empty($custom_fields)) {
            $customFieldsArray = $this->parseCustomFields($custom_fields);
            if (!empty($customFieldsArray)) {
                $query->filter(['customFields' => $customFieldsArray]);
            }
        }

        // Sort results
        $validSortFields = ['title', 'created_at', 'updated_at', 'position'];
        if (in_array($sort_by, $validSortFields)) {
            $query->orderBy($sort_by, $sort_by === 'created_at' ? 'desc' : 'asc');
        } else {
            $query->orderBy('position', 'asc');
        }

        return $query;
    }

    protected function parseCustomFields(string $customFields): array
    {
        $result = [];
        $pairs = explode(',', $customFields);
        
        foreach ($pairs as $pair) {
            $pair = trim($pair);
            if (strpos($pair, ':') !== false) {
                [$field, $value] = explode(':', $pair, 2);
                $result[trim($field)] = trim($value);
            }
        }
        
        return $result;
    }

    protected function formatContentAsHtml($content, string $contentType, array $params, int $limit): string
    {
        $totalFound = $content->count();
        
        $searchInfo = !empty($params['search_term']) ? "Search: \"{$params['search_term']}\" " : '';
        $statusInfo = isset($params['is_active']) && $params['is_active'] !== 'all' ? 
            "Status: " . ($params['is_active'] ? 'Published' : 'Unpublished') . " " : '';
        
        $header = "
        <div class='content-list-header mb-3'>
            <h4><i class='fas fa-file-alt text-primary me-2'></i>" . ucfirst($contentType) . " List</h4>
            <p class='mb-2'>
                {$searchInfo}{$statusInfo}
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

            $categories = $item->categories->pluck('title')->implode(', ');
            $categoryInfo = $categories ? 
                "<small class='text-muted'>Categories: {$categories}</small>" : 
                '<small class="text-muted">No categories</small>';

            // Show custom fields if any
            $customFieldsInfo = $this->formatCustomFields($item);

            $tableData[] = [
                'id' => "<strong>#{$item->id}</strong>",
                'title' => "<strong>{$title}</strong><br>{$url}",
                'type' => $typeBadge,
                'status' => $statusBadge,
                'excerpt' => $excerpt,
                'categories' => $categoryInfo,
                'custom_fields' => $customFieldsInfo,
                'created' => $createdAt,
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
                'categories' => 'Categories',
                'custom_fields' => 'Custom Fields',
                'created' => 'Created',
            ],
            '',
            'content-list-results'
        );

        return $header . $table;
    }

    protected function formatCustomFields($item): string
    {
        $customFields = [];
        
        // Get custom fields for this content
        $fields = CustomField::where('rel_id', $item->id)
            ->where('rel_type', get_class($item))
            ->get();
            
        foreach ($fields as $field) {
            $value = is_array($field->value) ? implode(', ', $field->value) : $field->value;
            if ($value) {
                $customFields[] = "<small><strong>{$field->name}:</strong> {$value}</small>";
            }
        }
        
        return $customFields ? implode('<br>', $customFields) : '<small class="text-muted">No custom fields</small>';
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

    protected function getContentById(int $id): ?Content
    {
        return Content::where('id', $id)
            ->where('is_deleted', 0)
            ->where('content_type', $this->contentType)
            ->first();
    }

    protected function updateContent(Content $content, array $data): bool
    {
        try {
            // Update basic fields
            $fillableFields = ['title', 'content_body', 'description', 'url', 'is_active'];
            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $content->$field = $data[$field];
                }
            }

            // Handle custom fields
            if (isset($data['custom_fields'])) {
                $customFieldsData = $this->parseCustomFields($data['custom_fields']);
                foreach ($customFieldsData as $fieldName => $fieldValue) {
                    $content->setCustomField([
                        'name' => $fieldName,
                        'name_key' => $fieldName,
                        'value' => [$fieldValue]
                    ]);
                }
            }

            return $content->save();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function createContent(array $data): ?Content
    {
        try {
            $content = new Content();
            $content->content_type = $this->contentType;
            
            // Set basic fields
            $fillableFields = ['title', 'content_body', 'description', 'url', 'is_active', 'parent'];
            foreach ($fillableFields as $field) {
                if (isset($data[$field])) {
                    $content->$field = $data[$field];
                }
            }

            if ($content->save()) {
                // Handle custom fields after saving
                if (isset($data['custom_fields'])) {
                    $customFieldsData = $this->parseCustomFields($data['custom_fields']);
                    foreach ($customFieldsData as $fieldName => $fieldValue) {
                        $content->setCustomField([
                            'name' => $fieldName,
                            'name_key' => $fieldName,
                            'value' => [$fieldValue]
                        ]);
                    }
                    $content->save();
                }
                
                return $content;
            }
            
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }
}
