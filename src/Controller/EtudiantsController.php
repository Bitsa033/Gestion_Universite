<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EtudiantsController extends AbstractController
{
    /**
     * @Route("/etudiants", name="etudiants")
     */
    public function index(): Response
    {
        return $this->render('etudiants/index.html.twig', [
            'controller_name' => 'EtudiantsController',
        ]);
    }
}
