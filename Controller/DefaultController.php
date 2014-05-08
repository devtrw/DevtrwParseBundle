<?php

namespace Devtrw\ParseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DevtrwParseBundle:Default:index.html.twig', array('name' => $name));
    }
}
