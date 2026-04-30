<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'erp:test-mail', description: 'Test ERP email sending')]
class TestMailCommand extends Command
{
    public function __construct(
        private MailerInterface $mailer,
        private string $from
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from($this->from)
            ->to($this->from)
            ->subject('✅ FarmIA Desk — Test email')
            ->text("L'envoi d'email fonctionne correctement depuis FarmIA Desk.");

        try {
            $this->mailer->send($email);
            $output->writeln('<info>Email envoyé avec succès à ' . $this->from . '</info>');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Échec : ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
