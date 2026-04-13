<?php

namespace App\EventListener;

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

        // 422 — #[MapRequestPayload] validation hatası
        $previous = $exception->getPrevious();
        if ($previous instanceof ValidationFailedException) {
            $errors = [];
            foreach ($previous->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            $event->setResponse(new JsonResponse(['errors' => $errors], Response::HTTP_UNPROCESSABLE_ENTITY));
            return;
        }

        // Domain exception'ları — exception code'u HTTP status olarak kullan (404, 409, vs.)
        if ($exception instanceof \RuntimeException) {
            $code = $exception->getCode();
            if (in_array($code, [400, 403, 404, 409, 422], strict: true)) {
                $event->setResponse(new JsonResponse(['error' => $exception->getMessage()], $code));
                return;
            }
        }

        // Symfony HTTP exception'ları (404 route not found, 405 method not allowed, vs.)
        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse(
                ['error' => $exception->getMessage()],
                $exception->getStatusCode(),
            ));
            return;
        }

        // 500 — beklenmeyen hatalar, logla ama response set etme (Symfony default error handler devreye girer)
        $this->logger->error($exception->getMessage(), ['exception' => $exception]);
    }
}
