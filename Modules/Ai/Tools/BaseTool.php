<?php

declare(strict_types=1);

namespace Modules\Ai\Tools;

use NeuronAI\Tools\PropertyType;
use NeuronAI\Tools\Tool;
use NeuronAI\Tools\ToolProperty;
use NeuronAI\Workflow\WorkflowState;

abstract class BaseTool extends Tool
{
    protected string $domain;
    protected array $requiredPermissions = [];
    protected WorkflowState $state;
    protected int $maxTries = 5;
    
    public function __construct(
        string $name,
        string $description,
        protected array $dependencies = []
    ) {
        parent::__construct($name, $description);
    }
    
    abstract public function __invoke(...$args): string;
    
    public function setState(WorkflowState $state): void
    {
        $this->state = $state;
    }
    
    protected function authorize(): bool
    {
        // For now, return true. In the future, implement proper permission checking
        foreach ($this->requiredPermissions as $permission) {
            // Check if user has permission - this would integrate with Microweber's permission system
            // return user_can($permission);
        }
        return true;
    }
    
    protected function validateInput(array $input): bool
    {
        // Common validation logic
        return true;
    }
    
    protected function formatAsHtmlTable(array $data, array $headers = []): string
    {
        if (empty($data)) {
            return '<div class="alert alert-info">No data found.</div>';
        }

        $html = '<div class="table-responsive mb-4">';
        $html .= '<table class="table table-striped table-bordered table-sm">';
        
        // Add headers if provided
        if (!empty($headers)) {
            $html .= '<thead class="table-light"><tr>';
            foreach ($headers as $header) {
                $html .= '<th>' . htmlspecialchars($header) . '</th>';
            }
            $html .= '</tr></thead>';
        }
        
        $html .= '<tbody>';
        
        foreach ($data as $row) {
            $html .= '<tr>';
            if (is_array($row)) {
                foreach ($row as $cell) {
                    $html .= '<td>' . htmlspecialchars((string) $cell) . '</td>';
                }
            } else {
                $html .= '<td>' . htmlspecialchars((string) $row) . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody></table></div>';
        
        return $html;
    }
    
    protected function formatAsCardGrid(array $data, array $fields = []): string
    {
        if (empty($data)) {
            return '<div class="alert alert-info">No data found.</div>';
        }

        $html = '<div class="row">';
        
        foreach ($data as $item) {
            $html .= '<div class="col-md-6 col-lg-4 mb-3">';
            $html .= '<div class="card">';
            $html .= '<div class="card-body">';
            
            if (is_array($item) || is_object($item)) {
                foreach ($fields as $field => $label) {
                    $value = is_array($item) ? ($item[$field] ?? '') : ($item->$field ?? '');
                    $html .= '<p><strong>' . htmlspecialchars($label) . ':</strong> ' . htmlspecialchars((string) $value) . '</p>';
                }
            } else {
                $html .= '<p>' . htmlspecialchars((string) $item) . '</p>';
            }
            
            $html .= '</div></div></div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    protected function handleError(string $message, bool $shouldFinish = false): string
    {
        if (isset($this->state)) {
            $this->state->set(static::class . '_finished', $shouldFinish);
        }
        
        return '<div class="alert alert-danger">' . htmlspecialchars($message) . '</div>';
    }
    
    protected function handleSuccess(string $message): string
    {
        if (isset($this->state)) {
            $this->state->set(static::class . '_finished', true);
        }
        
        return '<div class="alert alert-success">' . htmlspecialchars($message) . '</div>';
    }
    
    protected function formatMoney(float $amount, string $currency = 'EUR'): string
    {
        return number_format($amount, 2) . ' ' . $currency;
    }
    
    protected function formatDate(\DateTime $date, string $format = 'Y-m-d H:i:s'): string
    {
        return $date->format($format);
    }
}
