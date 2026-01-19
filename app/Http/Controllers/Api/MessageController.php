<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MessageResource;
use App\Repositories\Contracts\MessageRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

/**
 * Handles message-related API endpoints.
 */
class MessageController extends Controller
{
    /**
     * @param MessageRepositoryInterface $messageRepository
     */
    public function __construct(
        private MessageRepositoryInterface $messageRepository
    ) {}

    /**
     * Returns paginated list of sent messages.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function sent(Request $request)
    {
        $perPage = (int) $request->query('per_page', 15);

        Log::channel('messages')->info('GET /api/v1/messages/sent called', [
            'per_page' => $perPage,
            'ip' => $request->ip(),
        ]);

        try {
            $messages = $this->messageRepository->getSentPaginated($perPage);

            Log::channel('messages')->debug('Sent messages fetched', [
                'count' => $messages->count(),
                'current_page' => $messages->currentPage(),
                'per_page' => $messages->perPage(),
            ]);

            return MessageResource::collection($messages);

        } catch (\Throwable $e) {

            Log::channel('messages')->error('Error listing sent messages', [
                'per_page' => $perPage,
                'exception' => $e->getMessage(),
            ]);

            return response()->json([
                'error' => 'Unable to fetch sent messages'
            ], 500);
        }
    }
}
