<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FilieresController extends AbstractController
{
    /**
     * @Route("/filieres", name="filieres")
     */
    public function index(): Response
    {
        return $this->render('filieres/index.html.twig', [
            'controller_name' => 'FilieresController',
        ]);
    }
}
