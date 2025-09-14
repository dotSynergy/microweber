<?php

declare(strict_types=1);

namespace Modules\Ai\Workflows;

use Modules\Ai\Agents\BaseAgent;
use Modules\Ai\Nodes\AgentExecutionNode;
use Modules\Ai\Nodes\AgentRouterNode;
use Modules\Ai\Services\AgentFactory;
use NeuronAI\Exceptions\WorkflowException;
use NeuronAI\Workflow\Workflow;
use NeuronAI\Workflow\WorkflowState;

class GeneralAgentWorkflow extends Workflow
{
    /**
     * @throws WorkflowException
     */
    public function __construct(
        string $userQuery,
        protected AgentFactory $agentFactory,
        protected BaseAgent $routingAgent
    ) {
        parent::__construct(new WorkflowState(['user_query' => $userQuery]));
    }

    protected function nodes(): array
    {
        return [
            new AgentRouterNode($this->routingAgent),
            new AgentExecutionNode($this->agentFactory),
        ];
    }
}
