<?php

/*
 * This file is part of the Mobile Cart package.
 *
 * (c) Jesse Hanson <jesse@mobilecart.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MobileCart\CoreBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use MobileCart\CoreBundle\Event\CoreEvent;
use MobileCart\CoreBundle\Event\CoreEvents;
use MobileCart\CoreBundle\Constants\EntityConstants;

/**
 * Class ItemVarSetController
 * @package MobileCart\CoreBundle\Controller\Admin
 */
class ItemVarSetController extends Controller
{
    /**
     * @var string
     */
    protected $objectType = EntityConstants::ITEM_VAR_SET;

    /**
     * Lists ItemVarSet entities
     */
    public function indexAction(Request $request)
    {
        $event = new CoreEvent();
        $event->setRequest($request)
            ->setObjectType($this->objectType)
            ->setSection(CoreEvent::SECTION_BACKEND);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_SEARCH, $event);

        return $event->getResponse();
    }

    /**
     * Creates a new ItemVarSet entity
     */
    public function createAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance(EntityConstants::ITEM_VAR_SET);
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_set_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_ADMIN_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_SET_INSERT, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_SET_CREATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->getRequestAccept() == CoreEvent::JSON) {

            $invalid = [];
            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    $invalid[$childKey] = [];
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }

            return new JsonResponse([
                'success' => false,
                'invalid' => $invalid,
                'messages' => $event->getMessages(),
            ]);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Displays a form to create a new ItemVarSet entity
     */
    public function newAction(Request $request)
    {
        $entity = $this->get('cart.entity')->getInstance($this->objectType);
        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_set_create'))
            ->setFormMethod('POST');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_NEW_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Finds and displays a ItemVarSet entity
     */
    public function showAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        return new JsonResponse($entity->getData());
    }

    /**
     * Displays a form to edit an existing ItemVarSet entity
     */
    public function editAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);

        if (!$entity) {
            throw $this->createNotFoundException("Unable to find entity with ID: {$id}");
        }

        $event = new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_set_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_ADMIN_FORM, $event);

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Edits an existing ItemVarSet entity
     */
    public function updateAction(Request $request, $id)
    {
        $entity = $this->get('cart.entity')->find($this->objectType, $id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find ItemVarSet entity.');
        }

        $event= new CoreEvent();
        $event->setObjectType($this->objectType)
            ->setEntity($entity)
            ->setRequest($request)
            ->setFormAction($this->generateUrl('cart_admin_item_var_set_update', ['id' => $entity->getId()]))
            ->setFormMethod('PUT');

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_ADMIN_FORM, $event);

        $form = $event->getReturnData('form');
        if ($form->handleRequest($request)->isValid()) {

            $formData = $request->request->get($form->getName());
            $event->setFormData($formData);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_SET_UPDATE, $event);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_SET_UPDATE_RETURN, $event);

            return $event->getResponse();
        }

        if ($event->getRequestAccept() == CoreEvent::JSON) {

            $invalid = [];
            foreach($form->all() as $childKey => $child) {
                $errors = $child->getErrors();
                if ($errors->count()) {
                    $invalid[$childKey] = [];
                    foreach($errors as $error) {
                        $invalid[$childKey][] = $error->getMessage();
                    }
                }
            }

            return new JsonResponse([
                'success' => false,
                'invalid' => $invalid,
                'messages' => $event->getMessages(),
            ]);
        }

        $this->get('event_dispatcher')
            ->dispatch(CoreEvents::ITEM_VAR_SET_EDIT_RETURN, $event);

        return $event->getResponse();
    }

    /**
     * Deletes a ItemVarSet entity
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $entity = $this->get('cart.entity')->find($this->objectType, $id);
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find ItemVarSet entity.');
            }

            $event = new CoreEvent();
            $event->setObjectType($this->objectType)
                ->setEntity($entity)
                ->setRequest($request);

            $this->get('event_dispatcher')
                ->dispatch(CoreEvents::ITEM_VAR_SET_DELETE, $event);

            $request->getSession()->getFlashBag()->add(
                'success',
                'Field Set Successfully Deleted!'
            );
        }

        return $this->redirect($this->generateUrl('cart_admin_item_var_set'));
    }

    /**
     * Mass-Delete Categories
     */
    public function massDeleteAction(Request $request)
    {
        $itemIds = $request->get('item_ids', []);
        $returnData = ['item_ids' => []];

        if ($itemIds) {
            foreach($itemIds as $itemId) {
                $entity = $this->get('cart.entity')->find($this->objectType, $itemId);
                if (!$entity) {
                    $returnData['error'][] = $itemId;
                    continue;
                }

                $event = new CoreEvent();
                $event->setObjectType($this->objectType)
                    ->setEntity($entity)
                    ->setRequest($request);

                $this->get('event_dispatcher')
                    ->dispatch(CoreEvents::ITEM_VAR_SET_DELETE, $event);

                $returnData['item_ids'][] = $itemId;
            }

            $request->getSession()->getFlashBag()->add(
                'success',
                count($returnData['item_ids']) . ' ItemVarSets Successfully Deleted'
            );
        }

        return new JsonResponse($returnData);
    }

    /**
     * Creates a form to delete an entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    protected function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('cart_admin_item_var_set_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', ['label' => 'Delete'])
            ->getForm();
    }
}
