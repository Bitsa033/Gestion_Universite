<?php

namespace App\Controller;

use App\Entity\NotesEtudiant;
use App\Entity\Semestre;
use App\Repository\InscriptionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\MatiereRepository;
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
     * @Route("s", name="s")
     */
    public function t(ManagerRegistry $managerRegistry, Request $request, SessionInterface $session, InscriptionRepository $inscriptionRepository, UeRepository $ueRepository): Response
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
                    $semestre = $this->getDoctrine()->getRepository(Semestre::class)->find($sessionSe);

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
            'm' =>  $inscriptionRepository->inscriptionsUserFiliereNiveau($user, $sessionF, $sessionN)
        ]);
    }
}
