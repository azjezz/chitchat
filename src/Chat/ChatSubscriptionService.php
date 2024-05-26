<?php

declare(strict_types=1);

namespace App\Chat;

use Amp\Pipeline\ConcurrentIterator;
use Amp\Pipeline\Queue;
use Amp\Sql\SqlException;
use Neu\Component\Database\DatabaseInterface;
use Neu\Component\Database\Notification\ListenerInterface;
use Psr\Log\LoggerInterface;
use Revolt\EventLoop;
use Psl\Json;

use function count;

final class ChatSubscriptionService
{
    /**
     * The database instance.
     */
    private DatabaseInterface $database;

    /**
     * The logger instance.
     */
    private LoggerInterface $logger;

    /**
     * The next subscriber ID.
     */
    private int $id = 0;

    /**
     * The list of subscribers.
     *
     * @var array<int, Queue<array{username: string, message: string}>>
     */
    private array $subscribers = [];

    /**
     * The database listener instance.
     */
    private ?ListenerInterface $listener = null;

    /**
     * Create a new subscription service instance.
     *
     * @param DatabaseInterface $database The database instance.
     * @param LoggerInterface $logger The logger instance.
     */
    public function __construct(DatabaseInterface $database, LoggerInterface $logger)
    {
        $this->database = $database;
        $this->logger = $logger;
    }

    /**
     * Subscribe to chat notifications.
     */
    public function subscribe(): int
    {
        $source = new Queue();

        $id = ++$this->id;
        $this->subscribers[$id] = $source;

        $this->logger->info('New subscriber connected: ' . $id, [
            'id' => $id,
            'subscribers' => count($this->subscribers),
        ]);

        return $id;
    }

    /**
     * Get an iterator for the given subscriber ID.
     *
     * @return ConcurrentIterator<array{username: string, message: string}>|null The iterator or null if the subscriber does not exist.
     */
    public function getPipeline(int $id): ?ConcurrentIterator
    {
        // Start listening if not already started.
        $this->listen();

        $source = $this->subscribers[$id] ?? null;

        return $source?->iterate();
    }

    /**
     * Unsubscribe a subscriber by ID.
     */
    public function unsubscribe(int $id): void
    {
        $this->logger->info('Subscriber disconnected: ' . $id, [
            'id' => $id,
            'subscribers' => count($this->subscribers) - 1,
        ]);

        if (isset($this->subscribers[$id])) {
            $this->subscribers[$id]->complete();
            unset($this->subscribers[$id]);
        }

        if ([] === $this->subscribers) {
            $this->logger->info('No more subscribers, stopping listener');

            $this->close();
        }
    }

    /**
     * Start listening for notifications if not already started.
     */
    private function listen(): void
    {
        if (null === $this->listener) {
            $this->logger->info('Starting listener');

            $this->listener = $listener = $this->database->getListener('chat');

            EventLoop::queue(function() use($listener): void {
                try {
                    foreach ($listener->listen() as $notification) {
                        foreach ($this->subscribers as $subscriber) {
                            $data = Json\decode($notification->payload);

                            $subscriber->push($data);
                        }
                    }
                } catch (SqlException) {
                    foreach ($this->subscribers as $id => $_) {
                        $this->unsubscribe($id);
                    }
                }
            });
        }
    }

    /**
     * Close the listener and reset the listener instance.
     */
    private function close(): void
    {
        $this->listener?->close();
        $this->listener = null;
    }

    /**
     * Destructor to ensure the listener is closed.
     */
    public function __destruct()
    {
        $this->close();
    }
}