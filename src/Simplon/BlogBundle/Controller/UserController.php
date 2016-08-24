<?php

namespace Simplon\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Simplon\BlogBundle\Entity\User;
use Simplon\BlogBundle\Entity\Login;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Simplon\BlogBundle\Form\Type\UserType;
use Simplon\BlogBundle\Form\Type\LoginType;

class UserController extends Controller
{
	// Formulaire d'enregistrement
	// ***************************

	public function registerAction (Request $request) {
	
	$user = new User();

	$form = $this->createForm(UserType::class, $user);

	$form->handleRequest($request);

	if ($form->isSubmitted() && $form->isValid()) {
		$donnees = $form->getData();
	 	$donnees->setRoles(array('ROLE_USER'));

		$em = $this->getDoctrine()->getManager();

		$password = $donnees->getPassword();
		$encoder = $this->container->get('security.password_encoder');
		$encoded = $encoder->encodePassword($user, $password);
		$user->setPassword($encoded);
		$user->setSalt('');

		$avatar = $donnees->getAvatar();

		if ($avatar !== null) {
			$fileName = md5(uniqid()).'.'.$avatar->guessExtension();
			$avatar->move($this->getParameter('dossier_avatar'), $fileName);

			$donnees->setAvatar($fileName);
		}

	 	$em->persist($donnees);
		$em->flush();

		$this->addFlash('registerOk', 'Utilisateur bien enregistré');
		return $this->redirectToRoute('Simplon_blog_login');
	}   

		return $this->render('SimplonBlogBundle:Blog:register.html.twig', array('form' => $form->createView()));
  	}
  
	// Formulaire de login
	// *******************

	public function loginAction(Request $request)
	{
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			return $this->redirectToRoute('Simplon_blog_listeArticles');
		}

		$authenticationUtils = $this->get('security.authentication_utils');
        $login = new Login();

        $form = $this->createForm(LoginType::class, $login);

        $form->handleRequest($request);


        return $this->render('SimplonBlogBundle:Blog:login.html.twig', array(
		  'last_username' => $authenticationUtils->getLastUsername(),
		  'error'         => $authenticationUtils->getLastAuthenticationError(),
            'form' => $form->createView()
		));
	}

	// Formulaire de modification du profil
	// ************************************

	/**
	 * @Security("has_role('ROLE_USER')")
	 */
	public function editProfileAction (Request $request, $user) {

		$currentUser = $this->get('security.token_storage')->getToken()->getUser();
	
		if ($user != $currentUser->getUsername()) {
			$this->addFlash('wrongUser', 'Accès interdit, ce n\'est pas votre compte');
  			return $this->redirectToRoute('Simplon_blog_homepage');
		}

		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('SimplonBlogBundle:User')->findOneByUsername($user);

		$avatar = $user->getAvatar();
		$user->setAvatar(null);

		$form = $this->createForm(UserType::class, $user);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
		  
			if ($donnees->getAvatar() === null) {
				$user->setAvatar($avatar);
			}
			else {
				$avatar = $donnees->getAvatar();
				$fileName = md5(uniqid()).'.'.$avatar->guessExtension();
				$avatar->move($this->getParameter('dossier_avatar'), $fileName);
				$donnees->setAvatar($fileName);
			}

			$password = $donnees->getPassword();
			$encoder = $this->container->get('security.password_encoder');
			$encoded = $encoder->encodePassword($user, $password);
			$user->setPassword($encoded);

			$em->persist($donnees);
			$em->flush();

			return $this->redirectToRoute('Simplon_blog_homepage');
		}

		return $this->render('SimplonBlogBundle:Blog:editProfile.html.twig', array('form'=>$form->createView(), 'user'=>$user));
	}

	// Liste des utilisateurs
	// **********************

	/**
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listeUsersAction () {
		$em = $this->getDoctrine()->getManager();
		$users = $em->getRepository('SimplonBlogBundle:User')->findAll();

		return $this->render('SimplonBlogBundle:Blog:users.html.twig', array('users'=>$users));
	}

	// Modification du role d'un utilisateur
	// *************************************

	/**
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function changeRoleAction ($user) {
		$em = $this->getDoctrine()->getManager();
		$userTmp = $em->getRepository('SimplonBlogBundle:User')->findOneByUsername($user);

		if ($userTmp->getRoles() === ['ROLE_USER']) {
			$userTmp->setRoles(array('ROLE_ADMIN'));
		}
		elseif ($userTmp->getRoles() === ['ROLE_ADMIN']) {
			$userTmp->setRoles(array('ROLE_USER'));
		}

		$em->persist($userTmp);
		$em->flush();

		return $this->redirectToRoute('Simplon_blog_liste_users');
	}

	// Suppression d'un utilisateur
	// ****************************

	/**
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function removeUserAction ($user) {
		$em = $this->getDoctrine()->getManager();
		$userTmp = $em->getRepository('SimplonBlogBundle:User')->findOneByUsername($user);

		$em->remove($userTmp);
		$em->flush();
	
		$this->addFlash('removeUserOk', 'Utilisateur supprimé');
		return $this->redirectToRoute('Simplon_blog_homepage');
	}
}
