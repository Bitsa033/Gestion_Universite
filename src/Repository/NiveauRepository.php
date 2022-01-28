<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Niveau;
use App\Entity\Etudiant;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Niveau|null find($id, $lockMode = null, $lockVersion = null)
 * @method Niveau|null findOneBy(array $criteria, array $orderBy = null)
 * @method Niveau[]    findAll()
 * @method Niveau[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NiveauRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Niveau::class);
    }

    public function niveauxUser(User $user)
    {
        $a= $this->createQueryBuilder('n') ->andWhere('n.user = :val1')
            ->setParameter('val1', $user)
            ->orderBy('n.id', 'ASC');
        $query=$a->getQuery();

        return $query->execute();
        
    }

    public function niveauxUserPourEtudiantsInscris(User $user, Etudiant $etudiant)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT * FROM niveau,etudiant,inscription where
        niveau.user_id= :user and inscription.etudiant_id != :etudiant
            ';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'user'=>$user->getId(),
            'etudiant'=>$etudiant->getId()
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt;
        
    }

    // /**
    //  * @return Niveau[] Returns an array of Niveau objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('n.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Niveau
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
