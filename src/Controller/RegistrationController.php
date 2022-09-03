<?php

namespace App\Controller;

use App\Entity\User;
use App\Security\SecurityAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;

class RegistrationController extends AbstractController
{
    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $userPasswordEncoder, GuardAuthenticatorHandler $guardHandler, SecurityAuthenticator $authenticator, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        
        if (!empty($request->request->get('email2')) && !empty($request->request->get('password'))) {
            $email=$request->request->get('email2');
            $password=$request->request->get('password');
            $user->setEmail($email);
            $user->setPassword(
                // encode the plain password
                $userPasswordEncoder->encodePassword(
                    $user,
                    $password
                    )
                );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }
        // elseif (count_chars($request->request->get('password'))<6) {
        //     $this->addFlash('error','Votre mot de passe doit contenir aumoins 6 caracteres, veuillez recommencer ...');
        // }
        
        return $this->render('registration/register2.html.twig', [
            'error'=>'error'
        ]);
    }
}
