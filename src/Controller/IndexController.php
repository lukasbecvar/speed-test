<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * Class IndexController
 *
 * Main controller for show main page component
 *
 * @package App\Controller
 */
class IndexController extends AbstractController
{
    /**
     * Show main page
     *
     * @return Response The main page response
     */
    #[Route('/', methods: ['GET'], name: 'main_index')]
    public function index(): Response
    {
        return $this->render('speedtest.twig');
    }
}
