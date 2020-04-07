<?php declare(strict_types=1);

namespace App\Controller;

use App\Controller\HttpResponseController;
use App\Entity\Item;
use App\Entity\ShoppingList;
use App\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api", name="api_")
 */
final class ShoppingListController extends HttpResponseController
{

    /**
     * @Route("/list", name="add_list", methods={"POST"})
     */
    public function addList(Request $request, ValidatorInterface $validator) : JsonResponse
    {
        $input = json_decode($request->getContent());

        if ($input === null) {
            return $this->badParameterError('Invalid input JSON');
        }

        if (!property_exists($input, 'name') || !is_string($input->name) || !strlen($input->name)) {
            return $this->badParameterError('Missing, empty or non string value for the "name" property');
        }

        $list = new ShoppingList($input->name, $this->getUser());

        $errors = $validator->validate($list);

        if ($errors->count()) {
            return $this->handleValidationErrors($errors);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($list);
        $entityManager->flush();

        return $this->emptyJsonResponse(201);
    }

    /**
     * @Route("/list/{list}/items", name="add_items", methods={"POST"})
     */
    public function addItems(ShoppingList $list, Request $request) : JsonResponse
    {
        $inputItems = json_decode($request->getContent());

        if ($inputItems === null) {
            return $this->badParameterError('Invalid input JSON');
        }

        $items = [];

        foreach ($inputItems as $inputItem) {
            if (!property_exists($inputItem, 'name') || !is_string($inputItem->name) || !strlen($inputItem->name)) {
                return $this->badParameterError('Missing, empty or non string value for the "name" property');
            }
            if (!property_exists($inputItem, 'checked') || !is_bool($inputItem->checked)) {
                return $this->badParameterError('Missing, empty or non string value for the "checked" property');
            }

            $items[] = new Item($inputItem->name, $list, $inputItem->checked);
        }

        $entityManager = $this->getDoctrine()->getManager();
        foreach ($items as $item) {
            $entityManager->persist($item);
        }

        $list->addItems($items);
        $entityManager->persist($list);
        $entityManager->flush();

        return $this->emptyJsonResponse(201);
    }

    /**
     * @Route("/list/{list}/items", name="get_items", methods={"GET"})
     */
    public function getItems(ShoppingList $list) : JsonResponse
    {
        return $this->json($list->getItems(), 200, [], [
            'groups' => 'NoChildren'
        ]);
    }

    /**
     * Adds a user to a shoppinglist, making it shared between users
     * @Route("/list/{list}/user/{user}", name="add_owner", methods={"POST"})
     */
    public function addOwner(ShoppingList $list, User $user) : JsonResponse
    {
        $list->addOwner($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($list);
        $entityManager->flush();
        return $this->emptyJsonResponse();
    }
}