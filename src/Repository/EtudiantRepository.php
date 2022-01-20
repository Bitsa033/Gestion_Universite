<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Etudiant;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Etudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Etudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Etudiant[]    findAll()
 * @method Etudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Etudiant::class);
    }

    public function etudiantsUser(User $user)
    {
        $a= $this->createQueryBuilder('e') ->andWhere('e.user = :val1')
            ->setParameter('val1', $user)
            ->orderBy('e.id', 'ASC');
        $query=$a->getQuery();

        return $query->execute();
        
    }

    // public function searchAllStudentsNotRegister(InscriptionRepository $inscriptionRepository)
    // {
    //     $a= $this->createQueryBuilder('e') ->andWhere('e.id != :val1')
    //         ->setParameter('val1', $inscriptionRepository->find('id'))
    //         ->orderBy('e.id', 'ASC');
    //     $query=$a->getQuery();

    //     return $query->execute();
        
    // }

    // public function search($value)
    // {
    //     $a= $this->createQueryBuilder('e') ->andWhere('e.nom = :val')
    //         ->setParameter('val', $value)
    //         ->orderBy('e.id', 'ASC');
    //     $query=$a->getQuery();

    //     return $query->execute();
        
    // }

    // /**
    //  * @return Etudiant[] Returns an array of Etudiant objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Etudiant
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
