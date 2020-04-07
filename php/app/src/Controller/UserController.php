<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api", name="api_")
 */
class UserController extends HttpResponseController
{

    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, ValidatorInterface $validator): JsonResponse
    {
        $input = json_decode($request->getContent());

        if ($input === null) {
            return $this->badParameterError('Invalid input JSON');
        }

        if (!property_exists($input, 'email') || !is_string($input->email) || !strlen($input->email)) {
            return $this->badParameterError('Missing, empty or non string value for the "email" property');
        }

        if (!property_exists($input, 'password') || !is_string($input->password) || !strlen($input->password)) {
            return $this->badParameterError('Missing, empty or non string value for the "password" property');
        }

        $user = new User($input->email);

        $user->setPassword($encoder->encodePassword(
            $user,
            $input->password
        ));

        $errors = $validator->validate($user);

        if ($errors->count()) {
            return $this->handleValidationErrors($errors);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return $this->emptyJsonResponse(201);
    } 

}