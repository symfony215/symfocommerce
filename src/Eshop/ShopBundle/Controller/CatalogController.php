<?php

namespace Eshop\ShopBundle\Controller;

use Doctrine\ORM\EntityManager;
use Eshop\ShopBundle\Entity\Category;
use Eshop\ShopBundle\Entity\Manufacturer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class CatalogController extends Controller
{
    /**
     * Lists all Category entities.
     *
     * @Route("/", name="index_main")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categoryRepository = $em->getRepository('ShopBundle:Category');
        $newsRepository = $em->getRepository('ShopBundle:News');
        $slideRepository = $em->getRepository('ShopBundle:Slide');

        $categories = $categoryRepository->findAll();
        //sorted by order number
        $slides = $slideRepository->findBy(array(), array('slideOrder' => 'ASC'));
        $lastNews = $newsRepository->getLastNews();

        return array(
            'categories' => $categories,
            'news' => $lastNews,
            'slides' => $slides
        );
    }

    /**
     * @Template()
     */
    public function categoriesMenuAction()
    {
        $em = $this->getDoctrine()->getManager();
        $categoryRepository = $em->getRepository('ShopBundle:Category');

        $categories = $categoryRepository->findAll();

        return array(
            'categories' => $categories
        );
    }

    /**
     * @Template()
     */
    public function manufacturersMenuAction()
    {
        $em = $this->getDoctrine()->getManager();
        $manufacturerRepository = $em->getRepository('ShopBundle:Manufacturer');

        $manufacturers = $manufacturerRepository->findAll();

        return array(
            'manufacturers' => $manufacturers
        );
    }

    /**
     * @Route("/category/{slug}", name="category")
     * @Method("GET")
     * @Template()
     */
    public function categoryAction(Request $request, $slug = '')
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');
        $categoryRepository = $em->getRepository('ShopBundle:Category');
        $productRepository = $em->getRepository('ShopBundle:Product');

        /**
         * @var Category $requiredCategory
         */
        if ($slug == '') {
            //get first category id
            $requiredCategory = $categoryRepository->getFirstCategoryId();
            $requiredCategory = $requiredCategory['id'];
        } else {
            $requiredCategory = $categoryRepository->findBySlug($slug);
            $requiredCategory = $requiredCategory->getId();
        }

        $productsQuery = $productRepository->findByCategoryForPaginator($requiredCategory);
        $limit = $this->getParameter('category_products_pagination_count');
        $products = $paginator->paginate(
            $productsQuery,
            $this->get('request')->query->getInt('page', 1),
            $limit
        );

        $category = $categoryRepository->find($requiredCategory);

        return array(
            'category' => $category,
            'products' => $products,
            'sortedby' => $this->getSortingParamName($request)
        );
    }

    /**
     * @Route("/manufacturer/{slug}", name="manufacturer")
     * @Method("GET")
     * @Template()
     */
    public function manufacturerAction(Request $request, $slug = '')
    {
        $em = $this->getDoctrine()->getManager();
        $paginator = $this->get('knp_paginator');
        $manufacturerRepository = $em->getRepository('ShopBundle:Manufacturer');
        $productRepository = $em->getRepository('ShopBundle:Product');

        /**
         * @var Manufacturer $requiredManufacturer
         */
        if ($slug == '') {
            //get first category id
            $requiredManufacturer = $manufacturerRepository->getFirstManufacturerId();
            $requiredManufacturer = $requiredManufacturer['id'];
        } else {
            $requiredManufacturer = $manufacturerRepository->findBySlug($slug);
            $requiredManufacturer = $requiredManufacturer->getId();
        }

        $productsQuery = $productRepository->findByManufacturerForPaginator($requiredManufacturer);
        $limit = $this->getParameter('category_products_pagination_count');
        $products = $paginator->paginate(
            $productsQuery,
            $this->get('request')->query->getInt('page', 1),
            $limit
        );

        $manufacturer = $manufacturerRepository->find($requiredManufacturer);

        return array(
            'manufacturer' => $manufacturer,
            'products' => $products,
            'sortedby' => $this->getSortingParamName($request)
        );
    }

    /**
     * @Route("/product/{slug}", name="show_product")
     * @Method("GET")
     * @Template()
     */
    public function showProductAction($slug = '')
    {
        $em = $this->getDoctrine()->getManager();
        $productRepository = $em->getRepository('ShopBundle:Product');

        if ($slug == '') {
            return $this->redirectToRoute('index_main');
        } else {
            $product = $productRepository->findBySlug($slug);
        }

        return array(
            'product' => $product
        );
    }

    /**
     * Lists news entities.
     *
     * @Route("/news", name="news")
     * @Method("GET")
     * @Template()
     */
    public function newsAction()
    {
        $em = $this->getDoctrine()->getManager();

        $paginator = $this->get('knp_paginator');

        $dql = "SELECT a FROM ShopBundle:News a ORDER BY a.date DESC";
        $query = $em->createQuery($dql);
        $limit = $this->getParameter('products_pagination_count');

        $news = $paginator->paginate(
            $query,
            $this->get('request')->query->getInt('page', 1),
            $limit
        );

        return array(
            'news' => $news,
        );
    }

    /**
     * return sorting name param form request.
     */
    private function getSortingParamName(Request $request)
    {
        $sortedBy = '';
        $sortParam = $request->get('sort');

        switch ($sortParam) {
            case 'p.name':
                $sortedBy = 'manufacturer.sort.name';
                break;
            case 'p.price':
                $sortedBy = 'manufacturer.sort.price';
                break;
            default:
                $sortedBy = 'manufacturer.sort.default';
                break;
        }
        return $sortedBy;
    }

    /**
     * Lists news entities.
     *
     * @Route("/news", name="news")
     * @Method("GET")
     * @Template()
     */
    public function staticPagesMenuAction()
    {
        /**
         * @var EntityManager $em
         */
        $em = $this->getDoctrine()->getManager();

        $headers = $em->getRepository('ShopBundle:StaticPage')->getHeaders();

        return array(
            'headers' => $headers
        );
    }
}