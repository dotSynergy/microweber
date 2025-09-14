<?php

declare(strict_types=1);

namespace Modules\Ai\Tools;

use Modules\Post\Models\Post;
use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\ToolProperty;

class PostListTool extends BaseTool
{
    protected string $domain = 'content';
    protected array $requiredPermissions = ['view content'];

    public function __construct(protected array $dependencies = [])
    {
        parent::__construct(
            'post_list',
            'List blog posts with filtering options. Specialized tool for post content type.'
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
                name: 'blog_id',
                type: PropertyType::INTEGER,
                description: 'Filter posts by parent blog page ID.',
                required: false,
            ),
            new ToolProperty(
                name: 'search_term',
                type: PropertyType::STRING,
                description: 'Search term to find in post title, content, or description.',
                required: false,
            ),
            new ToolProperty(
                name: 'date_from',
                type: PropertyType::STRING,
                description: 'Start date for post search (YYYY-MM-DD format).',
                required: false,
            ),
            new ToolProperty(
                name: 'date_to',
                type: PropertyType::STRING,
                description: 'End date for post search (YYYY-MM-DD format).',
                required: false,
            ),
            new ToolProperty(
                name: 'limit',
                type: PropertyType::INTEGER,
                description: 'Maximum number of results to return (1-50). Default is 15.',
                required: false,
            ),
        ];
    }

    public function __invoke(...$args): string
    {
        // Extract parameters from variadic args
        $is_active = $args[0] ?? 'all';
        $blog_id = $args[1] ?? null;
        $search_term = $args[2] ?? '';
        $date_from = $args[3] ?? '';
        $date_to = $args[4] ?? '';
        $limit = $args[5] ?? 15;

        if (!$this->authorize()) {
            return $this->handleError('You do not have permission to list posts.');
        }

        // Validate limit
        $limit = max(1, min(50, $limit));

        try {
            $query = Post::query()
                ->where('is_deleted', 0);

            // Filter by active status
            if ($is_active !== 'all') {
                $query->where('is_active', (int)$is_active);
            }

            // Filter by blog page
            if ($blog_id !== null) {
                $query->where('parent', $blog_id);
            }

            // Search in content
            if (!empty($search_term)) {
                $query->where(function ($q) use ($search_term) {
                    $q->where('title', 'LIKE', '%' . $search_term . '%')
                      ->orWhere('content_body', 'LIKE', '%' . $search_term . '%')
                      ->orWhere('description', 'LIKE', '%' . $search_term . '%');
                });
            }

            // Filter by date range
            if (!empty($date_from)) {
                $query->whereDate('created_at', '>=', $date_from);
            }
            if (!empty($date_to)) {
                $query->whereDate('created_at', '<=', $date_to);
            }

            // Order by creation date (newest first)
            $query->orderBy('created_at', 'desc');

            $posts = $query->limit($limit)->get();

            if ($posts->isEmpty()) {
                $statusInfo = $is_active !== 'all' ? " with status '" . ($is_active ? 'published' : 'unpublished') . "'" : '';
                $searchInfo = !empty($search_term) ? " matching '{$search_term}'" : '';
                $dateInfo = '';
                if (!empty($date_from) || !empty($date_to)) {
                    $dateInfo = " in date range" . 
                        (!empty($date_from) ? " from {$date_from}" : '') .
                        (!empty($date_to) ? " to {$date_to}" : '');
                }
                
                return $this->formatAsHtmlTable(
                    [],
                    ['title' => 'Title', 'status' => 'Status', 'date' => 'Date'],
                    "No posts found{$statusInfo}{$searchInfo}{$dateInfo}.",
                    'post-list-empty'
                );
            }

            return $this->formatPostsAsHtml($posts, $is_active, $limit);

        } catch (\Exception $e) {
            return $this->handleError('Error listing posts: ' . $e->getMessage());
        }
    }

    protected function formatPostsAsHtml($posts, string $is_active, int $limit): string
    {
        $totalFound = $posts->count();
        
        $statusInfo = $is_active !== 'all' ? "Status: " . ($is_active ? 'Published' : 'Unpublished') . " " : '';
        
        $header = "
        <div class='post-list-header mb-3'>
            <h4><i class='fas fa-blog text-primary me-2'></i>Blog Posts</h4>
            <p class='mb-2'>
                {$statusInfo}
                <strong>Found:</strong> {$totalFound} post(s)" . 
                ($totalFound >= $limit ? " (showing first {$limit})" : '') . "
            </p>
        </div>";

        $cards = "<div class='row'>";
        
        foreach ($posts as $post) {
            $statusBadge = $post->is_active ? 
                "<span class='badge bg-success'>Published</span>" : 
                "<span class='badge bg-warning'>Unpublished</span>";

            $excerpt = $post->description ?: 
                       ($post->content_body ? \Str::limit(strip_tags($post->content_body), 120) : 'No description available');

            $postDate = $post->created_at ? 
                $post->created_at->format('M j, Y') : 
                'Unknown date';

            $author = '';
            if ($post->created_by) {
                try {
                    $user = \MicroweberPackages\User\Models\User::find($post->created_by);
                    $author = $user ? $user->email : "User #{$post->created_by}";
                } catch (\Exception $e) {
                    $author = "User #{$post->created_by}";
                }
            }

            $authorInfo = $author ? 
                "<small class='text-muted'><i class='fas fa-user'></i> {$author}</small><br>" : '';

            $parentBlog = '';
            if ($post->parent) {
                try {
                    $blogPage = \Modules\Content\Models\Content::find($post->parent);
                    $parentBlog = $blogPage ? 
                        "<small class='text-muted'><i class='fas fa-folder'></i> {$blogPage->title}</small><br>" : '';
                } catch (\Exception $e) {
                    // Ignore
                }
            }

            $cards .= "
            <div class='col-md-6 col-lg-4 mb-3'>
                <div class='card h-100 post-card'>
                    <div class='card-body'>
                        <div class='d-flex justify-content-between align-items-start mb-2'>
                            <h6 class='card-title mb-0'>{$post->title}</h6>
                            {$statusBadge}
                        </div>
                        <p class='card-text small'>{$excerpt}</p>
                    </div>
                    <div class='card-footer bg-transparent'>
                        {$authorInfo}
                        {$parentBlog}
                        <small class='text-muted'><i class='fas fa-calendar'></i> {$postDate}</small>
                    </div>
                </div>
            </div>";
        }
        
        $cards .= "</div>";

        return $header . $cards;
    }
}
