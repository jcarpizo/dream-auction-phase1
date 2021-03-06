<?php

namespace AuctionBundle\Controller;

use AuctionBundle\Entity\Image;
use AuctionBundle\Entity\Item;
use AuctionBundle\Form\ItemType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Item controller.
 *
 * @Route("item")
 */
class ItemController extends Controller
{
    /**
     * Lists all item entities.
     *
     * @Route("/", name="item_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $items = $em->getRepository('AuctionBundle:Item')->findAll();
        return $this->render('AuctionBundle:Item:index.html.twig', array(
            'items' => $items,
        ));
    }

    /**
     * Creates a new item entity.
     *
     * @Route("/new", name="item_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $item = new Item();
        $form = $this->createForm(ItemType::class, $item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            foreach ($item->getPictures() as $picture) {
                $fileName = md5(uniqid()) . '.' . $picture->guessExtension();
                $image = new Image();
                $picture->move(
                    $this->getParameter('items_directory'),
                    $fileName
                );
                $image->setName($picture->getClientOriginalName())
                    ->setFile($fileName)
                    ->setItem($item)
                    ->setCreatedAt(new \DateTime());
                $item->getImages()->add($image);
            }
            $item->setCreatedAt(new \DateTime())->setEndedAt(new \DateTime());

            $em->persist($item);
            $em->flush();

            return $this->redirectToRoute('item_show', array('id' => $item->getId()));
        }
        return $this->render('AuctionBundle:Item:new.html.twig', array(
            'item' => $item,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a item entity.
     *
     * @Route("/{id}", name="item_show")
     * @Method("GET")
     */
    public function showAction(Item $item)
    {
        $deleteForm = $this->createDeleteForm($item);
        return $this->render('AuctionBundle:Item:show.html.twig', array(
            'item' => $item,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing item entity.
     *
     * @Route("/{id}/edit", name="item_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Item $item)
    {
        $deleteForm = $this->createDeleteForm($item);
        $editForm = $this->createForm(ItemType::class, $item);
        $editForm->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {

            foreach ($item->getPictures() as $picture) {
                $fileName = md5(uniqid()) . '.' . $picture->guessExtension();
                $image = new Image();
                $picture->move(
                    $this->getParameter('items_directory'),
                    $fileName
                );
                $image->setName($picture->getClientOriginalName())
                    ->setFile($fileName)
                    ->setItem($item)
                    ->setCreatedAt(new \DateTime());
                $item->getImages()->add($image);
            }


            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('item_edit', array('id' => $item->getId()));
        }


        return $this->render('AuctionBundle:Item:edit.html.twig', array(
            'item' => $item,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a item entity.
     *
     * @Route("/{id}", name="item_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Item $item)
    {
        $form = $this->createDeleteForm($item);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($item);
            $em->flush();
        }

        return $this->redirectToRoute('item_index');
    }

    /**
     * Creates a form to delete a item entity.
     *
     * @param Item $item The item entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Item $item)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('item_delete', array('id' => $item->getId())))
            ->setMethod('DELETE')
            ->getForm();
    }
}
