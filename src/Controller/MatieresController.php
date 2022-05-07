<?php

namespace App\Controller;

use App\Entity\Ue;
use App\Entity\Matiere;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\MatiereRepository;
use App\Repository\SemestreRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("matieres_",name="matieres_")
 */
class MatieresController extends AbstractController
{
     /**
     * @Route("ajoutEt_liste", name="ajoutEt_liste")
     */
    public function ajout (MatiereRepository $matiereRepository, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        if (!empty($request->request->get('nom_mat'))) {
            $matiere=new Matiere();
            $matiere->setUser($user);
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($matiere);
            $manager->flush();

            return $this->redirectToRoute('matieres_ajoutEt_liste');
        } 

        return $this->render('matieres/matieres.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matiereRepository->matieresUser($user),
            'matieresNb'=>$matiereRepository->count([
                'user'=>$user
            ]),
        ]);
    }

    /**
     * @Route("miseAjour/{id}", name="miseAjour")
     */
    public function miseAjour (Matiere $matiere, Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on modifie les données
        if (!empty($request->request->get('nom_mat'))) {
            $matiere->setNom($request->request->get('nom_mat'));
            $matiere->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->flush();

            return $this->redirectToRoute('matieres_ajoutEt_liste');
        } 

        $repos=$this->getDoctrine()->getRepository(Matiere::class);
        $matieres = $repos->findAll();
        return $this->render('matieres/matiere_miseAjour.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matieres,
            'matiere'=>$matiere
        ]);
    }

    /**
     * Suppression des matieres
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression (Matiere $matiere, ManagerRegistry $end)
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
        $manager->remove($matiere);
        $manager->flush();

        return $this->redirectToRoute('matieres_ajoutEt_liste');
        
    }

    /**
     * on choisi la filiere, le semestre et le niveau  pour la creation des ues
     * @Route("choix_filiereEtNiveau_Ue", name="choix_filiereEtNiveau_Ue")
     */
    function choix_filiereEtNiveau_Ue(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository){
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        if (!empty($request->request->all())) {
            $filiere=$filiereRepository->find($request->request->get("filiere"));
            $semestre=$semestreRepository->find($request->request->get('semestre'));
            $niveau=$niveauRepository->find($request->request->get('niveau'));
            $get_filiere=$session->get('filiere',[]);
            $get_semestre=$session->get('semestre',[]);
            $get_niveau=$session->get('niveau',[]);
            if (!empty($get_filiere) && !empty($get_semestre) && !empty($get_niveau)) {
              $session->set('filiere',$filiere);
              $session->set('semestre',$semestre);
              $session->set('niveau',$niveau);
            }
            //dd($session);
            $session->set('filiere',$filiere);
            $session->set('semestre',$semestre);
            $session->set('niveau',$niveau);
            return $this->redirectToRoute('matieres_transfert');
        }
        return $this->render('matieres/filiereNiveau_Ue.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'semestres'=>$semestreRepository->findAll(),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on cree des ues pour les filieres et niveaux et on precise le semestre tous enregistrees dans la session
     * @Route("transfert", name="transfert")
     */
    public function transfert(SessionInterface $session,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,MatiereRepository $matiereRepository , SemestreRepository $semestreRepository ,Request $request, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }
        $sessionF=$session->get('filiere',[]);
        $sessionSe=$session->get('semestre',[]);
        $sessionN=$session->get('niveau',[]);
        if (!empty($sessionF) && !empty($sessionSe) && !empty($sessionN) && !empty($request->request->get('ue'))) {
            $matiere=$matiereRepository->find($request->request->get('ue'));    
            //dd($session->get('filiere'),$session->get('niveau'));
            $filiere=$filiereRepository->find($sessionF);
            $semestre=$semestreRepository->find($sessionSe);
            $niveau=$niveauRepository->find($sessionN);
            $ue=new Ue();
            $ue->setFiliere($filiere);
            $ue->setSemestre($semestre);
            $ue->setUser($user);
            $ue->setNiveau($niveau);
            $ue->setMatiere($matiere);
            $ue->setCreatedAt(new \DateTime());
            $manager = $end->getManager();
            $manager->persist($ue);
            $manager->flush();
            
            return $this->redirectToRoute('matieres_transfert');
        }
        $filieresPasEncoreUe=$filiereRepository->find($sessionF);
        $niveauxPasEncoreUe=$niveauRepository->find($sessionN);
        $semestrePasEncoreUe=$semestreRepository->find($sessionSe);
       
        return $this->render('matieres/transfert_matiere.html.twig', [
            'controller_name' => 'MatieresController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'matieres'=>$matiereRepository->matiereUserPasEncoreUe($user,$filieresPasEncoreUe,$niveauxPasEncoreUe,$semestrePasEncoreUe),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on choisi la filiere et le niveau  pour consulter des cours
     * @Route("choix_filiereEtNiveauUes_liste", name="choix_filiereEtNiveauUes_liste")
     */
    function choix_filiereEtNiveauUes_liste(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
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
            return $this->redirectToRoute('matieres_liste_Ues');
        }
        return $this->render('matieres/filiereNiveauUes_liste.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on consulte les donnees des ues
     * @Route("liste_Ues", name="liste_Ues")
     */
    public function liste_Ues (SessionInterface $session,FiliereRepository $filiereRepository,NiveauRepository $niveauRepository,UeRepository $ueRepository,Request $request)
    {
        //on cherche l'utilisateur connecté
        $user= $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
          return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        $sessionF=$session->get('filiere',[]);
        $sessionN=$session->get('niveau',[]);
        $uesUserFiliereNiveau=$ueRepository->uesFiliereNiveau($sessionF,$sessionN);
        return $this->render('matieres/liste_Ues.html.twig', [
            'controller_name' => 'MatieresController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'uesUserFiliereNiveau'=>$uesUserFiliereNiveau
        ]);
    }


    /**
     * on supprime les ues
     * @Route("ue_suppression/{id}", name="ue_suppression")
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

        return $this->redirectToRoute('matieres_liste_Ues');
        
    }
    
}
