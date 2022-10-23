<?php

namespace App\Repository;

use App\Entity\Attribute;
use App\Entity\Fact;
use App\Entity\Security;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Fact>
 *
 * @method Fact|null find($id, $lockMode = null, $lockVersion = null)
 * @method Fact|null findOneBy(array $criteria, array $orderBy = null)
 * @method Fact[]    findAll()
 * @method Fact[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FactRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Fact::class);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function add(Fact $entity, bool $flush = true): void
    {
        $this->_em->persist($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function remove(Fact $entity, bool $flush = true): void
    {
        $this->_em->remove($entity);
        if ($flush) {
            $this->_em->flush();
        }
    }

    // /**
    //  * @return Fact[] Returns an array of Fact objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('f.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Fact
    {
        return $this->createQueryBuilder('f')
            ->andWhere('f.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */

    public function getFact(Attribute $attributeId, Security $securityId): ?Fact
    {
        return $this->createQueryBuilder('f')
            ->andwhere('f.attribute = :attrId')
            ->andwhere('f.security = :secId')
            ->setParameter('attrId', $attributeId)
            ->setParameter('secId', $securityId)
            ->getQuery()
            ->getOneOrNullResult();
    }

}
