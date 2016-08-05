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

	public function indexAction (Request $request) {
		$articles = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->findAll();

		$session = $request->getSession();

		return $this->render('renaudBlogBundle:Blog:index.html.twig', array('articles' => $articles, 'vus'=>$session->get('vus')));
	}

	public function adminAction () {
		return $this->render('renaudBlogBundle:Blog:admin.html.twig');
	}

	public function adminArticlesAction () {
		return $this->render('renaudBlogBundle:Blog:adminArticles.html.twig');
	}

	public function adminComAction () {
		return $this->render('renaudBlogBundle:Blog:adminCom.html.twig');
	}

	public function addAction (Request $request) {
		$article = new Article();

		$form = $this->createForm(ArticleType::class, $article);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
			$donnees->setDate(new \DateTime());

			$em = $this->getDoctrine()->getManager();
			$em->persist($donnees);
			$em->flush();
		
			$this->addFlash('addOk', 'Article bien enregistré');
  			return $this->redirectToRoute('renaud_blog_homepage');
		}

		return $this->render('renaudBlogBundle:Blog:add.html.twig', array(
            'form' => $form->createView(),
        )); 
    }

	public function loginAction (Request $request) {
		$login = new Login;

		$form = $this->createForm(LoginType::class, $login);

		$form->handleRequest($request);

		return $this->render('renaudBlogBundle:Blog:login.html.twig');
	}

	public function registerAction (Request $request) {
		$user = new User();

		$form = $this->createForm(UserType::class, $user);

		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid()) {
			$donnees = $form->getData();
  			$donnees->setPassword(password_hash($donnees->getPassword(),PASSWORD_DEFAULT));

			$em = $this->getDoctrine()->getManager();
			$em->persist($donnees);
			$em->flush();

			$this->addFlash('registerOk', 'Utilisateur bien enregistré');
			return $this->redirectToRoute('renaud_blog_homepage');
		}		

		return $this->render('renaudBlogBundle:Blog:register.html.twig', array('form' => $form->createView(),));
	}

	public function viewAction ($id, Request $request) {
		$article = $this->getDoctrine()
		->getRepository('renaudBlogBundle:Article')
		->find($id);

		$session = $request->getSession();

		$articlesVus = $session->get('vus');
		$articlesVus[$id] = $id;
		$session->set('vus', $articlesVus);

		return $this->render('renaudBlogBundle:Blog:view.html.twig', array('article' => $article));
	}
}