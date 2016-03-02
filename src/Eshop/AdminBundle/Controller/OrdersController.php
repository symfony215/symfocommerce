<?php

namespace Eshop\AdminBundle\Controller;

use Eshop\ShopBundle\Entity\OrderProduct;
use Eshop\ShopBundle\Entity\Product;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Eshop\ShopBundle\Entity\Orders;
use Eshop\ShopBundle\Form\Type\OrdersType;

/**
 * Orders controller.
 *
 * @Route("/admin/orders")
 */
class OrdersController extends Controller
{

    /**
     * Lists all Orders entities.
     *
     * @Route("/", name="admin_orders")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');

        $dql = "SELECT a FROM ShopBundle:Orders a ORDER BY a.date DESC";
        $query = $em->createQuery($dql);
        $limit = $this->getParameter('admin_manufacturers_pagination_count');

        $orders = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );

        return array(
            'entities' => $orders,
        );
    }

    /**
     * Finds and displays a Orders entity.
     *
     * @Route("/{id}", name="admin_order_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        /**
         * @var Orders $order
         */
        $order = $em->getRepository('ShopBundle:Orders')->find($id);

        if (!$order) {
            throw $this->createNotFoundException('Unable to find Order entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $orderProducts = $order->getOrderProducts();
        $productsArray = array();
        $totalSum = 0;

        foreach($orderProducts as $orderProduct){
            $productPosition = array();
            /**
             * @var Product $product
             * @var OrderProduct $orderProduct
             */
            $product = $orderProduct->getProduct();
            $price = $product->getPrice();
            $quantity = $orderProduct->getQuantity();
            $sum = $price * $quantity;

            $productPosition['product'] = $product;
            $productPosition['quantity'] = $quantity;
            $productPosition['price'] = $price;
            $productPosition['sum'] = $sum;
            $totalSum += $sum;

            $productsArray[] = $productPosition;
        }

        return array(
            'entity' => $order,
            'delete_form' => $deleteForm->createView(),
            'totalsum' => $totalSum,
            'products' => $productsArray
        );
    }


    /**
     * Creates a form to delete a Orders entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_order_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array(
                'label' => 'Delete',
                'attr' => array('onclick' => 'return confirm("Are you sure?")')
            ))
            ->getForm()
            ;
    }

    /**
     * Deletes a Orders entity.
     *
     * @Route("/{id}", name="admin_order_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('ShopBundle:Orders')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Order entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_orders'));
    }
}