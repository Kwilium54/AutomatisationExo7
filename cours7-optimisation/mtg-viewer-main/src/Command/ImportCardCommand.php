<?php

namespace App\Command;

use App\Entity\Artist;
use App\Entity\Card;
use App\Repository\ArtistRepository;
use App\Repository\CardRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressIndicator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'import:card',
    description: 'Add a short description for your command',
)]
class ImportCardCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly LoggerInterface        $logger,
        private array                           $csvHeader = []
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        ini_set('memory_limit', '2G');
        $io = new SymfonyStyle($input, $output);
        $filepath = __DIR__ . '/../../data/cards.csv';

        $start = microtime(true);
        $this->logger->info('Import started', ['file' => $filepath]);

        $handle = fopen($filepath, 'r');
        if ($handle === false) {
            $this->logger->error('Import failed: file not found', ['file' => $filepath]);
            $io->error('File not found');
            return Command::FAILURE;
        }

        $total = 0;
        $imported = 0;
        $this->csvHeader = fgetcsv($handle);

        // array_flip() transforme le tableau ["uuid1", "uuid2", ...] en {"uuid1": 0, "uuid2": 1, ...}
        // Cela permet d'utiliser isset() qui est O(1) au lieu de in_array() qui est O(n)
        // Sans cela, avec 30 000 UUIDs en base, chaque ligne du CSV déclenchait un parcours complet du tableau
        $uuidInDatabase = array_flip(
            $this->entityManager->getRepository(Card::class)->getAllUuids()
        );

        $progressIndicator = new ProgressIndicator($output);
        $progressIndicator->start('Importing cards...');

        while (($row = $this->readCSV($handle)) !== false) {
            // Ligne malformée, on la saute
            if ($row === null) {
                continue;
            }
            $total++;

            if (!isset($uuidInDatabase[$row['uuid']])) {
                $this->addCard($row);
                // On ajoute l'UUID au dictionnaire pour éviter les doublons dans le même CSV
                $uuidInDatabase[$row['uuid']] = true;
                $imported++;
            }

            if ($total % 2000 === 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
                $progressIndicator->advance();
            }
        }
        // Toujours flush en sortie de boucle
        $this->entityManager->flush();
        $progressIndicator->finish('Importing cards done.');

        fclose($handle);

        $end = microtime(true);
        $timeElapsed = $end - $start;
        $this->logger->info('Import finished', [
            'total_rows' => $total,
            'imported' => $imported,
            'skipped' => $total - $imported,
            'duration_seconds' => round($timeElapsed, 2),
        ]);
        $io->success(sprintf(
            'Processed %d rows: %d imported, %d skipped (already in DB) in %.2f seconds',
            $total,
            $imported,
            $total - $imported,
            $timeElapsed
        ));
        return Command::SUCCESS;
    }

    private function readCSV(mixed $handle): array|null|false
    {
        $row = fgetcsv($handle);
        if ($row === false) {
            return false;
        }
        // Ignore les lignes malformées (nombre de colonnes différent du header)
        if (count($row) !== count($this->csvHeader)) {
            return null;
        }
        return array_combine($this->csvHeader, $row);
    }

    private function addCard(array $row)
    {
        $uuid = $row['uuid'];

        $card = new Card();
        $card->setUuid($uuid);
        $card->setManaValue($row['manaValue']);
        $card->setManaCost($row['manaCost']);
        $card->setName($row['name']);
        $card->setRarity($row['rarity']);
        $card->setSetCode($row['setCode']);
        $card->setSubtype($row['subtypes']);
        $card->setText($row['text']);
        $card->setType($row['type']);
        $this->entityManager->persist($card);

    }
}
