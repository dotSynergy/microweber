<?php

namespace Modules\Ai\Services;

use InvalidArgumentException;
use Modules\Ai\Services\Drivers\AiServiceInterface;
use Modules\Ai\Services\Drivers\GeminiAiDriver;
use Modules\Ai\Services\Drivers\OllamaAiDriver;
use Modules\Ai\Services\Drivers\OpenAiDriver;
use Modules\Ai\Services\Drivers\OpenRouterAiDriver;
use Modules\Ai\Services\Drivers\SupadataAiDriver;

class AiService implements AiServiceInterface
{
    /**
     * The active driver instance.
     *
     * @var \Modules\Ai\Services\Drivers\AiServiceInterface
     */
    protected AiServiceInterface $driver;

    /**
     * The available driver instances.
     *
     * @var array
     */
    protected array $drivers = [];

    /**
     * The configuration for all drivers.
     *
     * @var array
     */
    protected array $config = [];

    /**
     * Create a new AI service instance.
     *
     * @param string $defaultDriver
     * @param array $config
     */
    public function __construct(string $defaultDriver, array $config)
    {
        $this->config = $config;
        $this->driver = $this->createDriver($defaultDriver, $config[$defaultDriver] ?? []);
    }

    /**
     * Create a new driver instance.
     *
     * @param string $driver
     * @param array $config
     * @return \Modules\Ai\Services\Drivers\AiServiceInterface
     *
     * @throws InvalidArgumentException
     */
    protected function createDriver(string $driver, array $config): AiServiceInterface
    {
        if (isset($this->drivers[$driver])) {
            return $this->drivers[$driver];
        }

        $driverClass = match ($driver) {
            'openai' => OpenAiDriver::class,
            'openrouter' => OpenRouterAiDriver::class,
            'gemini' => GeminiAiDriver::class,
            'ollama' => OllamaAiDriver::class,
            //todo add more drivers
            default => throw new InvalidArgumentException("Driver [{$driver}] not supported."),
        };

        return $this->drivers[$driver] = new $driverClass($config);
    }

    /**
     * Send messages to chat and get a response.
     *
     * @param array $messages Array of messages
     * @param array $options Additional options
     * @return string|array The generated content or function call response array
     */
    public function sendToChat(array $messages, array $options = []): string|array
    {
        // Check if the current driver is enabled
        $driverName = $this->driver->getActiveDriver();
        $isEnabled = $this->config[$driverName]['enabled'] ?? false;

        if (!$isEnabled) {
            throw new \Exception("AI driver '$driverName' is not enabled. Please enable it in the settings.");
        }

        return $this->driver->sendToChat($messages, $options);
    }





    /**
     * Get the name of the currently active AI driver.
     *
     * @return string
     */
    public function getActiveDriver(): string
    {
        return $this->driver->getActiveDriver();
    }

    /**
     * Set the active AI driver.
     *
     * @param string $driver
     * @return void
     */
    public function setActiveDriver(string $driver): void
    {
        $this->driver = $this->createDriver($driver, $this->config[$driver] ?? []);
    }
}

