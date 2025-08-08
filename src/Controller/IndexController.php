<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
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
    public function index(Request $request): Response
    {
        $ipAddress = $request->getClientIp();
        $isPrivate = filter_var($ipAddress, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;

        return $this->render('speedtest.twig', [
            'ipAddress' => $ipAddress,
            'isPrivate' => $isPrivate,
        ]);
    }

    /**
     * Ping test endpoint
     *
     * @return Response
     */
    #[Route('/ping', methods: ['HEAD'], name: 'ping')]
    public function ping(): Response
    {
        return new Response();
    }

    /**
     * Download speed test endpoint
     *
     * @return Response
     */
    #[Route('/download', methods: ['GET'], name: 'download')]
    public function download(): Response
    {
        $response = new Response(str_repeat('0', 1024 * 1024 * 2)); // 2MB
        $response->headers->set('Content-Type', 'application/octet-stream');
        return $response;
    }

    /**
     * Upload speed test endpoint
     *
     * @return Response
     */
    #[Route('/upload', methods: ['POST'], name: 'upload')]
    public function upload(): Response
    {
        return new Response();
    }
}
