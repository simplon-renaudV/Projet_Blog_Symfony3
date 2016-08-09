<?php

namespace renaud\BlogBundle\Controller;

use renaud\BlogBundle\Form\ArticleType;
use renaud\BlogBundle\Form\UserType;
use renaud\BlogBundle\Form\LoginType;
use renaud\BlogBundle\Entity\Article;
use renaud\BlogBundle\Entity\User;
use renaud\BlogBundle\Entity\Login;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;

class BlogController extends Controller {

	// Page d'accueil, affiche uniquement les 4 derniers articles
	public function indexAction (Request $request) {

		$session = $request->getSession();

		$articles = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->findBy(array('publie'=>1), array('date'=>'DESC'), 4);
		
		return $this->render('renaudBlogBundle:Blog:index.html.twig', array('articles' => $articles, 'user'=>$session->get('user'), 'vus'=>$session->get('vus')));
	}

	// Affiche tout les articles
	public function listeArticlesAction (Request $request) {

		$session = $request->getSession();

		$articles = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->findAll();

		return $this->render('renaudBlogBundle:Blog:articles.html.twig', array('articles' => $articles, 'user'=>$session->get('user'), 'vus'=>$session->get('vus')));
	}

	// Affiche un seul article identifié par $id
	public function viewAction ($id, Request $request) {

		$session = $request->getSession();

		$article = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->find($id);

		$articlesVus = $session->get('vus');
		$articlesVus[$id] = $id;
		$session->set('vus', $articlesVus);

		return $this->render('renaudBlogBundle:Blog:view.html.twig', array('article' => $article, 'user'=>$session->get('user')));
	}

	// Page d'administration
	public function adminArticlesAction (Request $request) {
		$session = $request->getSession();
		
		$articles = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->findAll();

		$user = $this->getDoctrine()
		->getRepository('renaudBlogBundle:User')
		->findOneByMail($session->get('user'));

		return $this->render('renaudBlogBundle:Blog:adminArticles.html.twig', array('articles'=>$articles, 'user'=>$session->get('user'), 'id'=>$user->getId()));
	}

	// Formulaire pour ajouter un article
	public function addAction (Request $request) {
	
		$session = $request->getSession();

		$article = new Article();

		$form = $this->createForm(ArticleType::class, $article);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
			$donnees->setDate(new \DateTime());
			$donnees->setPublie(0);

			$em = $this->getDoctrine()->getManager();
			
 			$em->persist($donnees);
			$em->flush();
		
			$this->addFlash('addOk', 'Article bien enregistré');
  			return $this->redirectToRoute('renaud_blog_homepage');
		}

		return $this->render('renaudBlogBundle:Blog:add.html.twig', array(
            'form' => $form->createView(), 'user'=>$session->get('user')
        )); 
    }	

    // Formulaire pour modifier un article identifié par $id
    public function updateAction (Request $request, $id) {
		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();
    	$article = $em->getRepository('renaudBlogBundle:Article')->find($id);

   		if (!$article) {
        	throw $this->createNotFoundException('Cet article n\'existe pas ');
    	}

    	$form = $this->createForm(ArticleType::class, $article);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
			$donnees->setDate(new \DateTime());

			$em->persist($donnees);
			$em->flush();
		
			$this->addFlash('modifyOk', 'Article modifié');
  			return $this->redirectToRoute('renaud_blog_homepage');
		}

    	return $this->render('renaudBlogBundle:Blog:modify.html.twig', array('form'=>$form->createView(), 'user'=>$session->get('user')));
	}

	// Suppression de l'article $id
	public function removeAction (Request $request, $id) {
		$session = $request->getSession();

		$article = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->find($id);

		$em = $this->getDoctrine()->getManager();
		$em->remove($article);
		$em->flush();
	
		$this->addFlash('removeOk', 'Article supprimé');
  		return $this->redirectToRoute('renaud_blog_homepage');
	}

	// Permet de publier ou dépublier un article $id
	public function publierAction (Request $request, $id) {
		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();

		$article = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->find($id);

		if ($article->getPublie() == true) {
			$article->setPublie(false);
		}
		else {
			$article->setPublie(true);
		}

		$em->flush();

		return $this->redirectToRoute('renaud_blog_homepage');
	}

	// Formulaire de login
	public function loginAction (Request $request) {
		
		$session = $request->getSession();

		$login = new Login;

		$form = $this->createForm(LoginType::class, $login);
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
			
			$user = $this->getDoctrine()
			->getRepository('renaudBlogBundle:User')
			->findOneByMail($donnees->getMail());	
		
			if ($user == null)
			{
				$this->addFlash('userUnknown', 'Nom d\'utilisateur incorrect');
				return $this->redirectToRoute('renaud_blog_login');
			}
			else {
				if ($user->getMail() == $donnees->getMail() && password_verify($donnees->getPassword(), $user->getPassword())) {
						
					$session->set('user', $donnees->getMail());
					return $this->redirectToRoute('renaud_blog_homepage');
				}
				else {
					$this->addFlash('wrongPassword', 'Mot de passe incorrect');
					return $this->redirectToRoute('renaud_blog_login');
				}
			}
		}

		return $this->render('renaudBlogBundle:Blog:login.html.twig', array('form'=> $form->createView(),'user'=>$session->get('user')));
	}

	// Formulaire d'enregistrement
	public function registerAction (Request $request) {
		
		$session = $request->getSession();

		$user = new User();

		$form = $this->createForm(UserType::class, $user);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
  			$donnees->setPassword(password_hash($donnees->getPassword(),PASSWORD_DEFAULT));

			$em = $this->getDoctrine()->getManager();

 			$avatar = $donnees->getAvatar();
            $fileName = md5(uniqid()).'.'.$avatar->guessExtension();
            $avatar->move($this->getParameter('dossier_avatar'), $fileName);

            $donnees->setAvatar($fileName);

			$em->persist($donnees);
			$em->flush();

			$this->addFlash('registerOk', 'Utilisateur bien enregistré');
			return $this->redirectToRoute('renaud_blog_homepage');
		}		

		return $this->render('renaudBlogBundle:Blog:register.html.twig', array('form' => $form->createView(),'user'=>$session->get('user')));
	}

	// Permet de modifier les informations du profil
	public function editProfileAction (Request $request, $id) {

		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();
    	$user = $em->getRepository('renaudBlogBundle:User')->find($id);

    	$user->setAvatar(null);

    	$form = $this->createForm(UserType::class, $user);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
			$donnees->setPassword(password_hash($donnees->getPassword(),PASSWORD_DEFAULT));
			
			$em->persist($donnees);
			$em->flush();
			
			$session->set('user', $donnees->getMail());

			return $this->redirectToRoute('renaud_blog_homepage');
		}

    	return $this->render('renaudBlogBundle:Blog:editProfile.html.twig', array('form'=>$form->createView(), 'user'=>$session->get('user')));
	}

	// Déconnexion de la session
	public function disconnectAction (Request $request) {

		$session = $request->getSession();
		$session->set('user', null);

		return $this->redirectToRoute('renaud_blog_homepage');
	}
}