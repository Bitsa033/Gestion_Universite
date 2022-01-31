<?php

namespace App\Repository;

use App\Entity\Ue;
use App\Entity\User;
use App\Entity\Niveau;
use App\Entity\Filiere;
use App\Entity\Inscription;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Ue|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ue|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ue[]    findAll()
 * @method Ue[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ue::class);
    }

    public function uesUser(User $user)
    {
        $a= $this->createQueryBuilder('u') ->andWhere('u.user = :val1')
            ->setParameter('val1', $user)
            ->orderBy('u.id', 'ASC');
        $query=$a->getQuery();

        return $query->execute();
        
    }

    public function uesFiliereNiveau($filiere,$niveau)
    {
        $a= $this->createQueryBuilder('u') ->andWhere('u.filiere = :val1')->andWhere('u.niveau = :val2')
            ->setParameter('val1', $filiere)->setParameter('val2', $niveau)
            ->orderBy('u.id', 'ASC');
        $query=$a->getQuery();

        return $query->execute();
        
    }

    public function uePasEncoreNoterPourInscription(User $user,Filiere $filiere, Niveau $niveau, Inscription $inscription)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        select distinct matiere.nom, ue.id as id from matiere inner join ue on ue.matiere_id =
        matiere.id  where matiere.id in( 
        SELECT matiere_id from ue where filiere_id= :filiere 
        AND niveau_id= :niveau AND ue.id not in ( SELECT notes_etudiant.ue_id
        FROM notes_etudiant WHERE notes_etudiant.inscription_id = (
        SELECT inscription.id FROM inscription WHERE inscription.id= :inscription
        ) ) ) AND matiere.user_id= :user
            ';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'user'=>$user->getId(),
            'filiere'=>$filiere->getId(),
            'niveau'=>$niveau->getId(),
            'inscription'=>$inscription->getId()
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt;
        
    }

    // /**
    //  * @return Ue[] Returns an array of Ue objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Ue
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
