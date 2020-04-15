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
        if (!$list->getOwners()->contains($this->getUser())) {
            return $this->accessDeniedJsonResponse(); 
        }

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
     * @Route("/list/{list}/items", name="delete_items", methods={"DELETE"})
     */
    public function deleteItems(ShoppingList $list, Request $request) : JsonResponse
    {
        if (!$list->getOwners()->contains($this->getUser())) {
            return $this->accessDeniedJsonResponse(); 
        }

        $input = json_decode($request->getContent());

        if ($input === null) {
            return $this->badParameterError('Invalid input JSON');
        }

        if (!is_array($input) || empty($input)) {
            return $this->badParameterError('Expected a non empty array of item ids');
        }

        foreach ($input as $itemId) {
            if (!is_integer($itemId) || $itemId < 0) {
                return $this->badParameterError('The item ids must be positive integers');
            }
            $list->getItems()->remove($itemId);
        }

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($list);
        $entityManager->flush();

        return $this->emptyJsonResponse();
    }

    /**
     * @Route("/list/{list}/items", name="update_items", methods={"PATCH"})
     */
    public function updateItems(ShoppingList $list, Request $request) : JsonResponse
    {
        if (!$list->getOwners()->contains($this->getUser())) {
            return $this->accessDeniedJsonResponse(); 
        }

        $input = json_decode($request->getContent());

        if ($input === null) {
            return $this->badParameterError('Invalid input JSON');
        }

        if (!is_array($input) || empty($input)) {
            return $this->badParameterError('Expected a non empty array of item ids');
        }

        $entityManager = $this->getDoctrine()->getManager();

        $listItems = $list->getItems();
        foreach ($input as $inputItem) {
            if (!property_exists($inputItem, 'id')) {
                return $this->badParameterError('Missing "id" property');
            }
            $item = $listItems->get($inputItem->id);
            if ($item === null) {
                return $this->badParameterError("Invalid \"id\" property: {$inputItem->id}");
            } 
            if (property_exists($inputItem, 'name')) {
                if (!is_string($inputItem->name) || !strlen($inputItem->name)) {
                    return $this->badParameterError('Empty or non string value for the "name" property');
                }
                $item->setName($inputItem->name);
            }
            if (property_exists($inputItem, 'checked')) {
                if (!is_bool($inputItem->checked)) {
                    return $this->badParameterError('Missing, empty or non string value for the "checked" property');
                }
                $item->setChecked($inputItem->checked);
            }

            $entityManager->persist($item);
        }

        $entityManager->persist($list);
        $entityManager->flush();

        return $this->emptyJsonResponse();
    }

    /**
     * @Route("/list/{list}/items", name="get_items", methods={"GET"})
     */
    public function getItems(ShoppingList $list) : JsonResponse
    {
        if (!$list->getOwners()->contains($this->getUser())) {
            return $this->accessDeniedJsonResponse(); 
        }

        return $this->json($list->getItems()->getValues(), 200, [], [
            'groups' => 'NoChildren'
        ]);
    }

    /**
     * Adds a user to a shoppinglist, making it shared between users
     * @Route("/list/{list}/user/{user}", name="add_owner", methods={"POST"})
     */
    public function addOwner(ShoppingList $list, User $user) : JsonResponse
    {
        if (!$list->getOwners()->contains($this->getUser())) {
            return $this->accessDeniedJsonResponse(); 
        }

        $list->addOwner($user);
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($list);
        $entityManager->flush();
        return $this->emptyJsonResponse();
    }
}