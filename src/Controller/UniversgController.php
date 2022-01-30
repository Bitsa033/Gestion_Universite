<?php

namespace App\Controller;

use App\Entity\Ue;
use App\Entity\Niveau;
use App\Entity\Filiere;
use App\Entity\Matiere;
use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\MatiereRepository;
use App\Repository\EtudiantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class UniversgController extends AbstractController
{
    /**
     * @Route("/universg", name="universg")
     */
    public function index(InscriptionRepository $inscriptionRepository, FiliereRepository $filiereRepository, NiveauRepository $NiveauRepository, MatiereRepository $matiereRepository) : Response
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        return $this->render('universg/index.html.twig', [
            'controller_name' => 'UniversgController',
            'inscriptions'=>$inscriptionRepository->inscriptionssUser($user),
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$NiveauRepository->niveauxUser($user),
            'matieres'=>$matiereRepository->matieresUser($user)
        ]);
    }

    /**
     * @Route("ue_filiere", name="ue_filiere")
     */
    function ue_filiere(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiere',[]);
            if (!empty($get_filiere)) {
              $session->set('filiere',$filiere);
              $session->set('niveau',$niveau);
            }
            //dd($session);
            $session->set('filiere',$filiere);
            $session->set('niveau',$niveau);
            return $this->redirectToRoute('transfert_matiere');
        }
        return $this->render('universg/filiere_ue.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * Affectation des matieres à des filieres et niveaux
     * @Route("transfert_matiere", name="transfert_matiere")
     */
    public function transfert_matiere (SessionInterface $session,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,MatiereRepository $matiereRepository ,Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        $sessionF=$session->get('filiere',[]);
        $sessionN=$session->get('niveau',[]);
        if (!empty($sessionF) && !empty($sessionN) && $request->request->get('ue')) {
            $matiere=$matiereRepository->find($request->request->get('ue'));    
            //dd($session->get('filiere'),$session->get('niveau'));
            $filiere=$filiereRepository->find($sessionF);
            $niveau=$niveauRepository->find($sessionN);
            $ue=new Ue();
            $ue->setFiliere($filiere);
            $ue->setUser($user);
            $ue->setNiveau($niveau);
            $ue->setMatiere($matiere);
            $ue->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($ue);
            $manager->flush();
            
            return $this->redirectToRoute('transfert_matiere');
        }
        $filieresPasEncoreUe=$filiereRepository->find($sessionF);
       
        return $this->render('universg/transfert_matiere.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'matieres'=>$matiereRepository->matiereUserPasEncoreUe($user,$filieresPasEncoreUe),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * Affichage des unites d'enseignement
     * @Route("ue", name="ue")
     */
    public function ue (FiliereRepository $filiereRepository,NiveauRepository $niveauRepository ,Request $request, UeRepository $repo)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        $repostn=$this->getDoctrine()->getRepository(Ue::class)->findAll();
        $uesUserFiliereNiveau=$repo->uesFiliereNiveau($request->request->get('filiere'),$request->request->get('niveau'));
        return $this->render('universg/ue.html.twig', [
            'controller_name' => 'UniversgController',
            'ues'=>$repostn,
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'recherche'=>$uesUserFiliereNiveau
        ]);
    }


    /**
     * @Route("ue/suppression/{id}", name="suppression_ue")
     */
    public function suppression_ue(Ue $ue, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($ue);
        $manager->flush();

        return $this->redirectToRoute('ue');
        
    }

    /**
     * @Route("note_filiere", name="note_filiere")
     */
     function note_filiere(SessionInterface $session, Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
         //on cherche l'utilisateur connecté
         $user= $this->getUser();
         //si l'utilisateur est n'est pas connecté,
         // on le redirige vers la page de connexion
         if (!$user) {
           return $this->redirectToRoute('app_login');
         }
 
        if (!empty($request->request->all())) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiereNote',[]);
            if (!empty($get_filiere)) {
                $session->set('filiereNote',$filiere);
                $session->set('niveauNote',$niveau);
            }
            //dd($session);
            $session->set('filiereNote',$filiere);
            $session->set('niveauNote',$niveau);
            
            return $this->redirectToRoute('notes_etudiant');
        }
         return $this->render('universg/note_filiere.html.twig',[
             'filieres'=>$filiereRepository->filieresUser($user),
             'niveaux'=>$niveauRepository->niveauxUser($user),
         ]);
     }

     /**
     * @Route("notes_etudiant", name="notes_etudiant")
     */
    function note_etudiant(SessionInterface $session, Request $request, InscriptionRepository $inscriptionRepository, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $inscription= $inscriptionRepository->find($request->request->get("inscription"));
            $get_inscription=$session->get('inscriptionNote',[]);
            if (!empty($get_inscription)) {
                $session->set('inscriptionNote',$inscription);
            }
            //dd($session);
            $session->set('inscriptionNote',$inscription);
            
            return $this->redirectToRoute('ajout_notes');
        }

        $sessionF=$session->get('filiereNote',[]);
        $sessionN=$session->get('niveauNote',[]);

        $filiere=$filiereRepository->find($sessionF);
        $niveau=$niveauRepository->find($sessionN);
        return $this->render('universg/note_etudiant.html.twig',[
            'inscriptions'=>$inscriptionRepository->inscriptionsUserFiliereNiveau($user,$filiere,$niveau),
        ]);
    }

    /**
     * Creer des notes pour des etudiants
     * @Route("ajout_notes", name="ajout_notes")
     */
    public function ajout_notes( SessionInterface $session,InscriptionRepository $inscriptionRepository, UeRepository $ueRepository,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,Request $request,ManagerRegistry $end_e )
        {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        $sessionI=$session->get('inscriptionNote',[]);
        if ( !empty($sessionI) && !empty($request->request->get('moyenne'))) {
           
            $ue = $ueRepository->find($request->request->get("ue"));
            $inscription = $inscriptionRepository->find($sessionI);

            $note=new NotesEtudiant();
            $note->setUser($user);
            $note->setInscription($inscription);
            $note->setUe($ue);
            $note->setMoyenne($request->request->get('moyenne'));
            $note->setCreatedAt(new \DateTime());
            $manager=$end_e->getManager();
            $manager->persist($note);
            $manager->flush();

            //dd($request);
            return $this->redirectToRoute('ajout_notes');
        }

        $sessionF=$session->get('filiereNote',[]);
        $sessionN=$session->get('niveauNote',[]);

        $matieres=$ueRepository->uesFiliereNiveau($sessionF,$sessionN);
        $inscriptions=$inscriptionRepository->inscriptionsUserFiliereNiveau($user,$request->request->get('filiere'),$request->request->get('niveau'));

        return $this->render('universg/noter_etudiant.html.twig', [
            'controller_name' => 'UniversgController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'matieres'=>$matieres,
            'inscriptions'=>$inscriptions
        ]);
    }
    /**
     * @Route("essaie/{id}", name="essaie")
     */
    public function essaie(Etudiant $etudiant)
    {
        if ($etudiant->inscrit($etudiant)) {

            return $this->json([
                'inscrit'=>'oui',
                'message'=>'Etudiant inscrit'
    
            ],200);
          
        }
        return $this->json([
            'inscrit'=>'non',
            'message'=>'Etudiant pas inscrit'

        ],200);
    }
}

