<?php

namespace Eshop\ShopBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends EntityRepository
{
    //query for pagination without "getQuery()"
    public function findByCategoryForPaginator($categoryId){
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g')
            ->from('ShopBundle:Product', 'g')
            ->innerJoin('g.category', 'ca')
            ->where('ca.id = :categoryid')
            ->setParameter('categoryid', $categoryId
            );
    }

    //query for pagination without "getQuery()"
    public function findByManufacturerForPaginator($manufacturerId){
        return $this->getEntityManager()
            ->createQueryBuilder()
            ->select('g')
            ->from('ShopBundle:Product', 'g')
            ->innerJoin('g.manufacturer', 'ma')
            ->where('ma.id = :manufacturerid')
            ->setParameter('manufacturerid', $manufacturerId
            );
    }
}