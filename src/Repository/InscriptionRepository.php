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

    public function EtudiantPasDeNote(User $user, Filiere $filiere, Niveau $niveau, Semestre $semestre, Ue $ue)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        select inscription.id as idI,etudiant.nom as nomE from inscription inner JOIN etudiant
        on etudiant.id=inscription.etudiant_id where inscription.id not in( SELECT inscription_id 
        from notes_etudiant where semestre_id= :semestre and ue_id= :ue ) 
        AND inscription.user_id = :user and filiere_id= :filiere AND niveau_id= :niveau 

        ';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'user'=>$user->getId(),
            'filiere'=>$filiere->getId(),
            'niveau'=>$niveau->getId(),
            'semestre'=>$semestre->getId(),
            'ue'=>$ue->getId()
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt;
        
    }

    // public function inscriptionsUserFiliereNiveau(User $user,$filiere,$niveau)
    // {
    //     $a= $this->createQueryBuilder('i')->andWhere('i.user = :val1')
    //     ->andWhere('i.filiere = :val2')->andWhere('i.niveau = :val3')
    //     ->setParameter('val1', $user)->setParameter('val2', $filiere)
    //     ->setParameter('val3', $niveau)
    //         ->orderBy('i.id', 'ASC');
    //     $query=$a->getQuery();

    //     return $query->execute();
        
    // }

    // SELECT inscription.id as idI,etudiant.nom as nomE,
    // ue.id as idI,matiere.nom as nomM
    // from inscription inner join etudiant 
    // on etudiant.id=inscription.etudiant_id 
    // inner join ue on ue.filiere_id = inscription.filiere_id
    // inner JOIN matiere on matiere.id=ue.matiere_id
    // inner join semestre on semestre.id=ue.semestre_id
    // WHERE ue.semestre_id=1 and ue.id not in (SELECT n.ue_id
    // FROM notes_etudiant n )

    public function etudiantsMatieres(Filiere $filiere=null,Niveau $niveau=null,Semestre $semestre=null)
    {
        if ($filiere!=null && $niveau!=null && $semestre!=null) {
            # code...
            $conn = new \PDO('mysql:host=localhost;dbname=gnu','root','');
            $sql = '
            
            SELECT inscription.id as idI,etudiant.nom as nomE, ue.id as idC,
            matiere.nom as nomM from inscription inner join etudiant on etudiant.id
            =inscription.etudiant_id inner join ue on ue.filiere_id = inscription.filiere_id
            inner JOIN matiere on matiere.id=ue.matiere_id inner join semestre on semestre.id
            =ue.semestre_id WHERE ue.semestre_id=:semestre and ue.filiere_id=:filiere and 
            ue.niveau_id=:niveau and ue.id not in (SELECT n.ue_id
            FROM notes_etudiant n )
            ';
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'filiere'=>$filiere->getId(),
                'niveau'=>$niveau->getId(),
                'semestre'=>$semestre->getId()
            ]);
            $tab=$stmt->fetchAll();
            
            // returns an array of arrays (i.e. a raw data set)
            return $tab;
        }
        else {
            
        }

        
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
