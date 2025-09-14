<?php

declare(strict_types=1);

namespace Modules\Ai\Agents;

use GuzzleHttp\Exception\RequestException;
use NeuronAI\Agent;
use NeuronAI\Chat\Messages\Message;
use NeuronAI\Chat\Messages\ToolCallMessage;
use NeuronAI\Chat\Messages\UserMessage;
use NeuronAI\Exceptions\AgentException;
use NeuronAI\Observability\Events\AgentError;
use NeuronAI\Observability\Events\InferenceStart;
use NeuronAI\Observability\Events\InferenceStop;
use NeuronAI\Providers\AIProviderInterface;
use NeuronAI\Providers\Anthropic\Anthropic;
use NeuronAI\Providers\Deepseek;
use NeuronAI\Providers\Gemini\Gemini;
use NeuronAI\Providers\Mistral;
use NeuronAI\Providers\Ollama\Ollama;
use NeuronAI\Providers\OpenAI\OpenAI;
use NeuronAI\StructuredOutput\JsonSchema;
use NeuronAI\SystemPrompt;
use NeuronAI\Tools\ToolInterface;
use NeuronAI\Tools\Toolkits\ToolkitInterface;
use NeuronAI\AgentInterface;
use NeuronAI\Workflow\WorkflowState;

abstract class BaseAgent extends Agent
{
    protected array $tools = [];
    protected string $domain;
    protected int $maxTries = 5;
    protected string $instructions = 'Your are a helpful and friendly AI that can help with anything that is asked.';
    protected $providerName = null;
    protected $model = null;

    public function __construct(?string $providerName = null, ?string $model = null, protected array $dependencies = [])
    {
        if ($providerName) {
            $this->providerName = $providerName;
        } else {
            $this->providerName = config('modules.ai.default_driver');
        }

        if ($model) {
            $this->model = $model;
        } else {
            $this->model = config('modules.ai.drivers.' . $this->providerName . '.model');
        }

        $this->setupTools();
    }

    abstract protected function setupTools(): void;

    public function setState(WorkflowState $state): void
    {
        foreach ($this->tools as $tool) {
            if (method_exists($tool, 'setState')) {
                $tool->setState($state);
            }
        }
    }

    protected function tools(): array
    {
        return $this->tools;
    }

    public function addTool(ToolInterface|ToolkitInterface|array $tools): AgentInterface
    {
        if (is_array($tools)) {
            foreach ($tools as $tool) {
                $this->tools[] = $tool;
            }
        } else {
            $this->tools[] = $tools;
        }
        
        return $this;
    }

    public function getTools(): array
    {
        return $this->tools;
    }

    protected function provider(): AIProviderInterface
    {
        // return an AI provider (Anthropic, OpenAI, Ollama, Gemini, etc.)


        if ($this->providerName == 'anthropic') {
            return new Anthropic(
                key: config('modules.ai.drivers.anthropic.api_key'),
                model: $this->model,
            );
        }

        if ($this->providerName == 'openai') {
            return new OpenAI(
                key: config('modules.ai.drivers.openai.api_key'),
                model: $this->model,
            );
        }

        if ($this->providerName == 'ollama') {
            return new Ollama(
                url: config('modules.ai.drivers.ollama.url'),
                model: $this->model,
            );
        }

        if ($this->providerName == 'deepseek') {
            return new Deepseek(
                key: config('modules.ai.drivers.deepseek.api_key'),
                model: $this->model,
            );
        }

        if ($this->providerName == 'mistral') {
            return new Mistral(
                key: config('modules.ai.drivers.mistral.api_key'),
                model: $this->model,
            );
        }


        if ($this->providerName == 'gemini') {
            return new Gemini(
                key: config('modules.ai.drivers.gemini.api_key'),
                model: $this->model,
            );
        }

    }
}
