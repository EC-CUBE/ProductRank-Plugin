<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) LOCKON CO.,LTD. All Rights Reserved.
 *
 * http://www.lockon.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\ProductRank;

use Eccube\Application;
use Eccube\Entity\Master\ProductListOrderBy;
use Eccube\Plugin\AbstractPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Eccube\Repository\Master\ProductListOrderByRepository;
use Doctrine\ORM\NoResultException;

class PluginManager extends AbstractPluginManager
{
    /**
     * Execute uninstall
     *
     * @param array $meta
     * @param ContainerInterface $container
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function uninstall(array $meta, ContainerInterface $container)
    {
        $this->removeProductListOrderBy($container);
    }

    /**
     * Enable
     *
     * @param array $meta
     * @param ContainerInterface $container
     */
    public function enable(array $meta, ContainerInterface $container)
    {
        $this->addProductListOrderBy($container);
    }

    /**
     * Execute disable
     *
     * @param array $meta
     * @param ContainerInterface $container
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function disable(array $meta, ContainerInterface $container)
    {
        $this->removeProductListOrderBy($container);
    }

    /**
     * Add product list order by
     *
     * @param ContainerInterface $container
     */
    private function addProductListOrderBy(ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        /** @var ProductListOrderByRepository $productListOrderByRepository */
        $productListOrderByRepository = $entityManager->getRepository('Eccube\Entity\Master\ProductListOrderBy');
        $ProductListOrderByMax = $productListOrderByRepository->findOneBy([], ['sort_no' => 'DESC']);

        /** @var \Eccube\Entity\Master\ProductListOrderBy $plob */
        $ProductListOrderBy = new ProductListOrderBy();
        $ProductListOrderBy->setId($container->getParameter('product_rank.product_list_order_id'));
        $ProductListOrderBy->setName($container->getParameter('product_rank.product_list_order_name'));
        $ProductListOrderBy->setSortNo($ProductListOrderByMax->getSortNo() + 1);

        $entityManager->persist($ProductListOrderBy);
        $entityManager->flush();
    }

    /**
     * Remove product list order by
     *
     * @param ContainerInterface $container
     *
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    private function removeProductListOrderBy(ContainerInterface $container)
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $container->get('doctrine.orm.entity_manager');

        /** @var ProductListOrderByRepository $repos */
        $repos = $entityManager->getRepository('Eccube\Entity\Master\ProductListOrderBy');
        try {
            $ProductListOrderBy = $repos->createQueryBuilder('plob')
                ->where('plob.id = :id')
                ->getQuery()
                ->setParameters(['id' => $container->getParameter('product_rank.product_list_order_id')])
                ->getSingleResult();

            $entityManager->remove($ProductListOrderBy);
            $entityManager->flush();
        } catch (NoResultException $e) {
            // silence
        }
    }
}
