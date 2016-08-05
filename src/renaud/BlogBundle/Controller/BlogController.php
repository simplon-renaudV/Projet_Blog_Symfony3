<?php

namespace renaud\BlogBundle\Controller;

use renaud\BlogBundle\Entity\Article;
use renaud\BlogBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
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

		$form = $this->createFormBuilder($article)
			->add('Titre', TextType::class)
			->add('Auteur', TextType::class)
			->add('Contenu', TextareaType::class)
			->add('Publier', SubmitType::class, array('label' => 'Publier'))
			->getForm();

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

	public function loginAction () {
		return $this->render('renaudBlogBundle:Blog:login.html.twig');
	}

	public function registerAction (Request $request) {
		$user = new User();

		$form = $this->createFormBuilder($user)
			->add('Mail', EmailType::class)
			->add('Nom', TextType::class)
			->add('Prenom', TextType::class)
			->add('Password', RepeatedType::class, array(
				'type' => PasswordType::class,
				'invalid_message' => 'Les mots de passes doivent etre identiques', 
				'options' => array ('attr' => array('class' => 'password-field')),
				'required' => true,
				'first_options'  => array('label' => 'Mot de passe'),
    			'second_options' => array('label' => 'Confirmer le mot de passe'),
				))
			->add('Avatar', FileType::class)
			->add('Creer', SubmitType::class, array('label' => 'S\'enregistrer'))
			->getForm();

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