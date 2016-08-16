<?php

namespace renaud\BlogBundle\Controller;

use renaud\BlogBundle\Form\ArticleType;
use renaud\BlogBundle\Form\UserType;
use renaud\BlogBundle\Form\LoginType;
use renaud\BlogBundle\Entity\Article;
use renaud\BlogBundle\Entity\User;
use renaud\BlogBundle\Entity\Login;
use renaud\BlogBundle\Entity\ArticleLu;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage;

class BlogController extends Controller {

	// Page d'accueil
	// **************

	public function indexAction (Request $request) {

		$session = $request->getSession();

		$articles = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->findBy(array('publie'=>1), array('date'=>'DESC'), 4);
		
		return $this->render('renaudBlogBundle:Blog:index.html.twig', array('articles' => $articles, 'vus'=>$session->get('vus')));
	}

	// Liste de tout les articles publies
	// **********************************

	/**
   	* @Security("has_role('ROLE_USER')")
   	*/
	public function listeArticlesAction (Request $request) {

		$session = $request->getSession();

		$articles = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->findAll();

		return $this->render('renaudBlogBundle:Blog:articles.html.twig', array('articles' => $articles, 'vus'=>$session->get('vus')));
	}

	// Affichage d'un seul article
	// ***************************

	/**
   	* @Security("has_role('ROLE_USER')")
   	*/
	public function viewAction ($id, Request $request) {

		$session = $request->getSession();

		$em = $this->getDoctrine()->getManager();

		$articleLu = new ArticleLu();

		$article = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->find($id);

		$user = $this->get('security.token_storage')->getToken()->getUser();
 		
 		$articleLu->setArticle($article);
 		$articleLu->setUser($user);
 		$articleLu->setLu(true);

		/*$articlesVus = $session->get('vus');
		$articlesVus[$id] = $id;
		$session->set('vus', $articlesVus);*/

		$em->persist($articleLu);
		$em->flush();

		return $this->render('renaudBlogBundle:Blog:view.html.twig', array('article' => $article));
	}

	// Back-office
	// ***********

	/**
   	* @Security("has_role('ROLE_ADMIN')")
   	*/
	public function adminArticlesAction (Request $request) {
		$session = $request->getSession();
		
		$articles = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->findAll();

		return $this->render('renaudBlogBundle:Blog:adminArticles.html.twig', array('articles'=>$articles));
	}

	// Ajout d'un article
	// ******************

	/**
   	* @Security("has_role('ROLE_ADMIN')")
   	*/
	public function addAction (Request $request) {
	
		$session = $request->getSession();

		$article = new Article();

		$form = $this->createForm(ArticleType::class, $article);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
			$donnees->setDate(new \DateTime());
			$donnees->setPublie(0);

			$security = $this->get('security.token_storage');
			$token = $security->getToken();
			$user = $token->getUser();

			$donnees->setAuteur($user->getUsername());
			$em = $this->getDoctrine()->getManager();
			
 			$em->persist($donnees);
			$em->flush();
		
			$this->addFlash('addOk', 'Article bien enregistré');
  			return $this->redirectToRoute('renaud_blog_homepage');
		}

		return $this->render('renaudBlogBundle:Blog:add.html.twig', array(
            'form' => $form->createView())); 
    }	

    // Modification d'un article
    // *************************

	/**
   	* @Security("has_role('ROLE_ADMIN')")
   	*/
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

    	return $this->render('renaudBlogBundle:Blog:modify.html.twig', array('form'=>$form->createView()));
	}

	// Suppression d'un article
	// ************************

	/**
   	* @Security("has_role('ROLE_ADMIN')")
   	*/
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

	// Publication ou dépublication d'un article
	// *****************************************

	/**
   	* @Security("has_role('ROLE_ADMIN')")
   	*/
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
			$article->setDate(new \DateTime());
		}

		$em->flush();

		return $this->redirectToRoute('renaud_blog_homepage');
	}
}