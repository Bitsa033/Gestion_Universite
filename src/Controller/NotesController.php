<?php

namespace App\Controller;

use App\Entity\NotesEtudiant;
use App\Entity\Semestre;
use App\Repository\FiliereRepository;
use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MatiereRepository;
use App\Repository\NiveauRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * @Route("/notes_", name="notes_")
 */
class NotesController extends AbstractController
{
    /**
     * @Route("/notes", name="notes")
     */
    public function index(): Response
    {
        return $this->render('notes/index.html.twig', [
            'controller_name' => 'NotesController',
        ]);
    }

    /**
     * @Route("choixFiliereNiveauxSemestreN", name="choixFiliereNiveauxSemestreN")
     */
    public function choixFiliereNiveauxSemestreC(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository,SemestreRepository $semestreRepository)
    {

        if (!empty($request->request->get('filiere')) && !empty($request->request->get('classe')) && !empty($request->request->get('semestre'))) {
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $classe = $niveauRepository->find($request->request->get('classe'));
            $semestre=$semestreRepository->find($request->request->get('semestre'));
            $get_filiere = $session->get('filiere', []);
            $get_classe = $session->get('niveau', []);
            $get_semestre = $session->get('semestre', []);
            if (!empty($get_filiere) && !empty($get_niveau) && !empty($get_semestre)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $classe);
                $session->set('semestre', $semestre);
            }
            $session->set('filiere', $filiere);
            $session->set('niveau', $classe);
            $session->set('semestre', $semestre);
            //dd($session);

            //return $this->redirectToRoute('etudiants_i');
        }

        return $this->redirectToRoute('notes_s');
    }

    /**
     * @Route("s", name="s")
     */
    public function t(ManagerRegistry $managerRegistry, Request $request, SessionInterface $session, InscriptionRepository $inscriptionRepository, UeRepository $ueRepository,FiliereRepository $filiereRepository,NiveauRepository $niveauRepository, SemestreRepository $semestreRepository): Response
    {
        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        $user = $this->getUser();

        if (!empty($request->request->get("cours"))) {

            $check_array = $request->request->get("inscription");
            foreach ($request->request->get("nom") as $key => $value) {
                if (in_array($request->request->get("nom")[$key], $check_array)) {
                    //dd($request->request->get("inscription")[$key]);
                    //echo $request->request->get("moyenne")[$key];
                    //echo '<br>';
                    $etudiant = $inscriptionRepository->find($request->request->get("inscription")[$key]);
                    $cours = $ueRepository->find($request->request->get("cours"));
                    $moyenne = $request->request->get("moyenne")[$key];
                    $semestre = $semestreRepository->find($sessionSe);

                    $notesEtudiant = new NotesEtudiant();
                    $notesEtudiant->setInscription($etudiant);
                    $notesEtudiant->setUe($cours);
                    $notesEtudiant->setMoyenne($moyenne);
                    $notesEtudiant->setSemestre($semestre);
                    $notesEtudiant->setCreatedAt(new \datetime());
                    $notesEtudiant->setUser($user);
                    $manager = $managerRegistry->getManager();
                    $manager->persist($notesEtudiant);
                    $manager->flush();
                }
            }
        }

        return $this->render('notes_etudiant/essaie.html.twig', [
            'mr' =>  $ueRepository->uesFiliereNiveau($sessionF, $sessionN),
            'm' =>  $inscriptionRepository->inscriptionsUserFiliereNiveau($user, $sessionF, $sessionN),
            'filieres'=>$filiereRepository->filieresUser($user),
            'classes' =>$niveauRepository->niveauxUser($user),
            'semestres' =>$semestreRepository->findAll()
        ]);
    }
}
