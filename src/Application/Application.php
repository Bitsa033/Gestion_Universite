<?php 
namespace App\Application;

use App\Entity\Etudiant;
use App\Entity\Filiere;
use App\Entity\Inscription;
use App\Entity\Matiere;
use App\Entity\Niveau;
use App\Entity\NotesEtudiant;
use App\Entity\Semestre;
use App\Entity\Ue;
use App\Entity\User;
use App\Repository\EtudiantRepository;
use App\Repository\FiliereRepository;
use App\Repository\InscriptionRepository;
use App\Repository\MatiereRepository;
use App\Repository\NiveauRepository;
use App\Repository\NotesEtudiantRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use App\Repository\UserRepository;
use Doctrine\Persistence\ManagerRegistry;

class Application
{
    public $table_user;
    public $table_filiere;
    public $table_niveau;
    public $table_semestre;
    public $table_matiere;
    public $table_ue;
    public $table_etudiant;
    public $table_inscription;
    public $table_note;

    public $repo_user;
    public $repo_filiere;
    public $repo_niveau;
    public $repo_semestre;
    public $repo_matiere;
    public $repo_ue;
    public $repo_etudiant;
    public $repo_inscription;
    public $repo_note;

    public $db;

    function __construct(
                        UserRepository $userRepository,
                        FiliereRepository $filiereRepository,
                        NiveauRepository $niveauRepository,
                        SemestreRepository $semestreRepository,
                        MatiereRepository $matiereRepository,
                        UeRepository $ueRepository,
                        EtudiantRepository $etudiantRepository,
                        InscriptionRepository $inscriptionRepository,
                        NotesEtudiantRepository $notesEtudiantRepository,
                        ManagerRegistry $managerRegistry
    )
    {
        $this->repo_user=$userRepository;
        $this->repo_filiere=$filiereRepository;
        $this->repo_niveau=$niveauRepository;
        $this->repo_semestre=$semestreRepository;
        $this->repo_matiere=$matiereRepository;
        $this->repo_ue=$ueRepository;
        $this->repo_etudiant=$etudiantRepository;
        $this->repo_inscription=$inscriptionRepository;
        $this->repo_note=$notesEtudiantRepository;

        $this->table_user= new User;
        $this->table_filiere=Filiere::class;
        $this->table_niveau= Niveau::class;
        $this->table_semestre= Semestre::class;
        $this->table_matiere= Matiere::class;
        $this->table_ue= Ue::class;
        $this->table_etudiant= new Etudiant;
        $this->table_inscription= Inscription::class;
        $this->table_note= NotesEtudiant::class;

        $this->db=$managerRegistry->getManager();

    }

    public function multiple_row($array)
    {
        foreach ($array as $key => $value) {
            $k[] = $key;
            $v[] = $value;
        }
        $k = implode(",", $k);
        $v = implode(",", $v);

        return $array;
    }

    public function new_filiere($data, User $user)
    {
        $this->multiple_row($data);
        
        $filiere= new $this->table_filiere;
        $filiere->setUser($user);
        $filiere->setNom(ucfirst($data['nom']));
        $filiere->setSigle(strtoupper($data['sigle']));
        $filiere->setCreatedAt(new \datetime);
        $this->db->persist($filiere);
        $this->db->flush();
    }

    public function new_classe($data, User $user)
    {
        $this->multiple_row($data);
        
        $classe= new $this->table_niveau;
        $classe->setUser($user);
        $classe->setNom(ucfirst($data['nom']));
        $classe->setCreatedAt(new \datetime);
        $this->db->persist($classe);
        $this->db->flush();
    }

    public function new_semestre($data, User $user)
    {
        $this->multiple_row($data);
        
        $semestre= new $this->table_semestre;
        $semestre->setUser($user);
        $semestre->setNom(ucfirst($data['nom']));
        $semestre->setCreatedAt(new \datetime);
        $this->db->persist($semestre);
        $this->db->flush();
    }



}

class EcritureMatiere{

    function Enregistrer($tableauValaleurs, User $utilisateur, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        
        $object = new Matiere;
        $object->setUser($utilisateur);
        $object->setNom(ucfirst($tableauValaleurs['nom']));
        $object->setCreatedAt(new \datetime);

        $ue=new Ue;
        $ue->setFiliere($tableauValaleurs['filiere']);
        $ue->setNiveau($tableauValaleurs['niveau']);
        $ue->setSemestre($tableauValaleurs['semestre']);
        $ue->setUser($utilisateur);
        $ue->setMatiere($object);
        $ue->setNote($tableauValaleurs['note']);
        $ue->setCredit($tableauValaleurs['note']/20);
        $ue->setCode($tableauValaleurs['code']);
        $ue->setCreatedAt(new \DateTime);
        $manager = $enregistreur->getManager();
        $manager->persist($ue);
        $manager->flush();
    }

