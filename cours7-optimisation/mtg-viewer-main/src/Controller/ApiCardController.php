<?php

namespace App\Controller;

use App\Entity\Artist;
use App\Entity\Card;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use OpenApi\Attributes as OA;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/card', name: 'api_card_')]
#[OA\Tag(name: 'Card', description: 'Routes for all about cards')]
class ApiCardController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CardRepository $cardRepository,
        private readonly LoggerInterface $logger
    ) {
    }
    #[Route('/all', name: 'List all cards', methods: ['GET'])]
    #[OA\Put(description: 'Return all cards in the database')]
    #[OA\Response(response: 200, description: 'List all cards')]
    public function cardAll(): Response
    {
        $this->logger->info('API call: GET /api/card/all');
        $cards = $this->entityManager->getRepository(Card::class)->findAll();
        return $this->json($cards);
    }

    #[Route('/search', name: 'Search cards', methods: ['GET'])]
    #[OA\Get(description: 'Search cards by name (max 20 results)')]
    #[OA\Parameter(name: 'name', description: 'Name to search for (min 3 characters)', in: 'query', required: true, schema: new OA\Schema(type: 'string'))]
    #[OA\Response(response: 200, description: 'List of matching cards (max 20)')]
    #[OA\Response(response: 400, description: 'Query too short (less than 3 characters)')]
    public function cardSearch(Request $request): Response
    {
        $name = $request->query->get('name', '');
        $this->logger->info('API call: GET /api/card/search', ['name' => $name]);

        if (strlen($name) < 3) {
            return $this->json(['error' => 'Search query must be at least 3 characters'], 400);
        }

        $cards = $this->cardRepository->searchByName($name);
        return $this->json($cards);
    }

    #[Route('/{uuid}', name: 'Show card', methods: ['GET'])]    #[OA\Parameter(name: 'uuid', description: 'UUID of the card', in: 'path', required: true, schema: new OA\Schema(type: 'integer'))]
    #[OA\Put(description: 'Get a card by UUID')]
    #[OA\Response(response: 200, description: 'Show card')]
    #[OA\Response(response: 404, description: 'Card not found')]
    public function cardShow(string $uuid): Response
    {
        $this->logger->info('API call: GET /api/card/{uuid}', ['uuid' => $uuid]);
        $card = $this->entityManager->getRepository(Card::class)->findOneBy(['uuid' => $uuid]);
        if (!$card) {
            $this->logger->warning('Card not found', ['uuid' => $uuid]);
            return $this->json(['error' => 'Card not found'], 404);
        }
        return $this->json($card);
    }
}
