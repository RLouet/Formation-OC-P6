<?php


namespace App\Controller;


use App\Form\ProfileType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProfileController extends AbstractController
{
    /**
     * @Route("/profile", name="profile_edit")
     */
    public function profileEdit(Request $request): Response
    {
        $user = $this->getUser();

        if (!$user) {
            //return $this->redirectToRoute('front_home');
        }

        //(gd_info());

        $form = $this->createForm(ProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {

            }
        }

        return $this->render('profile/profile-edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}