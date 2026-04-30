<?php

namespace App\Command;

use App\Service\ERP\ERPEmailService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'erp:test-stock-alert', description: 'Test ERP stock alert emails')]
class TestStockAlertCommand extends Command
{
    public function __construct(
        private ERPEmailService $emailService,
        private string $alertEmail
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('Testing critical stock alert...');
        $this->emailService->sendStockCritiqueAlert(
            'QSD (test)',
            3,
            3,   // current stock
            5,   // seuil
            $this->alertEmail,
            'Test User'
        );
        $output->writeln('<info>✅ Critical stock alert sent to ' . $this->alertEmail . '</info>');

        $output->writeln('Testing zero stock alert...');
        $this->emailService->sendStockZeroAlert(
            'QSD (test)',
            3,
            $this->alertEmail,
            'Test User'
        );
        $output->writeln('<info>✅ Zero stock alert sent to ' . $this->alertEmail . '</info>');

        return Command::SUCCESS;
    }
}
