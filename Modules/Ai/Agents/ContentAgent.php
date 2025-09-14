<?php

declare(strict_types=1);

namespace Modules\Ai\Agents;

use Modules\Ai\Tools\MediaSearchTool;
use NeuronAI\SystemPrompt;
use NeuronAI\Workflow\WorkflowState;

class ContentAgent extends BaseAgent
{
    protected string $domain = 'content';

    public function __construct(
        ?string $providerName = null,
        ?string $model = null,
        protected array $dependencies = []
    ) {
        parent::__construct($providerName, $model, $dependencies);
    }

    public function instructions(): string
    {
        return (string)new SystemPrompt(
            background: [
                'You are an AI Agent specialized in Content Management for the Microweber CMS.',
                'You can help with content creation, editing, SEO optimization, and content analysis.',
                'You assist with pages, posts, blog articles, and general content management tasks.',
            ],
            steps: [
                'When asked about content creation, provide structured and SEO-friendly content.',
                'Help with writing compelling titles, descriptions, and meta information.',
                'Suggest content improvements and optimization strategies.',
                'Provide guidance on content structure and formatting.',
            ],
            output: [
                'Always respond with well-formatted HTML content when creating or suggesting content.',
                'Include proper heading structure (H1, H2, H3) for better SEO.',
                'Provide actionable content recommendations with clear explanations.',
                'Format responses using appropriate HTML elements for readability.',
            ],
        );
    }

    protected function setupTools(): void
    {
        // Add comprehensive content management tools
        $this->addTool(new \Modules\Ai\Tools\ContentListTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\GetContentTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\PageListTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\PostListTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\ProductListTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\ContentSearchTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\MediaSearchTool($this->dependencies));


        // Add editing tools
        $this->addTool(new \Modules\Ai\Tools\ContentEditTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\PostEditTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\ProductEditTool($this->dependencies));

        // Add creation tools - now with simple constructors
        $this->addTool(new \Modules\Ai\Tools\CreateContentTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\CreatePostTool($this->dependencies));
        $this->addTool(new \Modules\Ai\Tools\CreateProductTool($this->dependencies));

        // Add RAG search tool for broader content discovery
        $ragService = app(\Modules\Ai\Services\RagSearchService::class);
        $this->addTool(new \Modules\Ai\Tools\RagSearchTool($ragService, $this->dependencies));
    }
}
