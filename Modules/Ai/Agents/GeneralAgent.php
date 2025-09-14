<?php

declare(strict_types=1);

namespace Modules\Ai\Agents;

use Modules\Ai\Services\AgentFactory;
use Modules\Ai\Services\RagSearchService;
use Modules\Ai\Tools\RagSearchTool;
use NeuronAI\SystemPrompt;
use NeuronAI\Workflow\WorkflowState;

class GeneralAgent extends BaseAgent
{
    protected string $domain = 'general';
    
    protected AgentFactory $agentFactory;
    protected RagSearchService $ragService;
    
    public function __construct(
        AgentFactory $agentFactory,
        RagSearchService $ragService,
        ?string $providerName = null,
        ?string $model = null,
        protected array $dependencies = []
    ) {
        $this->agentFactory = $agentFactory;
        $this->ragService = $ragService;
        parent::__construct($providerName, $model, $dependencies);
    }

    public function instructions(): string
    {
        return (string)new SystemPrompt(
            background: [
                'You are a General AI Assistant for the Microweber CMS system with advanced search capabilities.',
                'You have access to RAG (Retrieval-Augmented Generation) search that can find information across all system data.',
                'You can search customers, products, content, orders, and previous conversations to provide comprehensive answers.',
                'For any informational query, you MUST use the rag_search tool to find relevant information before responding.',
                'You also have access to specialized agents for different domains when needed.',
            ],
            steps: [
                'For ANY question that asks for information, facts, or data, you MUST call the rag_search tool first.',
                'Use the exact search results provided by the tool as the main part of your response.',
                'Preserve all formatting and information from the search results.',
                'You can add context or explanation, but do not omit the search results.',
                'For customer-related queries, include customer search in your RAG search.',
                'For product-related queries, include product search in your RAG search.',
                'For general questions, search across all content types.',
                'Only respond directly without search for simple greetings or small talk.',
            ],
            output: [
                'Provide clear, helpful responses formatted with proper HTML.',
                'When suggesting searches, explain what information the user can find.',
                'Include examples of how to use different search options.',
                'Use headings, lists, and formatting to make information easy to read.',
                'If you can\'t help with something specific, explain what the system can do and suggest alternatives.',
            ],
        );
    }

    protected function setupTools(): void
    {
        $this->addTool(new RagSearchTool($this->ragService, $this->dependencies));
    }
    
    public function routeToSpecializedAgent(string $domain, string $message): string
    {
        try {
            switch ($domain) {
                case 'customer':
                    $agent = $this->agentFactory->agent('customer', $this->providerName, $this->model);
                    return $agent->handle($message);
                    
                case 'shop':
                case 'product':
                    $agent = $this->agentFactory->agent('shop', $this->providerName, $this->model);
                    return $agent->handle($message);
                    
                case 'content':
                    $agent = $this->agentFactory->agent('content', $this->providerName, $this->model);
                    return $agent->handle($message);
                    
                default:
                    return $this->getGeneralHelp();
            }
        } catch (\Exception $e) {
            return $this->getGeneralHelp();
        }
    }
    
    public function getGeneralHelp(): string
    {
        return '
        <div class="general-help">
            <h3>Welcome to Microweber AI Assistant</h3>
            <p>I can help you with various tasks in your Microweber CMS. Here are some things you can ask me about:</p>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-users"></i> Customer Management</h5>
                        </div>
                        <div class="card-body">
                            <p>I can help you find and manage customers:</p>
                            <ul>
                                <li>Search customers by email, phone, or name</li>
                                <li>View customer details and addresses</li>
                                <li>Check customer order history</li>
                            </ul>
                            <p><strong>Try asking:</strong> "Find customer john@example.com" or "Search for customers named Smith"</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-shopping-cart"></i> Product & Shop Management</h5>
                        </div>
                        <div class="card-body">
                            <p>I can help you manage your online store:</p>
                            <ul>
                                <li>Search products by name, SKU, or category</li>
                                <li>Filter products by price range</li>
                                <li>Check product inventory and details</li>
                            </ul>
                            <p><strong>Try asking:</strong> "Search for products under €50" or "Find products in Electronics category"</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-edit"></i> Content Management</h5>
                        </div>
                        <div class="card-body">
                            <p>I can assist with content creation and management:</p>
                            <ul>
                                <li>Help create SEO-friendly content</li>
                                <li>Suggest content improvements</li>
                                <li>Provide writing and formatting guidance</li>
                            </ul>
                            <p><strong>Try asking:</strong> "Help me write a product description" or "SEO tips for blog posts"</p>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-question-circle"></i> General Help</h5>
                        </div>
                        <div class="card-body">
                            <p>I can provide general assistance with:</p>
                            <ul>
                                <li>Microweber CMS features and functionality</li>
                                <li>Best practices and recommendations</li>
                                <li>Troubleshooting guidance</li>
                            </ul>
                            <p><strong>Try asking:</strong> "How do I..." or "What is the best way to..."</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info">
                <h6>💡 Tips for better results:</h6>
                <ul class="mb-0">
                    <li>Be specific in your requests (include email addresses, product names, etc.)</li>
                    <li>Use natural language - ask questions as you would to a colleague</li>
                    <li>If you don\'t find what you\'re looking for, try different search terms</li>
                </ul>
            </div>
        </div>';
    }
}
