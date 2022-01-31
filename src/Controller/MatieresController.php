<?php

namespace App\Controller;

use App\Entity\Ue;
use App\Entity\Matiere;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\MatiereRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MatieresController extends AbstractController
{
    /**
     * @Route("/matieres", name="matieres")
     */
    public function index(): Response
    {
        return $this->render('matieres/index.html.twig', [
            'controller_name' => 'MatieresController',
        ]);
    }

     /**
     * @Route("matieres", name="matieres")
     */
    public function matieres (MatiereRepository $matiereRepository, Request $request, ManagerRegistry $end)
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

            return $this->redirectToRoute('matieres');
        } 

        return $this->render('universg/matieres.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matiereRepository->matieresUser($user),
            'matieresNb'=>$matiereRepository->count([
                'user'=>$user
            ]),
        ]);
    }

    /**
     * @Route("matiere_edit/{id}", name="matiere_edit")
     */
    public function matiere_edit (Matiere $matiere, Request $request, ManagerRegistry $end)
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

            return $this->redirectToRoute('matieres');
        } 

        $repos=$this->getDoctrine()->getRepository(Matiere::class);
        $matieres = $repos->findAll();
        return $this->render('universg/matiere_edit.html.twig', [
            'controller_name' => 'UniversgController',
            'matieres'=>$matieres,
            'matiere'=>$matiere
        ]);
    }

    /**
     * Suppression des matieres
     * @Route("matiere_suppression/{id}", name="suppression_matiere")
     */
    public function suppression_matiere (Matiere $matiere, ManagerRegistry $end)
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

        return $this->redirectToRoute('matieres');
        
    }

    /**
     * on choisi la filiere et le niveau  pour la creation de leur ues
     * @Route("ue_filiere_niveau", name="ue_filiere_niveau")
     */
    function ue_filiere_niveau(SessionInterface $session,Request $request,FiliereRepository $filiereRepository, NiveauRepository $niveauRepository){
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
        return $this->render('universg/ue_filiere_niveau.html.twig',[
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on cree des ues pour les filieres et niveaux
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
        $niveauxPasEncoreUe=$niveauRepository->find($sessionN);
       
        return $this->render('universg/transfert_matiere.html.twig', [
            'controller_name' => 'MatieresController',
            'filieres'=>$filiereRepository->filieresUser($user),
            'matieres'=>$matiereRepository->matiereUserPasEncoreUe($user,$filieresPasEncoreUe,$niveauxPasEncoreUe),
            'niveaux'=>$niveauRepository->niveauxUser($user),
        ]);
    }

    /**
     * on consulte les donnees des ues
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
            'controller_name' => 'MatieresController',
            'ues'=>$repostn,
            'filieres'=>$filiereRepository->filieresUser($user),
            'niveaux'=>$niveauRepository->niveauxUser($user),
            'recherche'=>$uesUserFiliereNiveau
        ]);
    }


    /**
     * on supprime les ues
     * @Route("ue_suppression/{id}", name="suppression_ue")
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
    
}
