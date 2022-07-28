<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Niveau;
use App\Entity\Filiere;
use App\Entity\Matiere;
use App\Entity\Semestre;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Matiere|null find($id, $lockMode = null, $lockVersion = null)
 * @method Matiere|null findOneBy(array $criteria, array $orderBy = null)
 * @method Matiere[]    findAll()
 * @method Matiere[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MatiereRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Matiere::class);
    }

    // public function uesFiliereNiveau($filiere,$niveau)
    // {
    //     $a= $this->createQueryBuilder('u') ->andWhere('u.filiere = :val1')->andWhere('u.niveau = :val2')
    //         ->setParameter('val1', $filiere)->setParameter('val2', $niveau)
    //         ->orderBy('u.id', 'ASC');
    //     $query=$a->getQuery();

    //     return $query->execute();
        
    // }

    public function matiereUserPasEncoreUe(User $user,Filiere $filiere, Niveau $niveau, Semestre $semestre)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        select * from matiere where id not in(
            SELECT matiere_id from ue where filiere_id= :filiere AND
            niveau_id= :niveau AND semestre_id= :semestre
            )
        AND matiere.user_id= :user
            ';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'user'=>$user->getId(),
            'filiere'=>$filiere->getId(),
            'niveau'=>$niveau->getId(),
            'semestre'=>$semestre->getId()
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt;
        
    }

    // /**
    //  * @return Matiere[] Returns an array of Matiere objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('m.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Matiere
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
