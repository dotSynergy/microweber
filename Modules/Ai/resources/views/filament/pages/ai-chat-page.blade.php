<x-filament-panels::page>
    <div class="ai-chat-container">
        <!-- Chat History -->
        <div class="chat-history mb-4" style="height: 500px; overflow-y: auto; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1rem; background: #fafafa;">
            @foreach($chatHistory as $index => $chat)
                <div class="chat-message mb-3 {{ $chat['type'] === 'user' ? 'text-end' : 'text-start' }}">
                    <div class="d-inline-block" style="max-width: 80%;">
                        @if($chat['type'] === 'user')
                            <div class="bg-primary text-white p-3 rounded" style="border-radius: 1rem 1rem 0.25rem 1rem !important;">
                                <div class="fw-bold mb-1">
                                    <i class="fas fa-user me-1"></i> You
                                </div>
                                <div>{!! nl2br(e($chat['message'])) !!}</div>
                            </div>
                        @elseif($chat['type'] === 'assistant')
                            <div class="bg-light border p-3 rounded" style="border-radius: 1rem 1rem 1rem 0.25rem !important;">
                                <div class="fw-bold mb-2 text-primary">
                                    @if(isset($chat['agent']))
                                        @switch($chat['agent'])
                                            @case('customer')
                                                <i class="fas fa-users me-1"></i> Customer Service
                                                @break
                                            @case('shop')
                                                <i class="fas fa-shopping-cart me-1"></i> Shop Assistant
                                                @break
                                            @case('content')
                                                <i class="fas fa-edit me-1"></i> Content Manager
                                                @break
                                            @default
                                                <i class="fas fa-robot me-1"></i> AI Assistant
                                        @endswitch
                                    @else
                                        <i class="fas fa-robot me-1"></i> AI Assistant
                                    @endif
                                </div>
                                <div>{!! $chat['message'] !!}</div>
                            </div>
                        @else
                            <div class="bg-danger text-white p-3 rounded">
                                <div class="fw-bold mb-1">
                                    <i class="fas fa-exclamation-triangle me-1"></i> Error
                                </div>
                                <div>{{ $chat['message'] }}</div>
                            </div>
                        @endif
                        
                        <div class="small text-muted mt-1">
                            {{ $chat['timestamp'] }}
                        </div>
                    </div>
                </div>
            @endforeach
            
            @if($isProcessing)
                <div class="chat-message mb-3 text-start">
                    <div class="d-inline-block bg-light border p-3 rounded" style="border-radius: 1rem 1rem 1rem 0.25rem !important;">
                        <div class="fw-bold mb-2 text-primary">
                            <i class="fas fa-robot me-1"></i> AI Assistant
                        </div>
                        <div>
                            <i class="fas fa-spinner fa-spin me-2"></i>
                            Thinking...
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Chat Form -->
        <div class="chat-input-area">
            <form wire:submit="sendMessage" class="d-flex flex-column gap-3">
                {{ $this->form }}
                
                <div class="d-flex gap-2 align-items-center">
                    <button 
                        type="submit" 
                        class="btn btn-primary flex-grow-1"
                        wire:loading.attr="disabled"
                        wire:target="sendMessage"
                        @disabled($isProcessing || empty($userMessage))
                    >
                        <span wire:loading.remove wire:target="sendMessage">
                            <i class="fas fa-paper-plane me-2"></i>Send Message
                        </span>
                        <span wire:loading wire:target="sendMessage">
                            <i class="fas fa-spinner fa-spin me-2"></i>Sending...
                        </span>
                    </button>
                    
                    <button 
                        type="button" 
                        class="btn btn-outline-secondary"
                        wire:click="clearChat"
                        wire:confirm="Are you sure you want to clear the chat history?"
                    >
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </form>
        </div>

        <!-- Quick Action Buttons -->
        <div class="quick-actions mt-4">
            <h6 class="mb-3">Quick Actions:</h6>
            <div class="row">
                <div class="col-md-3 mb-2">
                    <button 
                        type="button" 
                        class="btn btn-outline-primary btn-sm w-100"
                        wire:click="$set('userMessage', 'Find customer john@example.com')"
                    >
                        <i class="fas fa-users me-1"></i>
                        Find Customer
                    </button>
                </div>
                <div class="col-md-3 mb-2">
                    <button 
                        type="button" 
                        class="btn btn-outline-success btn-sm w-100"
                        wire:click="$set('userMessage', 'Search for products under €50')"
                    >
                        <i class="fas fa-shopping-cart me-1"></i>
                        Search Products
                    </button>
                </div>
                <div class="col-md-3 mb-2">
                    <button 
                        type="button" 
                        class="btn btn-outline-warning btn-sm w-100"
                        wire:click="$set('userMessage', 'Help me write SEO content')"
                    >
                        <i class="fas fa-edit me-1"></i>
                        Content Help
                    </button>
                </div>
                <div class="col-md-3 mb-2">
                    <button 
                        type="button" 
                        class="btn btn-outline-info btn-sm w-100"
                        wire:click="$set('userMessage', 'What can you help me with?')"
                    >
                        <i class="fas fa-question-circle me-1"></i>
                        General Help
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Auto-scroll script -->
    <script>
        document.addEventListener('livewire:updated', () => {
            const chatHistory = document.querySelector('.chat-history');
            if (chatHistory) {
                chatHistory.scrollTop = chatHistory.scrollHeight;
            }
        });

        // Also scroll on initial load
        document.addEventListener('DOMContentLoaded', () => {
            const chatHistory = document.querySelector('.chat-history');
            if (chatHistory) {
                chatHistory.scrollTop = chatHistory.scrollHeight;
            }
        });
    </script>

    <!-- Custom styles -->
    <style>
        .chat-history::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-history::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .chat-history::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        
        .chat-history::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
        
        .chat-message {
            animation: fadeIn 0.3s ease-in;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .quick-actions .btn {
            font-size: 0.875rem;
            padding: 0.5rem;
        }
    </style>
</x-filament-panels::page>
