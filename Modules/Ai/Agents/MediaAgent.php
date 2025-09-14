<?php

declare(strict_types=1);

namespace Modules\Ai\Agents;

use Modules\Ai\Tools\MediaSearchTool;
use Modules\Ai\Tools\RagSearchTool;
use NeuronAI\SystemPrompt;
use NeuronAI\Workflow\WorkflowState;

class MediaAgent extends BaseAgent
{
    protected string $domain = 'media';

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
                'You are an AI Agent specialized in Media Management for the Microweber CMS.',
                'You can help with image optimization, file organization, media library management.',
                'You assist with media-related tasks including file uploads, image processing, and gallery management.',
            ],
            steps: [
                'When asked about media management, provide guidance on best practices.',
                'Help with image optimization and file organization strategies.',
                'Suggest proper file naming conventions and folder structures.',
                'Provide advice on image formats, sizes, and compression.',
            ],
            output: [
                'Always respond with well-formatted HTML content.',
                'Provide actionable media management recommendations.',
                'Include examples and best practices for media handling.',
                'Use clear formatting to make information easy to understand.',
            ],
        );
    }

    protected function setupTools(): void
    {
        // Add media-specific tools
        $this->addTool(new MediaSearchTool($this->dependencies));
        
        // Add RAG search tool for media content discovery
        $ragService = app(\Modules\Ai\Services\RagSearchService::class);
        $this->addTool(new RagSearchTool($ragService, $this->dependencies));
    }
}
