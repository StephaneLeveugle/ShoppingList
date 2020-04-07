<?php declare(strict_types=1);

namespace App\Controller;

use App\Exception\BadParameterHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\ConstraintViolationList;

class HttpResponseController extends AbstractController
{
    public function error(string $message, int $code = 500) : JsonResponse
    {
        return $this->json(
            [
                'error' => [
                    'message' => $message,
                    'code' => $code
                ]
            ], $code
        );
    }

    public function badParameterError($message) : JsonResponse
    {
        return $this->error($message, 400);
    }

    public function handleValidationErrors(ConstraintViolationList $errors) : JsonResponse
    {
        $errorMessages = [];
        foreach ($errors as $error) {
            $errorMessages[] = "{$error->getPropertyPath()}: {$error->getMessage()}"; 
        }
        return $this->badParameterError(implode(' ', $errorMessages));
    }

    public function emptyJsonResponse(int $status = 204) : JsonResponse
    {
        return new JsonResponse(null, $status);
    }
}