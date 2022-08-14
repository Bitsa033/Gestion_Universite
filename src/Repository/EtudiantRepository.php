<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Etudiant;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PDO;

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

    public function etudiantsListe(User $user)
    {
        $conn =new \PDO('mysql:host=localhost;dbname=gnu','root','');
        $sql = '
        SELECT etudiant.id as id , etudiant.nom as nom ,
        etudiant.prenom as prenom , etudiant.sexe as sexe , 
        inscription.filiere_id as filiere, filiere.nom as filiere ,
        inscription.niveau_id as niveau, niveau.nom as niveau FROM 
        etudiant LEFT JOIN inscription ON inscription.etudiant_id
        = etudiant.id LEFT join niveau on niveau.id=inscription.niveau_id 
        LEFT JOIN filiere on filiere.id=inscription.filiere_id 
        where etudiant.user_id= :user order by etudiant.id desc
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'user'=>$user->getId()
        ]);
        $tab=$stmt->fetchAll();
        // returns an array of arrays (i.e. a raw data set)
        return $tab;
        
    }

    public function etudiantsPasInscris(User $user)
    {
        $conn = new \PDO('mysql:host=localhost;dbname=gnu','root','');
        $sql = '
        SELECT * FROM etudiant where etudiant.user_id= :user and 
        etudiant.id not in (SELECT etudiant_id from inscription)
            ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'user'=>$user->getId()
        ]);
        $tab=$stmt->fetchAll();
        // returns an array of arrays (i.e. a raw data set)
        return $tab;
        
    }

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
