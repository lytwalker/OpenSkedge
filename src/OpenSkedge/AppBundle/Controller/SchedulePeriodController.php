<?php

namespace OpenSkedge\AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use OpenSkedge\AppBundle\Entity\SchedulePeriod;
use OpenSkedge\AppBundle\Form\SchedulePeriodType;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

/**
 * SchedulePeriod controller.
 *
 */
class SchedulePeriodController extends Controller
{
    /**
     * Lists all SchedulePeriod entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $schedulePeriods = $em->getRepository('OpenSkedgeBundle:SchedulePeriod')->findBy(array(), array(
            'endTime' => 'DESC'
        ));

        $page = $this->container->get('request')->query->get('page', 1);

        $adapter = new ArrayAdapter($schedulePeriods);
        $paginator = new Pagerfanta($adapter);
        $paginator->setMaxPerPage(15);
        $paginator->setCurrentPage($page);

        $entities = $paginator->getCurrentPageResults();

        return $this->render('OpenSkedgeBundle:SchedulePeriod:index.html.twig', array(
            'entities'  => $entities,
            'paginator' => $paginator,
        ));
    }

    /**
     * Finds and displays a SchedulePeriod entity.
     *
     */
    public function viewAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OpenSkedgeBundle:SchedulePeriod')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SchedulePeriod entity.');
        }

        $positions = $em->getRepository('OpenSkedgeBundle:Position')->findAll();

        $position_page = $this->container->get('request')->query->get('position_page', 1);
        $position_adapter = new ArrayAdapter($positions);
        $position_paginator = new Pagerfanta($position_adapter);
        $position_paginator->setMaxPerPage(15);
        $position_paginator->setCurrentPage($position_page);

        $positions = $position_paginator->getCurrentPageResults();

        $availSchedules = $em->getRepository('OpenSkedgeBundle:AvailabilitySchedule')->findBy(array(
            'schedulePeriod' => $id
        ));

        $user_page = $this->container->get('request')->query->get('user_page', 1);
        $user_adapter = new ArrayAdapter($availSchedules);
        $user_paginator = new Pagerfanta($user_adapter);
        $user_paginator->setMaxPerPage(15);
        $user_paginator->setCurrentPage($user_page);

        $avails = $user_paginator->getCurrentPageResults();

        $deleteForm = $this->createDeleteForm($id);

        return $this->render('OpenSkedgeBundle:SchedulePeriod:view.html.twig', array(
            'entity'      => $entity,
            'positions'   => $positions,
            'avails'      => $avails,
            'position_paginator' => $position_paginator,
            'user_paginator'   => $user_paginator,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a new SchedulePeriod entity.
     *
     */
    public function newAction(Request $request)
    {
        $entity  = new SchedulePeriod();
        $form = $this->createForm(new SchedulePeriodType(), $entity);

        if ($request->getMethod() == 'POST') {
            $form->bind($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('schedule_period_view', array('id' => $entity->getId())));
            }
        }

        return $this->render('OpenSkedgeBundle:SchedulePeriod:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Edits an existing SchedulePeriod entity.
     *
     */
    public function editAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('OpenSkedgeBundle:SchedulePeriod')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find SchedulePeriod entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new SchedulePeriodType(), $entity);
        if ($request->getMethod() == 'POST') {
            $editForm->bind($request);

            if ($editForm->isValid()) {
                $em->persist($entity);
                $em->flush();

                return $this->redirect($this->generateUrl('schedule_period_view', array('id' => $id)));
            }
        }

        return $this->render('OpenSkedgeBundle:SchedulePeriod:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a SchedulePeriod entity.
     *
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('OpenSkedgeBundle:SchedulePeriod')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find SchedulePeriod entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('schedule_periods'));
    }

    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}