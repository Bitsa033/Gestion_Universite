<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Niveau;
use App\Entity\Filiere;
use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Entity\Semestre;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method NotesEtudiant|null find($id, $lockMode = null, $lockVersion = null)
 * @method NotesEtudiant|null findOneBy(array $criteria, array $orderBy = null)
 * @method NotesEtudiant[]    findAll()
 * @method NotesEtudiant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class NotesEtudiantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NotesEtudiant::class);
    }

    public function notesEtudiantUser(User $user,Filiere $filiere, Niveau $niveau, Semestre $semestre,Inscription $inscription)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        
        select notes_etudiant.id as idN, etudiant.nom as nomE, matiere.nom as nomM, 
        moyenne,notes_etudiant.created_at as dateN,semestre.nom as nomSe from 
        notes_etudiant inner join inscription on
        inscription.id = notes_etudiant.inscription_id inner join etudiant 
        on etudiant.id= inscription.etudiant_id inner join 
        ue on ue.id= notes_etudiant.ue_id  inner join matiere on matiere.id
        = ue.matiere_id inner join semestre on semestre.id=notes_etudiant.semestre_id
        WHERE notes_etudiant.user_id = :user AND inscription.filiere_id = :filiere AND  
        inscription.niveau_id = :niveau AND semestre.id= :semestre and notes_etudiant.inscription_id = :inscription
        order by etudiant_id 
        

        ';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'user'=>$user->getId(),
            'filiere'=>$filiere->getId(),
            'niveau'=>$niveau->getId(),
            'semestre'=>$semestre->getId(),
            'inscription'=>$inscription->getId()
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt;
        
    }

    // /**
    //  * @return NotesEtudiant[] Returns an array of NotesEtudiant objects
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
    public function findOneBySomeField($value): ?NotesEtudiant
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
