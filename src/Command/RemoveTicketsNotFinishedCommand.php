<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\TicketAbandoned;
use App\Repository\TicketAbandonedRepository;
use App\Repository\TicketRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:remove-tickets-not-finished',
    description: 'Remove old pending tickets',
)]
class RemoveTicketsNotFinishedCommand extends Command
{
    private const TIME_MODIFIER = '-5 minutes';
    private TicketRepository $ticketRepository;
    private TicketAbandonedRepository $ticketAbandonedRepository;

    public function __construct(
        string $name = null,
        TicketRepository $ticketRepository,
        TicketAbandonedRepository $ticketAbandonedRepository
    ) {
        parent::__construct($name);
        $this->ticketRepository = $ticketRepository;
        $this->ticketAbandonedRepository = $ticketAbandonedRepository;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $maxDate = (new \DateTimeImmutable())->modify(self::TIME_MODIFIER);
        $criteria = (new Criteria())
            ->where(Criteria::expr()->eq('endDate', null))
            ->andWhere(Criteria::expr()->lt('startDate', $maxDate));

        $ticketsToDelete = $this->ticketRepository->matching($criteria);
        $toRemove = count($ticketsToDelete);
        foreach ($ticketsToDelete AS $ticketToDelete) {
            $ticketAbandoned = TicketAbandoned::fromTicket($ticketToDelete);
            $this->ticketAbandonedRepository->add($ticketAbandoned, true);
            $this->ticketRepository->remove($ticketToDelete, true);
        }
        $io->success($toRemove . ' old tickets removed');

        return Command::SUCCESS;
    }
}
