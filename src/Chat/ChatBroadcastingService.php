<?php

declare(strict_types=1);

namespace App\Chat;

use Neu\Component\Database\DatabaseInterface;
use Psl\Json;
use Psl\Html;

final readonly class ChatBroadcastingService
{
    /**
     * Create a new chat broadcasting service instance.
     *
     * @param DatabaseInterface $database The database instance.
     */
    public function __construct(
        private DatabaseInterface $database
    ) {
    }

    /**
     * Notify that a user has joined the chat.
     *
     * @param string $username The username of the user who joined.
     */
    public function joined(string $username): void
    {
        $this->broadcast('system', '"' . $username . '" has joined the chat');
    }

    /**
     * Broadcast a message to the chat.
     *
     * @param string $username The username of the sender.
     * @param string $message The message to broadcast.
     */
    public function broadcast(string $username, string $message): void
    {
        $this->database->getNotifier('chat')->notify(Json\encode([
            'username' => Html\encode_special_characters($username),
            'message' => Html\encode_special_characters($message),
        ]));
    }
}