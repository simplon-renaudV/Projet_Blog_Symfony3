<?php

namespace renaud\BlogBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use renaud\BlogBundle\Entity\User;
use renaud\BlogBundle\Form\UserType;

class UserController extends Controller
{
	// Formulaire d'enregistrement
	// ***************************

	public function registerAction (Request $request) {
	
	$session = $request->getSession();

	$user = new User();

	$form = $this->createForm(UserType::class, $user);

	$form->handleRequest($request);

	if ($form->isSubmitted() && $form->isValid()) {
		$donnees = $form->getData();
	 	$donnees->setRoles(array('ROLE_ADMIN'));

		$em = $this->getDoctrine()->getManager();

		$password = $donnees->getPassword();
		$encoder = $this->container->get('security.password_encoder');
		$encoded = $encoder->encodePassword($user, $password);

		$user->setPassword($encoded);
		$user->setSalt('');

		$avatar = $donnees->getAvatar();

		if ($avatar != null) {
			$fileName = md5(uniqid()).'.'.$avatar->guessExtension();
			$avatar->move($this->getParameter('dossier_avatar'), $fileName);

			$donnees->setAvatar($fileName);
		}

	 	$em->persist($donnees);
		$em->flush();

		$this->addFlash('registerOk', 'Utilisateur bien enregistré');
		return $this->redirectToRoute('renaud_blog_homepage');
	}   

		return $this->render('renaudBlogBundle:Blog:register.html.twig', array('form' => $form->createView()));
  	}
  
	// Formulaire de login
	// *******************

	public function loginAction(Request $request)
	{
		// Si le visiteur est déjà identifié, on le redirige vers l'accueil
		if ($this->get('security.authorization_checker')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
			return $this->redirectToRoute('renaud_blog_homepage');
		}

		// Le service authentication_utils permet de récupérer le nom d'utilisateur
		// et l'erreur dans le cas où le formulaire a déjà été soumis mais était invalide
		// (mauvais mot de passe par exemple)

		$authenticationUtils = $this->get('security.authentication_utils');

		return $this->render('renaudBlogBundle:Blog:login.html.twig', array(
		  'last_username' => $authenticationUtils->getLastUsername(),
		  'error'         => $authenticationUtils->getLastAuthenticationError(),
		));
	}

	// Formulaire de modification du profil
	// ************************************

	/**
	 * @Security("has_role('ROLE_USER')")
	 */
	public function editProfileAction (Request $request, $user) {

		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();
		$user = $em->getRepository('renaudBlogBundle:User')->findOneByUsername($user);

		$user->setAvatar(null);

		$form = $this->createForm(UserType::class, $user);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
		  
			$password = $donnees->getPassword();
			$encoder = $this->container->get('security.password_encoder');
			$encoded = $encoder->encodePassword($user, $password);

			$user->setPassword($encoded);
			$em->persist($donnees);
			$em->flush();

			return $this->redirectToRoute('renaud_blog_homepage');
		}

		return $this->render('renaudBlogBundle:Blog:editProfile.html.twig', array('form'=>$form->createView(), 'user'=>$user));
	}

	// Liste des utilisateurs
	// **********************

	/**
	 * @Security("has_role('ROLE_ADMIN')")
	 */
	public function listeUsersAction () {
		$em = $this->getDoctrine()->getManager();
		$users = $em->getRepository('renaudBlogBundle:User')->findAll();

		return $this->render('renaudBlogBundle:Blog:users.html.twig', array('users'=>$users));
	}

	public function changeRoleAction ($user) {
		$em = $this->getDoctrine()->getManager();
		$userTmp = $em->getRepository('renaudBlogBundle:User')->findOneByUsername($user);

		if ($userTmp->getRoles() === ['ROLE_USER']) {
			$userTmp->setRoles(array('ROLE_ADMIN'));
		}
		elseif ($userTmp->getRoles() === ['ROLE_ADMIN']) {
			$userTmp->setRoles(array('ROLE_USER'));
		}

		$em->persist($userTmp);
		$em->flush();

		return $this->redirectToRoute('renaud_blog_liste_users');
	}
}