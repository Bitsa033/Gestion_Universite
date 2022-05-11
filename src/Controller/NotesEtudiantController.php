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
     * @Route("choixFiliereNiveauxSemestreListeNote", name="notes_etudiant_choixFiliereNiveauxSemestreListeNote")
     */
    public function choixFiliereNiveauxSemestreListeNote(Request $request, SessionInterface $session, FiliereRepository $filiereRepository, NiveauRepository $niveauRepository, SemestreRepository $semestreRepository): Response
    {

        if (!empty($request->request->all())) {
            $filiere = $filiereRepository->find($request->request->get("filiere"));
            $niveau = $niveauRepository->find($request->request->get('niveau'));
            $get_filiere = $session->get('filiere', []);
            if (!empty($get_filiere)) {
                $session->set('filiere', $filiere);
                $session->set('niveau', $niveau);
            }
            //dd($session);
            $session->set('filiere', $filiere);
            $session->set('niveau', $niveau);
            return $this->redirectToRoute('notes_etudiant_index');
        }

        $user = $this->getUser();

        return $this->render('notes_etudiant/choixFiliereNiveauxSemestreListeNote.html.twig', [
            'filieres' => $filiereRepository->filieresUser($user),
            'niveaux' => $niveauRepository->niveauxUser($user),
            'semestres' => $semestreRepository->findAll()
        ]);
    }

    /**
     * @Route("index", name="notes_etudiant_index", methods={"GET"})
     */
    public function index(SessionInterface $session, NotesEtudiantRepository $notesEtudiantRepository, UeRepository $ueRepository): Response
    {
        $user = $this->getUser();

        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $notesUserFiliereNiveau = $notesEtudiantRepository->notesEtudiantUser($user, $sessionF, $sessionN);
        return $this->render('notes_etudiant/index.html.twig', [
            'notes_etudiants' => $notesEtudiantRepository->findAll(),
            'notesUserFiliereNiveau' => $notesUserFiliereNiveau
        ]);
    }

    /**
     * @Route("new", name="notes_etudiant_new", methods={"GET", "POST"})
     */
    public function new(InscriptionRepository $inscriptionRepository, UeRepository $ueRepository, SessionInterface $session, Request $request, EntityManagerInterface $entityManager): Response
    {

        //on cherche l'utilisateur connecté
        $user = $this->getUser();
        //si l'utilisateur est n'est pas connecté,
        // on le redirige vers la page de connexion
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        //on cherche les informations de la filiere,la classe et le semestre stockees dans la session
        $sessionF = $session->get('filiere', []);
        $sessionN = $session->get('niveau', []);
        $sessionSe = $session->get('semestre', []);

        if (!empty($request->request->get("inscription")) && !empty($request->request->get("ue")) && !empty($request->request->get("moyenne"))) {
            // on cherche les posts
            $etudiant = $inscriptionRepository->find($request->request->get("inscription"));
            $cours = $ueRepository->find($request->request->get("ue"));
            $moyenne = $request->get("moyenne");
            $notesEtudiant = new NotesEtudiant();
            $notesEtudiant->setInscription($etudiant);
            $notesEtudiant->setUe($cours);
            $notesEtudiant->setMoyenne($moyenne);
            $notesEtudiant->setSemestre($sessionSe);
            $notesEtudiant->setCreatedAt(new \datetime());
            $notesEtudiant->setUser($user);
            $entityManager->persist($notesEtudiant);
            $entityManager->flush();

            //dd($request);

            return $this->redirectToRoute('notes_etudiant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('notes_etudiant/new.html.twig', [
            'inscriptions' => $inscriptionRepository->inscriptionsUserFiliereNiveau($user, $sessionF, $sessionN),
            'cours' => $ueRepository->uesFiliereNiveau($sessionF, $sessionN)
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
     * @Route("{id}/edit", name="notes_etudiant_edit", methods={"GET", "POST"})
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

    /**
     * @Route("t", name="t")
     */
    public function t(EtudiantRepository $m): Response
    {

        return $this->render('notes_etudiant/essaie.html.twig', [
            'm' => $m->findAll()
        ]);
    }
}
