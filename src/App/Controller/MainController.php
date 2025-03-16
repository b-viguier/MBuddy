<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class MainController extends AbstractController
{
    public function index(): Response
    {
        return $this->render(
            'main/index.html.twig',
        );
    }

    public function about(): Response
    {
        return new Response('<html><body><h1>About</h1></body></html>');
    }
}
