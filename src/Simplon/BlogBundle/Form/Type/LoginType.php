<?php
namespace Simplon\BlogBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class LoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('Username', TextType::class, array('attr' => array(
        'placeholder' => 'Pseudo',
    )))
			->add('Password', PasswordType::class, array('attr' => array(
                'placeholder' => 'Mot de passe',
            )))
			->add('Creer', SubmitType::class, array('label' => 'Login'));
    }
}
