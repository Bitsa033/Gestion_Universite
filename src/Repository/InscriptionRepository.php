<?php

namespace App\Repository;

use App\Entity\Filiere;
use App\Entity\User;
use App\Entity\Inscription;
use App\Entity\Niveau;
use App\Entity\Semestre;
use App\Entity\Ue;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Inscription|null find($id, $lockMode = null, $lockVersion = null)
 * @method Inscription|null findOneBy(array $criteria, array $orderBy = null)
 * @method Inscription[]    findAll()
 * @method Inscription[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InscriptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Inscription::class);
    }

    public function EtudiantPasDeNote(User $user, Filiere $filiere, Niveau $niveau, Semestre $semestre)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        select inscription.id as idI,etudiant.nom as nomE from inscription inner JOIN etudiant
        on etudiant.id=inscription.etudiant_id where inscription.id not in( SELECT inscription_id 
        from notes_etudiant where filiere_id= :filiere AND niveau_id= :niveau AND semestre_id= :semestre ) 
        AND inscription.user_id = :user and filiere_id= :filiere AND niveau_id= :niveau 

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

    public function inscriptionssUser(User $user)
    {
        $a= $this->createQueryBuilder('i') ->andWhere('i.user = :val1')
            ->setParameter('val1', $user)
            ->orderBy('i.id', 'ASC');
        $query=$a->getQuery();

        return $query->execute();
        
    }

    public function inscriptionsUserFiliereNiveau(User $user,$filiere,$niveau)
    {
        $a= $this->createQueryBuilder('i')->andWhere('i.user = :val1')
        ->andWhere('i.filiere = :val2')->andWhere('i.niveau = :val3')
        ->setParameter('val1', $user)->setParameter('val2', $filiere)
        ->setParameter('val3', $niveau)
            ->orderBy('i.id', 'ASC');
        $query=$a->getQuery();

        return $query->execute();
        
    }

    public function inscriptionsFiliereNiveau($filiere,$niveau)
    {
        if ( empty($niveau)) {

            $a= $this->createQueryBuilder('i') 
            ->andWhere('i.filiere = :val1')
            ->setParameter('val1', $filiere)
            ->orderBy('i.id', 'ASC');
            $query=$a->getQuery();
            
            return $query->execute();
            
        }

        elseif (empty($fililere)) {

            $a= $this->createQueryBuilder('i') ->andWhere('i.niveau = :val1')
            ->setParameter('val1', $niveau)
            ->orderBy('i.id', 'ASC');
            $query=$a->getQuery();

            return $query->execute();
            
        }
    
        $a= $this->createQueryBuilder('i') ->andWhere('i.filiere = :val1')
        ->andWhere('i.niveau = :val2')
        ->setParameter('val1', $filiere)->setParameter('val2', $niveau)
        ->orderBy('i.id', 'ASC');
        $query=$a->getQuery();

        return $query->execute();
        
    }

    // public function search($niveau,$filiere)
    // {
    //     $a= $this->createQueryBuilder('i') ->andWhere('i.niveau = :val1')->andWhere('i.filiere = :val2')
    //         ->setParameter('val1', $niveau)->setParameter('val2', $filiere)
    //         ->orderBy('i.id', 'ASC');
    //     $query=$a->getQuery();

    //     return $query->execute();
        
    // }
    
    /** on affiche tous les etudiants qui font parti de la meme filiere 
    * et niveau
    */
    // public function searchStudentsAsFiliereNiveau(Inscription $inscription)
    // {
    //     $a= $this->createQueryBuilder('i') ->andWhere('i.niveau = :val1')->andWhere('i.filiere = :val2')
    //         ->andWhere('i.id != :val3')
    //         ->setParameter('val1', $inscription->getNiveau())->setParameter('val2', $inscription->getFiliere())
    //         ->setParameter('val3', $inscription->getId())
    //         ->orderBy('i.id', 'ASC');
    //     $query=$a->getQuery();

    //     return $query->execute();
        
    // }

    // /**
    //  * @return Inscription[] Returns an array of Inscription objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Inscription
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
