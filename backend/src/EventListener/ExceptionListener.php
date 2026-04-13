<?php

namespace App\EventListener;

use App\Exception\ConflictException;
use App\Exception\NotFoundException;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsEventListener]
class ExceptionListener
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {}

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // 422 — #[MapRequestPayload] validation error
        $previous = $exception->getPrevious();
        if ($previous instanceof ValidationFailedException) {
            $errors = [];
            foreach ($previous->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $event->setResponse(new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }

        // Domain exceptions
        $statusCode = match (true) {
            $exception instanceof NotFoundException => Response::HTTP_NOT_FOUND,
            $exception instanceof ConflictException => Response::HTTP_CONFLICT,
            default                                 => null,
        };

        if ($statusCode !== null) {
            $event->setResponse(new JsonResponse(['error' => $exception->getMessage()], $statusCode));
            return;
        }

        // Symfony HTTP exceptions (404 route not found, 405 method not allowed, etc.)
        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse(
                ['error' => $exception->getMessage()],
                $exception->getStatusCode(),
            ));
            return;
        }

        // 500 — unexpected errors, log but do not set response (Symfony default error handler will take over)
        $this->logger->error($exception->getMessage(), ['exception' => $exception]);
    }
}
