<?php

declare(strict_types=1);

namespace Modules\Ai\Agents;

use NeuronAI\StructuredOutput\SchemaProperty;

class AgentRoutingOutput
{
    #[SchemaProperty(
        description: 'The domain/type of agent that should handle this query. Options: "content", "shop", "customer", "general"',
        required: true
    )]
    public string $agent_type;

    #[SchemaProperty(
        description: 'Confidence level in the routing decision (0.0 to 1.0)',
        required: true
    )]
    public float $confidence;

    #[SchemaProperty(
        description: 'Brief explanation of why this agent was chosen',
        required: true
    )]
    public string $reasoning;

    #[SchemaProperty(
        description: 'Any additional context or parameters to pass to the specialized agent',
        required: false
    )]
    public array $context = [];
}
