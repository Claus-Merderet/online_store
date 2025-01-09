<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class OrderController extends AbstractController
{
    public function create(Request $request): void
    {
        //$productDTO = new OrederDTO(json_decode($request->getContent(), true));
    }

}
