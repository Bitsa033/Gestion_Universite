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
use Exception;
use PDO;

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

    public $server='localhost';
    public $username='root';
    public $database='gnu';

    public function connection_to_databse(){

        try {
            $pdo=new \PDO('mysql:host=localhost;dbname=gnu','root','');
        } catch (Exception $th) {
            die( $th->getMessage());
        }

        return $pdo;
    }

    public function notesEtudiant(User $user)
    {
        $conn = $this->connection_to_databse();
        $sql = '
        SELECT inscription_id as idi, etudiant.nom as etudiant, moyenne,
        ue.id as idu, matiere.nom as matiere from notes_etudiant n
        INNER join inscription i on i.id= n.inscription_id
        inner join etudiant on etudiant.id=i.etudiant_id
        inner join ue on ue.id=n.ue_id inner join matiere on 
        matiere.id=ue.matiere_id WHERE i.id in(1) and n.user_id= :user
        ';
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'user'=>$user->getId(),
        ]);

        $resultat=$stmt->fetchAll();

        // returns an array of arrays (i.e. a raw data set)
        return $resultat;
        
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