    function MettreAJour($tableauValaleurs,Matiere $object, ManagerRegistry $enregistreur)
    {
        foreach ($tableauValaleurs as $key => $value) {
            $k[] = $key;
            $v[] = $value;
        }
        $k = implode(",", $k);
        $v = implode(",", $v);
        
        $object->setNom(ucfirst($tableauValaleurs['nom']));
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->flush();
    }
    
}

class EcritureCours{

    function Enregistrer(Matiere $matiere,Niveau $classe,Filiere $filiere,Semestre $semestre, User $utilisateur, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        
        $object = new Ue;
        $object->setUser($utilisateur);
        $object->setMatiere($matiere);
        $object->setNiveau($classe);
        $object->setFiliere($filiere);
        $object->setSemestre($semestre);
        $object->setCredit(4);
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->persist($object);
        $manager->flush();
    }

    function MettreAJour(Ue $object,Matiere $matiere,Niveau $classe,Filiere $filiere,Semestre $semestre, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        $object->setMatiere($matiere);
        $object->setNiveau($classe);
        $object->setFiliere($filiere);
        $object->setSemestre($semestre);
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->flush();
    }
    
}

class EcritureEtudiant{

    function persistance(ManagerRegistry $managerRegistry ,$Entity){
       
        $manager=$managerRegistry->getManager();
        $manager->persist($Entity);
        $manager->flush();
    }

    function Enregistrer($tableauValaleurs, User $utilisateur)
    {

        //on enregistre
        $object = new Etudiant;
        $object->setUser($utilisateur);
        $object->setNom(ucfirst($tableauValaleurs['nom']));
        $object->setPrenom(ucfirst($tableauValaleurs['prenom']));
        $object->setSexe(ucfirst($tableauValaleurs['sexe']));
        $object->setCreatedAt(new \datetime);
        //on inscrit
        $inscription=new Inscription();
        $inscription->setEtudiant($object);
        $inscription->setFiliere($tableauValaleurs['filiere']);
        $inscription->setNiveau($tableauValaleurs['niveau']);
        $inscription->setCreatedAt(new \datetime);
        //on retourne les resultats
        return $inscription;

    }

    function MettreAJour($tableauValaleurs,Etudiant $object, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        
        $object->setNom(ucfirst($tableauValaleurs['nom']));
        $object->setPrenom(ucfirst($tableauValaleurs['prenom']));
        $object->setSexe(ucfirst($tableauValaleurs['sexe']));
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->flush();
    }
    
}

class EcritureInscription{

    function Enregistrer(Etudiant $etudiant,Niveau $classe,Filiere $filiere, User $utilisateur, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        
        $object = new Inscription;
        $object->setUser($utilisateur);
        $object->setEtudiant($etudiant);
        $object->setNiveau($classe);
        $object->setFiliere($filiere);
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->persist($object);
        $manager->flush();
    }

    function MettreAJour(Inscription $object,Niveau $classe,Filiere $filiere, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        $object->setNiveau($classe);
        $object->setFiliere($filiere);
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->flush();
    }
    
}

class EcritureNote{

    function Enregistrer($tableauValeurs,Inscription $inscription,Ue $cours,Semestre $semestre, User $utilisateur, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        
        $object = new NotesEtudiant;
        $object->setUser($utilisateur);
        $object->setInscription($inscription);
        $object->setUe($cours);
        $object->setSemestre($semestre);
        $object->setMoyenne($tableauValeurs['moyenne']);
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->persist($object);
        $manager->flush();
    }

    function MettreAJour($tableauValaleurs,NotesEtudiant $object, ManagerRegistry $enregistreur)
    {
        // foreach ($tableauValaleurs as $key => $value) {
        //     $k[] = $key;
        //     $v[] = $value;
        // }
        // $k = implode(",", $k);
        // $v = implode(",", $v);
        
        $object->setMoyenne(ucfirst($tableauValaleurs['moyenne']));
        $object->setCreatedAt(new \datetime);
        $manager = $enregistreur->getManager();
        $manager->flush();
    }
    
}