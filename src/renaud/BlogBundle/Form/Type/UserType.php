<?php
namespace renaud\BlogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('Username', TextType::class)
			->add('Mail', EmailType::class)
			->add('Nom', TextType::class)
			->add('Prenom', TextType::class)
			->add('Password', RepeatedType::class, array(
				'type' => PasswordType::class,
				'invalid_message' => 'Les mots de passes doivent être identiques', 
				'options' => array ('attr' => array('class' => 'password-field')),
				'required' => true,
				'first_options'  => array('label' => 'Mot de passe'),
    			'second_options' => array('label' => 'Confirmer le mot de passe'),
				))
			->add('Avatar', FileType::class)
			->add('Creer', SubmitType::class, array('label' => 'S\'enregistrer'));
    }
}
