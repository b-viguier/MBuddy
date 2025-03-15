<?php

declare(strict_types=1);

namespace Bveing\MBuddy\App\Controller;

use Symfony\Component\HttpFoundation\Response;

class MainController
{
    public function index(): Response
    {
        return new Response('<html><body><h1>Hello World</h1></body></html>');
    }

    public function about(): Response
    {
        return new Response('<html><body><h1>About</h1></body></html>');
    }
}
