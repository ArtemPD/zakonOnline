<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\MessageBag;

class UnprocessableContentException extends Exception
{
    protected MessageBag $messageBag;

    public function __construct(MessageBag $messageBag, $message = "", $code = 422, Throwable $previous = null)
    {
        $this->messageBag = $messageBag;
        parent::__construct($message, $code, $previous);
    }

    /**
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->message,
            'context' => 'data_validation',
            'errors' => $this->messageBag->getMessages(),
            'status_code' => $this->getCode(),
        ], $this->getCode());
    }
}
