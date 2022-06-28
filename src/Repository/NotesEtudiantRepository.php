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

    public function notesEtudiant(User $user)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT semestre.nom as semestre, etudiant.nom as etudiant, matiere.nom as matiere, moyenne from notes_etudiant
        inner join inscription on inscription.id=notes_etudiant.inscription_id inner join etudiant
        on etudiant.id=inscription.etudiant_id inner join ue on ue.id=notes_etudiant.ue_id inner JOIN
        matiere on matiere.id=ue.matiere_id inner join semestre on semestre.id= notes_etudiant.semestre_id
        order by ue_id
        ';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'user'=>$user->getId(),
        ]);

        // returns an array of arrays (i.e. a raw data set)
        return $stmt;
        
    }

    public function notesEtudiant2(User $user)
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = '
        SELECT distinct semestre.nom as semestre,moyenne, matiere.nom as matiere
        from notes_etudiant INNER JOIN inscription on inscription.id = notes_etudiant.inscription_id 
        inner join etudiant on etudiant.id = inscription.etudiant_id INNER JOIN semestre on semestre.id = 
        notes_etudiant.semestre_id INNER JOIN ue on ue.id = notes_etudiant.ue_id INNER JOIN matiere ON matiere.id
        = ue.matiere_id WHERE inscription.filiere_id = 4 and inscription.niveau_id = 1 and inscription.id=1
        ';
        $stmt = $conn->prepare($sql);
        $stmt->executeQuery([
            'user'=>$user->getId(),
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
