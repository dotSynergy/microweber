<?php

declare(strict_types=1);

namespace Modules\Ai\Agents;

use Modules\Ai\Services\AgentFactory;
use Modules\Ai\Services\RagSearchService;
use Modules\Ai\Tools\RagSearchTool;
use Modules\Ai\Workflows\GeneralAgentWorkflow;
use NeuronAI\SystemPrompt;
use NeuronAI\Workflow\WorkflowState;
use Illuminate\Support\Facades\Log;

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
                'You are an intelligent routing agent for the Microweber CMS system.',
                'Your primary job is to analyze user queries and route them to the appropriate specialized agent.',
                'You have access to specialized agents for different domains: content, shop, customer, and general assistance.',
                'You make routing decisions based on query analysis and intent detection.',
                'When used as a routing agent, provide structured routing decisions.',
                'When used for general assistance, provide helpful information about the system.',
            ],
            steps: [
                'Analyze the user query to understand the intent and domain.',
                'Identify keywords and context that indicate which specialized agent should handle the request.',
                'Make confident routing decisions when the domain is clear.',
                'Use general assistance when the query doesn\'t fit specific domains.',
                'Provide clear reasoning for routing decisions.',
            ],
            output: [
                'For routing: Provide structured routing decisions with confidence scores.',
                'For general help: Provide clear, helpful responses formatted with proper HTML.',
                'Include explanations and reasoning in routing decisions.',
                'Format responses appropriately for the context and intended use.',
            ],
        );
    }

    protected function setupTools(): void
    {
    //    $this->addTool(new RagSearchTool($this->ragService, $this->dependencies));
    }

    /**
     * Handle user query using workflow pattern
     */
    public function handle(string $message): string
    {
        try {
            // Create routing agent (using a simple base agent for routing decisions)
            $routingAgent = new class($this->providerName, $this->model) extends BaseAgent {
                protected function setupTools(): void {
                    // Routing agent doesn't need tools
                }

                public function instructions(): string {
                    return (string)new SystemPrompt(
                        background: [
                            'You are an intelligent agent router for the Microweber CMS system.',
                            'Your job is to analyze user queries and determine which specialized agent should handle them.',
                        ],
                        steps: [
                            'Analyze the user query to understand intent and domain.',
                            'Choose the most appropriate agent type based on keywords and context.',
                            'Provide confidence score and clear reasoning.',
                        ],
                        output: [
                            'Provide structured routing decisions with agent_type, confidence, reasoning, and context.',
                        ],
                    );
                }
            };

            // Create and execute the workflow
            $workflow = new GeneralAgentWorkflow(
                userQuery: $message,
                agentFactory: $this->agentFactory,
                routingAgent: $routingAgent
            );

            $handler = $workflow->start();

            // Collect progress events and final result
            $progressMessages = [];
            foreach ($handler->streamEvents() as $event) {
                if (method_exists($event, 'message')) {
                    $progressMessages[] = $event->message;
                }
            }

            $result = $handler->getResult();
            $response = $result->get('agent_response', '');

            // If we have a response, return it
            if (!empty($response)) {
                return $response;
            }

            // Fallback to general help if no response
            return $this->getGeneralHelp();

        } catch (\Exception $e) {
            Log::error('GeneralAgent workflow error', [
                'message' => $message,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Fallback to direct agent handling
            return $this->handleDirectly($message);
        }
    }

    /**
     * Direct handling fallback (original method)
     */
    protected function handleDirectly(string $message): string
    {
        // Determine which agent to use based on simple keyword matching
        $domain = $this->detectDomain($message);

        if ($domain !== 'general') {
            return $this->routeToSpecializedAgent($domain, $message);
        }

        // Handle as general query
        return $this->getGeneralHelp();
    }

    /**
     * Simple domain detection based on keywords
     */
    protected function detectDomain(string $message): string
    {
        $message = strtolower($message);

        // Content domain keywords
        if (preg_match('/\b(content|blog|post|page|write|seo|article|trending|trends|google trends)\b/', $message)) {
            return 'content';
        }

        // Shop domain keywords
        if (preg_match('/\b(product|shop|order|price|inventory|sku|buy|sell|cart|checkout)\b/', $message)) {
            return 'shop';
        }

        // Customer domain keywords
        if (preg_match('/\b(customer|user|account|address|email|phone|client)\b/', $message)) {
            return 'customer';
        }

        return 'general';
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
            Log::error('Agent routing error', [
                'domain' => $domain,
                'message' => $message,
                'error' => $e->getMessage()
            ]);
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
                                <li>Research trending topics with Google Trends</li>
                                <li>Suggest content improvements</li>
                                <li>Provide writing and formatting guidance</li>
                            </ul>
                            <p><strong>Try asking:</strong> "What are trending topics about AI?" or "Help me write a product description"</p>
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
                    <li>Ask about trending topics to get data-driven content ideas</li>
                </ul>
            </div>
        </div>';
    }
}
