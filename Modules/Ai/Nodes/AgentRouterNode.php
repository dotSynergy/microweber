<?php

declare(strict_types=1);

namespace Modules\Ai\Nodes;

use Modules\Ai\Agents\AgentRoutingOutput;
use Modules\Ai\Agents\BaseAgent;
use Modules\Ai\Events\ProgressEvent;
use Modules\Ai\Events\SpecializedAgentExecutionEvent;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Workflow\Node;
use NeuronAI\Workflow\StartEvent;
use NeuronAI\Workflow\WorkflowState;

class AgentRouterNode extends Node
{
    public function __construct(
        protected BaseAgent $routingAgent
    ) {
    }

    public function __invoke(StartEvent $event, WorkflowState $state): \Generator|SpecializedAgentExecutionEvent
    {
        $userQuery = $state->get('user_query', '');
        
        yield new ProgressEvent("🤖 Analyzing your request to determine the best agent...");

        $routingPrompt = $this->buildRoutingPrompt($userQuery);

        /** @var AgentRoutingOutput $routing */
        $routing = $this->routingAgent->structured(
            new UserMessage($routingPrompt),
            AgentRoutingOutput::class
        );

        yield new ProgressEvent("🎯 Routing to {$routing->agent_type} agent (confidence: " . round($routing->confidence * 100, 1) . "%)");
        yield new ProgressEvent("💭 Reasoning: {$routing->reasoning}");

        $state->set('agent_routing', [
            'agent_type' => $routing->agent_type,
            'confidence' => $routing->confidence,
            'reasoning' => $routing->reasoning,
            'context' => $routing->context
        ]);

        return new SpecializedAgentExecutionEvent(
            agentType: $routing->agent_type,
            userQuery: $userQuery,
            context: $routing->context
        );
    }

    protected function buildRoutingPrompt(string $userQuery): string
    {
        return <<<EOT
You are an intelligent agent router for the Microweber CMS system. Your job is to analyze user queries and determine which specialized agent should handle them.

Available Agents:
1. **content** - Handles content management, SEO, blog posts, pages, writing, editing, trending topics, Google Trends research
2. **shop** - Handles e-commerce, products, orders, inventory, pricing, categories, shopping-related queries
3. **customer** - Handles customer management, user accounts, customer support, customer data, addresses
4. **general** - Handles system questions, general help, configuration, and queries that don't fit other categories

User Query: "{$userQuery}"

Guidelines:
- If the query mentions products, shopping, orders, prices, inventory, SKU, or e-commerce → use "shop"
- If the query mentions customers, users, accounts, addresses, customer data → use "customer"  
- If the query mentions content, writing, SEO, blogs, pages, trending topics, Google Trends → use "content"
- If the query is about system help, configuration, general questions → use "general"
- If unsure, use "general" with lower confidence

Consider keywords, intent, and context. Provide high confidence (0.8+) only when very certain.

Examples:
- "Find products under €50" → shop (high confidence)
- "Search for customer john@example.com" → customer (high confidence)
- "Help me write a blog post" → content (high confidence)
- "What trending topics should I write about?" → content (high confidence)
- "How do I configure Microweber?" → general (medium confidence)
EOT;
    }
}
