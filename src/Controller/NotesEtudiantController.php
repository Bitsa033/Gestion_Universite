<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\NotesEtudiant;
use App\Entity\Semestre;
use App\Form\EditNotesType;
use App\Form\NotesEtudiantType;
use App\Repository\EtudiantRepository;
use App\Repository\FiliereRepository;
use App\Repository\InscriptionRepository;
use App\Repository\NiveauRepository;
use App\Repository\NotesEtudiantRepository;
use App\Repository\SemestreRepository;
use App\Repository\UeRepository;
use Doctrine\Common\Annotations\Annotation\Enum;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("notes_etudiant_")
 */
class NotesEtudiantController extends AbstractController
{
    /**
     * @Route("index", name="notes_etudiant_index", methods={"GET"})
     */
    public function index(SessionInterface $session, NotesEtudiantRepository $notesEtudiantRepository, UeRepository $ueRepository): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        //dd($session);
        $notesUserFiliereNiveau = $notesEtudiantRepository->notesEtudiantUser($user, $sessionF, $sessionN,$sessionSe);
        return $this->render('notes_etudiant/index.html.twig', [
            'notes_etudiants' => $notesEtudiantRepository->findAll(),
            'notesUserFiliereNiveau' => $notesUserFiliereNiveau
        ]);
    }

    /**
     * @Route("test", name="notes_etudiant_test", methods={"GET"})
     */
    public function test(SessionInterface $session,InscriptionRepository $inscriptionRepository, NotesEtudiantRepository $notesEtudiantRepository, UeRepository $ueRepository): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);
        //dd($session);
        $notesUserFiliereNiveau = $notesEtudiantRepository->notesEtudiantUser($user, $sessionF, $sessionN,$sessionSe);
        return $this->render('notes_etudiant/test.html.twig', [
            'notes_etudiants' => $notesEtudiantRepository->findAll(),
            'notesUserFiliereNiveau' => $notesUserFiliereNiveau,
            'n2' => $ueRepository->uesFiliereNiveau($sessionF,$sessionN,$sessionSe),
            'inscriptions'=>$inscriptionRepository->etudiantsFiliereClasse($sessionF,$sessionN)
        ]);
    }

    /**
     * @Route("{id}", name="notes_etudiant_show", methods={"GET"})
     */
    public function show(NotesEtudiant $notesEtudiant): Response
    {
        return $this->render('notes_etudiant/show.html.twig', [
            'notes_etudiant' => $notesEtudiant,
        ]);
    }

    /**
     * @Route("{id}_edit", name="notes_etudiant_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, NotesEtudiant $notesEtudiant, EntityManagerInterface $entityManager): Response
    {
        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //sinon on modifie les données
        if (!empty($request->request->get('moyenne'))) {
            $notesEtudiant->setMoyenne($request->request->get('moyenne'));
            $notesEtudiant->setCreatedAt(new \DateTime());

            $entityManager->flush();

            return $this->redirectToRoute('notes_etudiant_index');
        }

        return $this->render('notes_etudiant/edit.html.twig', [
            'notes_etudiant' => $notesEtudiant,
        ]);
    }

    /**
     * @Route("{id}", name="notes_etudiant_delete", methods={"POST"})
     */
    public function delete(Request $request, NotesEtudiant $notesEtudiant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $notesEtudiant->getId(), $request->request->get('_token'))) {
            $entityManager->remove($notesEtudiant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('notes_etudiant_index', [], Response::HTTP_SEE_OTHER);
    }

}
