<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Repository\UeRepository;
use App\Repository\NiveauRepository;
use App\Repository\FiliereRepository;
use App\Repository\EtudiantRepository;
use App\Repository\InscriptionRepository;
use Doctrine\Persistence\ManagerRegistry;
use Enregistrement\EcritureEtudiant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("etudiants_", name="etudiants_")
 */
class EtudiantsController extends AbstractController
{

    /**
     * @Route("ajout", name="ajout")
     */
    public function ajout(Request $request, ManagerRegistry $end_e)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données
        if( !empty($request->request->get('nom')) && !empty($request->request->get('prenom')) && !empty($request->request->get('sexe'))) {

            $nom=$request->request->get('nom');
            $prenom=$request->request->get('prenom');
            $sexe=$request->request->get('sexe');
            $data=array(
                'nom'=>$nom,
                'prenom'=>$prenom,
                'sexe'=>$sexe
            );
            //on enregistre l'etudiant
            $enregistrerEtudiant = new EcritureEtudiant;
            $enregistrerEtudiant->Enregistrer($data,$user,$end_e);

            return $this->redirectToRoute('etudiants_ajout');
        }
        return $this->render('etudiants/ajout_etudiants.html.twig', [
            
        ]);
    }

    /**
     * @Route("liste", name="liste")
     */
    public function liste(EtudiantRepository $etudiantRepository)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('etudiants/etudiants.html.twig', [
            'etudiants' => $etudiantRepository->etudiantsUser($user),
            'nbEtudiants' => $etudiantRepository->count([
                'user' => $user
            ])
        ]);
    }

    /**
     * On supprime un etudiant par son id
     * @Route("suppression/{id}", name="suppression")
     */
    public function suppression(Etudiant $etudiant, ManagerRegistry $end)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on supprime les données
        $manager = $end->getManager();
        $manager->remove($etudiant);
        $manager->flush();

        return $this->redirectToRoute('etudiants_liste');
    }

    /**
     * @Route("choixFiliereNiveauxI", name="choixFiliereNiveauxI")
     */
    public function choixFiliereNiveauxI(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe'))) {
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $classe = $niveauRepository->find($request->request->get('classe'));
            $get_filiere = $session->get('filiere', []);
            $get_classe = $session->get('niveau', []);
            if (!empty($get_filiere)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $classe);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $classe);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('etudiants_i');
    }

    /**
     * @Route("i", name="i")
     */
    public function i(ManagerRegistry $managerRegistry, Request $request, SessionInterface $session, EtudiantRepository $etudiantRepository, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository): Response
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $user = $this->getUser();

        if (isset($_POST['enregistrer'])) {

            $check_array = $request->request->get("etudiantId");
            foreach ($request->request->get("etudiantName") as $key => $value) {
                if (in_array($request->request->get("etudiantName")[$key], $check_array)) {
                    //dd($request->request->get("inscription")[$key]);
                    // echo $request->request->get("matiereName")[$key];
                    // echo '<br>';
                    $etudiant = $etudiantRepository->find($request->request->get("etudiantName")[$key]);
                    $filiere = $filiereRepository->find($sessionF);
                    $classe = $niveauRepository->find($sessionN);
                    $inscription = new Inscription();
                    $inscription->setUser($user);
                    $inscription->setEtudiant($etudiant);
                    $inscription->setFiliere($filiere);
                    $inscription->setNiveau($classe);
                    $inscription->setCreatedAt(new \DateTime());
                    //$manager=$mg->getManager();
                    $manager = $managerRegistry->getManager();
                    $manager->persist($inscription);
                    $manager->flush();
                }
            }
        }

        return $this->render('etudiants/essaie.html.twig', [
            'mr' =>  $etudiantRepository->etudiantsUserPasInscris($user),
            'filieres' =>$filiereRepository->filieresUser($user),
            'classes'  =>$niveauRepository->niveauxUser($user)
        ]);
    }

    /**
     * On inscrit l'etudiant
     * @Route("inscription", name="inscription")
     */
    public function inscription(EtudiantRepository $etudiantRepository, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, Request $request, ManagerRegistry $end_e)
    {

        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on insert les données

        if (
            !empty($request->request->get('niveau')) &&
            !empty($request->request->get('filiere')) &&
            !empty($request->request->get('etudiant'))
        ) {

            $niveau = $niveauRepository->find($request->request->get("niveau"));
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $etudiant = $etudiantRepository->find($request->request->get("etudiant"));

            $inscription = new Inscription();
            $inscription->setUser($user);
            $inscription->setEtudiant($etudiant);
            $inscription->setFiliere($filiere);
            $inscription->setNiveau($niveau);
            $inscription->setCreatedAt(new \DateTime());
            //$manager=$mg->getManager();
            $manager = $end_e->getManager();
            $manager->persist($inscription);
            $manager->flush();

            return $this->redirectToRoute('etudiants_inscription');
        }


        return $this->render('etudiants/inscription_etudiants.html.twig', [
            'controller_name' => 'EtudiantsController',
            'niveaux' => $niveauRepository->niveauxUser($user),
            'filieres' => $filiereRepository->filieresUser($user),
            'etudiants' => $etudiantRepository->etudiantsUserPasInscris($user)
        ]);
    }

    /**
     * on consulte les donnees des inscriptions
     * @Route("liste_Inscriptions", name="liste_Inscriptions")
     */
    public function liste_Inscriptions(FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, Request $request, UeRepository $ueRepository, InscriptionRepository $inscriptionRepository)
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on consulte les données
        $postFiliere = $request->request->get('filiere');
        $postNiveau = $request->request->get('niveau');
        $inscriptions = $inscriptionRepository->inscriptionsFiliereNiveau($postFiliere, $postNiveau, $user);
        return $this->render('etudiants/liste_inscriptions_etudiants.html.twig', [
            'controller_name' => 'EtudiantsController',
            'ues' => $ueRepository->uesUser($user),
            'filieres' => $filiereRepository->filieresUser($user),
            'niveaux' => $niveauRepository->niveauxUser($user),
            'inscriptions' => $inscriptions
        ]);
    }
}
