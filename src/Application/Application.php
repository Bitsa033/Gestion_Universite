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

        //on cherche l'utilisateur connecté
        // $user= $this->getUser();
        // if (!$user) {
        //   return $this->redirectToRoute('app_login');
        // }

    }

    public function insert_to_db($entity){
       
        $this->db->persist($entity);
        $this->db->flush();
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

    public function nouvelle_filiere($data, User $user)
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

    public function nouveau_niveau($data, User $user)
    {
        $this->multiple_row($data);
        
        $classe= new $this->table_niveau;
        $classe->setUser($user);
        $classe->setNom(ucfirst($data['nom']));
        $classe->setCreatedAt(new \datetime);
        $this->db->persist($classe);
        $this->db->flush();
    }

    public function nouveau_semestre($data, User $user)
    {
        $this->multiple_row($data);
        
        $semestre= new $this->table_semestre;
        $semestre->setUser($user);
        $semestre->setNom(ucfirst($data['nom']));
        $semestre->setCreatedAt(new \datetime);
        $this->db->persist($semestre);
        $this->db->flush();
    }

    public function nouvelle_matiere($data, User $user)
    {
        $matiere = new $this->table_matiere;
        $matiere->setUser($user);
        $matiere->setNom(ucfirst($data['nom']));
        $matiere->setCreatedAt(new \datetime);

        $ue=new $this->table_ue;
        $ue->setFiliere($this->repo_filiere->find($data['filiere']));
        $ue->setNiveau($this->repo_niveau->find($data['niveau']));
        $ue->setSemestre($this->repo_semestre->find($data['semestre']));
        $ue->setUser($user);
        $ue->setMatiere($matiere);
        $ue->setNote($data['note']);
        $ue->setCredit($data['note']/20);
        $ue->setCode($data['code']);
        $ue->setCreatedAt(new \DateTime);
        $this->db->persist($ue);
        $this->db->flush();
    }

    public function nouveau_cour($data)
    {
        $object = new $this->table_ue;
        $object->setUser($data['user']);
        $object->setMatiere($this->repo_matiere->find($data['matiere']));
        $object->setFiliere($this->repo_filiere->find($data['filiere']));
        $object->setNiveau($this->repo_niveau->find($data['niveau']));
        $object->setSemestre($this->repo_semestre->find($data['semestre']));
        $object->setCredit(4);
        $object->setCreatedAt(new \datetime);
        $this->db->persist($object);
        $this->db->flush();
    }

    public function nouvel_etudiant($data)
    {

        //on enregistre
        $etudiant = $this->table_etudiant;
        $etudiant->setUser($data['user']);
        $etudiant->setNom(ucfirst($data['nom']));
        $etudiant->setPrenom(ucfirst($data['prenom']));
        $etudiant->setSexe(ucfirst($data['sexe']));
        $etudiant->setCreatedAt(new \datetime);
        //on inscrit
        $inscription=new $this->table_inscription;
        $inscription->setEtudiant($etudiant);
        $inscription->setFiliere($this->repo_filiere->find($data['filiere']));
        $inscription->setNiveau($this->repo_niveau->find($data['niveau']));
        $inscription->setCreatedAt(new \datetime);
        //on retourne les resultats
        $this->db->persist($inscription);
        $this->db->flush();

    }

    public function nouvelle_inscription($data)
    {
        
        $object = new $this->table_inscription;
        $object->setUser($this->repo_user->find($data['user']));
        $object->setEtudiant($this->repo_etudiant->find($data['etudiant']));
        $object->setNiveau($this->repo_niveau->find($data['niveau']));
        $object->setFiliere($this->repo_filiere->find($data['filiere']));
        $object->setCreatedAt(new \datetime);
        $this->db->persist($object);
        $this->db->flush();
    }

    function nouvelle_note($data)
    {
        $object = new $this->table_note;
        $object->setUser($this->repo_user->find($data['user']));
        $object->setInscription($this->repo_inscription->find($data['inscription']));
        $object->setUe($this->repo_ue->find($data['cours']));
        $object->setSemestre($this->repo_semestre->find($data['semestre']));
        $object->setMoyenne($data['note']);
        $object->setCreatedAt(new \datetime);
        $this->db->persist($object);
        $this->db->flush();
    }


}