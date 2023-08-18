<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CountryBordersService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FindPathController extends AbstractController
{

    public function __construct(private CountryBordersService $countryBordersService)
    {
    }

    #[Route('/routing/{orig}/{dest}', name: 'app_find_path')]
    public function index(string $orig, string $dest): JsonResponse
    {

        try {
            return $this->json([
                'rouote' => $this->countryBordersService->findPath($orig, $dest)
            ]);
        } catch (\InvalidArgumentException $exceptiosn) {
            return $this->json(null, Response::HTTP_BAD_REQUEST);
        }
    }
}
