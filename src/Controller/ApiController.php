<?php

namespace App\Controller;

use App\Entity\Etudiant;
use App\Repository\EtudiantRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Stmt\Else_;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ApiController extends AbstractController
{
    /**
     * @Route("/api", name="api")
     */
    public function index(EtudiantRepository $etudiantRepository): Response
    {
        $etudiants=$etudiantRepository->findAll();
        //on instancie un encoder json
        $encoders=[ new JsonEncoder()];
        //on instancie un normaliseur donc un convertisseur
        $normalizer=[new ObjectNormalizer()];
        //on prepare la convertion de la collection en tableau php
        $serializer=new Serializer($normalizer,$encoders);
        //on convertit la collection en Tableau
        $contentJson=$serializer->serialize($etudiants, 'json' , [
            'circular_reference_handler' =>function($object){
                return $object->getId();
            }
        ]);

        //dd($contentJson);

        $response=new Response($contentJson);
        $response->headers->set('Content-Type','application/json');
        return $response;
        
    }

    /**
     * @Route("api_create", name="api_create")
     */
    public function create_student(Request $request, ManagerRegistry $managerRegistry): Response
    {
        $donnee= json_decode($request->getContent());
        $etudiant = new Etudiant();
        $etudiant->setNom(strtoupper($donnee->nom));
        $etudiant->setprenom(strtoupper($donnee->prenom));
        $etudiant->setSexe(strtoupper($donnee->sexe));
        $etudiant->setCreatedAt(new \DateTime());
        $manager=$managerRegistry->getManager();
        $manager->persist($etudiant);
        $manager->flush();
        return new Response('données crées',201);
    }

    /**
     * @Route("api_update/{id}", name="api_update")
     */
    public function update_student(?Etudiant $etudiant, Request $request, ManagerRegistry $managerRegistry): Response
    {
        $donnee= json_decode($request->getContent());
        if (!$etudiant) {
           
            $etudiant = new Etudiant();
            $etudiant->setNom(strtoupper($donnee->nom));
            $etudiant->setprenom(strtoupper($donnee->prenom));
            $etudiant->setSexe(strtoupper($donnee->sexe));
        }
        $etudiant->setNom(strtoupper($donnee->nom));
        $etudiant->setprenom(strtoupper($donnee->prenom));
        $etudiant->setSexe(strtoupper($donnee->sexe));
        if (!$etudiant->getId()) {
           
            $etudiant->setCreatedAt(new \DateTime());
            $manager=$managerRegistry->getManager();
            $manager->persist($etudiant);
            $manager->flush();
            return new Response('données crées',201);
        }
        $manager=$managerRegistry->getManager();
        $manager->persist($etudiant);
        $manager->flush();
        return new Response('données modifiées',200);
    }

    /**
     * @Route("api_delete/{id}", name="api_delete")
     */
    public function delete_student(Etudiant $etudiant, Request $request, ManagerRegistry $managerRegistry): Response
    {
        
        if (!$etudiant->getId()) {
           
            return new Response('erreur',500);
        }
        $manager=$managerRegistry->getManager();
        $manager->remove($etudiant);
        $manager->flush();
        return new Response('données supprimées',200);
    }

    
}
