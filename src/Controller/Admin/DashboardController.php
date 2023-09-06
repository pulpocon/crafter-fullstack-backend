<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Invoice;
use App\Entity\PaypalDetails;
use App\Entity\Ticket;
use App\Entity\TicketAbandoned;
use App\Entity\TicketPlan;
use App\Repository\PaypalDetailsRepository;
use App\Repository\TicketPlanRepository;
use App\Repository\TicketRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends AbstractDashboardController
{
    private const SPEAKER = 'Speaker';
    private const SPONSORS = '/(Boardfy|Sponsor|Codely)/';
    private const CHARLAS = 'charlas';
    private const CHARLAS_COMIDA = 'charlas+comida';
    private const COMIDA = 'management';
    private const CQRS = 'crafter-full';
    private const CQRS_FRIDAY = 'management';

    public function __construct(
        private TicketPlanRepository $ticketPlanRepository,
        private TicketRepository $ticketRepository,
        private PaypalDetailsRepository $paypalDetailsRepository,
        private ChartBuilderInterface $chartBuilder
    ) {}
    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        return $this->render('admin/my-dashboard.html.twig', [
            'revenue' => $this->getRevenue(),
            'attendees' => $this->getAttendees(),
            'information' => $this->getInformation(),
            'percentTicketsSold' => $this->getPercentOfTicketsSold(),
            'salesPerDay' => $this->getSalesPerDay(),
        ]);
    }

    private function getRevenue() : array
    {
        $payments = $this->paypalDetailsRepository->findAll();
        $revenue = [];
        $total = [
            'name' => 'Total',
            'tickets' => 0,
            'paid' => 0,
            'fee' => 0,
            'net' => 0,
            'tax' => 0,
            'cash' => 0,
        ];

        $ticketsRevoked = [];

        foreach ($payments as $payment) {

            /** @var Ticket $ticket */
            $ticket = $this->ticketRepository->findOneBy(['reference' => $payment->getReference()]);
            if ($ticket->isRevoked()) {
                $ticketsRevoked[$ticket->getId()]['paid'] = (float) $payment->getPaid();
                $ticketsRevoked[$ticket->getId()]['fee'] = (float) $payment->getFee();
                $ticketsRevoked[$ticket->getId()]['net'] = (float) $payment->getNetAmount();
                $ticketsRevoked[$ticket->getId()]['tax'] = $ticket->getTicketPlan()->getTax();
                continue;
            }

            $ticketPlan = $ticket->getTicketPlan();

            if (null === $ticketPlan) {
                continue;
            }

            if (!array_key_exists($ticketPlan->getId(), $revenue)) {
                $revenue[$ticketPlan->getId()] = [
                    'name' => $ticketPlan->getName(),
                    'tickets' => 0,
                    'paid' => 0,
                    'fee' => 0,
                    'net' => 0,
                    'tax' => 0,
                    'cash' => 0,
                ];
            }

            $revenue[$ticketPlan->getId()]['tickets']++;
            if (
                $ticket->getUpgradedFrom() instanceof Ticket &&
                array_key_exists($ticket->getUpgradedFrom()->getId(), $ticketsRevoked)
            ) {
                $paymentRevoked = $ticketsRevoked[$ticket->getUpgradedFrom()->getId()];
                $cash = (float) $payment->getNetAmount() + $paymentRevoked['net']
                    - $ticket->getTicketPlan()->getTax() - $paymentRevoked['tax'];

                $revenue[$ticketPlan->getId()]['paid'] += (float) $payment->getPaid() + $paymentRevoked['paid'];
                $revenue[$ticketPlan->getId()]['fee'] += (float) $payment->getFee() + $paymentRevoked['fee'];
                $revenue[$ticketPlan->getId()]['net'] += (float) $payment->getNetAmount() + $paymentRevoked['net'];
                $revenue[$ticketPlan->getId()]['tax'] += $ticket->getTicketPlan()->getTax();
                $revenue[$ticketPlan->getId()]['cash'] += max($cash, 0);

                $total['tickets']++;
                $total['paid'] += (float) $revenue[$ticketPlan->getId()]['paid'];
                $total['fee'] += (float) $revenue[$ticketPlan->getId()]['fee'];
                $total['net'] += (float) $revenue[$ticketPlan->getId()]['net'];
                $total['tax'] += $revenue[$ticketPlan->getId()]['tax'];
                $total['cash'] += max($cash, 0);
            } else {
                $cash = (float) $payment->getNetAmount() - $ticket->getTicketPlan()->getTax();

                $revenue[$ticketPlan->getId()]['paid'] += (float) $payment->getPaid();
                $revenue[$ticketPlan->getId()]['fee'] += (float) $payment->getFee();
                $revenue[$ticketPlan->getId()]['net'] += (float) $payment->getNetAmount();
                $revenue[$ticketPlan->getId()]['tax'] += $ticket->getTicketPlan()->getTax();
                $revenue[$ticketPlan->getId()]['cash'] += max($cash, 0);

                $total['tickets']++;
                $total['paid'] += (float) $payment->getPaid();
                $total['fee'] += (float) $payment->getFee();
                $total['net'] += (float) $payment->getNetAmount();
                $total['tax'] += $ticket->getTicketPlan()->getTax();
                $total['cash'] += max($cash, 0);
            }

        }
        $revenue[] = $total;
        return $revenue;
    }

    private function getAttendees() : array
    {
        $tickets = $this->ticketRepository->findBy(['revoked' => false]);
        $attendees = [];
        foreach (TicketPlan::AVAILABLE_ACCESSES as $name => $key) {
            $nameToAdd = $name;
            if ($key === self::CHARLAS) {
                $nameToAdd = ucfirst(self::CHARLAS);
            }

            if ($key === self::CHARLAS_COMIDA) {
                $nameToAdd = self::COMIDA;
            }

            $attendees[$key] = [
                'name' => $nameToAdd,
                'speakers' => 0,
                'sponsors' => 0,
                'paid' => 0,
                'attendees' => 0,
            ];
        }

        foreach ($tickets as $ticket) {

            $ticketPlan = $ticket->getTicketPlan();
            if (null === $ticketPlan) {
                continue;
            }

            foreach ($ticketPlan->getAccessTo() as $accessTo) {
                if ($accessTo === self::CHARLAS_COMIDA) {
                    $attendees[self::CHARLAS]['attendees']++;
                    if (str_contains($ticketPlan->getName(), self::SPEAKER)) {
                        $attendees[self::CHARLAS]['speakers']++;
                    } elseif (preg_match(self::SPONSORS, $ticketPlan->getName())) {
                        $attendees[self::CHARLAS]['sponsors']++;
                    } else {
                        $attendees[self::CHARLAS]['paid']++;
                    }
                }

                $attendees[$accessTo]['attendees']++;
                if (str_contains($ticketPlan->getName(), self::SPEAKER)) {
                    $attendees[$accessTo]['speakers']++;
                } elseif (preg_match(self::SPONSORS, $ticketPlan->getName())) {
                    $attendees[$accessTo]['sponsors']++;
                } else {
                    $attendees[$accessTo]['paid']++;
                }
            }
        }

        $attendees['totalViernes'] = [
            'name' => 'Total asistentes el viernes',
            'attendees' => $attendees['crafter']['attendees'] + $attendees['devops']['attendees'],
            'speakers' => $attendees['crafter']['speakers'] + $attendees['devops']['speakers'],
            'sponsors' => $attendees['crafter']['sponsors'] + $attendees['devops']['sponsors'],
            'paid' => $attendees['crafter']['paid'] + $attendees['devops']['paid'],
        ];

        $attendees['totalSabado'] = [
            'name' => 'Total asistentes el sábado',
            'attendees' => $attendees[self::CHARLAS]['attendees'],
            'speakers' => $attendees[self::CHARLAS]['speakers'],
            'sponsors' => $attendees[self::CHARLAS]['sponsors'],
            'paid' => $attendees[self::CHARLAS]['paid'],
        ];

        $attendees['total'] = [
            'name' => 'Total Asistentes',
            'attendees' => $attendees[self::CHARLAS]['attendees'] + $attendees[self::CQRS]['attendees']
                + $attendees[self::CQRS_FRIDAY]['attendees'],
            'speakers' => $attendees[self::CHARLAS]['speakers'] + $attendees[self::CQRS]['speakers']
                + $attendees[self::CQRS_FRIDAY]['speakers'],
            'sponsors' => $attendees[self::CHARLAS]['sponsors'] + $attendees[self::CQRS]['sponsors']
                + $attendees[self::CQRS_FRIDAY]['sponsors'],
            'paid' => $attendees[self::CHARLAS]['paid'] + $attendees[self::CQRS]['attendees']
                + $attendees[self::CQRS_FRIDAY]['attendees'],
        ];
        return $attendees;
    }

    private function getInformation() : array
    {
        $tickets = $this->ticketRepository->findBy(['revoked' => false]);
        $information = [
            'hombre' => ['xs' => 0, 's' => 0, 'm' => 0, 'l' => 0, 'xl' => 0, 'xxl' => 0, 'xxxl' => 0],
            'mujer' => ['xs' => 0, 's' => 0, 'm' => 0, 'l' => 0, 'xl' => 0, 'xxl' => 0, 'xxxl' => 0],
            'Omnivoro' => 0,
            'Vegetariano' => 0,
            'Vegano' => 0
        ];

        foreach ($tickets as $ticket) {
            $information[$ticket->getShirtType()][$ticket->getShirtSize()]++;
            $information[$ticket->getFeeding()]++;
        }

        return $information;
    }

    private function getColor($num) :array
    {
        $hash = md5('color' . $num); // modify 'color' to get a different palette
        return array(
            hexdec(substr($hash, 0, 2)), // r
            hexdec(substr($hash, 2, 2)), // g
            hexdec(substr($hash, 4, 2))); //b
    }

    public function getPercentOfTicketsSold(): Chart
    {
        $ticketPlans = $this->ticketPlanRepository->findAll();
        $label = [];
        $data = [];
        $background = [];
        $border = [];
        foreach ($ticketPlans as $key => $ticketPlan) {
            $rgb = implode(',', $this->getColor($key));
            $label[] = $ticketPlan->getName();
            $data[] = $ticketPlan->getTicketsSold() / $ticketPlan->getQuantity() * 100;
            $background[] = 'rgb(' . $rgb . ', 0.2)';
            $border[] = 'rgb(' . $rgb . ')';
        }

        return $this->chartBuilder->createChart(Chart::TYPE_BAR)->setData([
            'labels' => $label,
            'datasets' => [[
                'label' => '% de entradas vendidas',
                'data' => $data,
                'backgroundColor' => $background,
                'borderColor' => $border,
                'borderWith' => 1
            ]]
        ]);
    }

    private function getSalesPerDay() : Chart
    {
        $salesPerDay = $this->ticketRepository->salesPerDay();
        $data = [];
        $borderColor = [];
        $labels = [];
        foreach ($salesPerDay as $key => $day) {
            $labels[] = $day['date'];
            $data[] = $day['total'];
            $borderColor[] = $this->getColor($key);
        }

        return $this->chartBuilder->createChart(Chart::TYPE_LINE)->setData([
            'labels' => $labels,
            'datasets' => [[
                'label' => 'Ventas por día',
                'data' => $data,
                'borderColor' => [] === $borderColor ? '' :
                    'rgb(' . implode(',', $borderColor[random_int(0, count($borderColor) - 1)]) . ')',
                'fill' => false,
                'tension' => 0.1
            ]]
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            // the name visible to end users
            ->setTitle('ACME Corp.')
            // you can include HTML contents too (e.g. to link to an image)
            ->setTitle('<img src="/image/pulpoCon22.png" alt="Pulpo Con"> ACME <span class="text-small">Corp.</span>')

            // by default EasyAdmin displays a black square as its default favicon;
            // use this method to display a custom favicon: the given path is passed
            // "as is" to the Twig asset() function:
            // <link rel="shortcut icon" href="{{ asset('...') }}">
            ->setFaviconPath('favicon.svg')

            // the domain used by default is 'messages'
            ->setTranslationDomain('my-custom-domain')

            // there's no need to define the "text direction" explicitly because
            // its default value is inferred dynamically from the user locale
            ->setTextDirection('ltr')

            // set this option if you prefer the page content to span the entire
            // browser width, instead of the default design which sets a max width
            ->renderContentMaximized()

            // set this option if you prefer the sidebar (which contains the main menu)
            // to be displayed as a narrow column instead of the default expanded design
            ->renderSidebarMinimized()

            // by default, users can select between a "light" and "dark" mode for the
            // backend interface. Call this method if you prefer to disable the "dark"
            // mode for any reason (e.g. if your interface customizations are not ready for it)
            ->disableDarkMode()

            // by default, all backend URLs are generated as absolute URLs. If you
            // need to generate relative URLs instead, call this method
            ->generateRelativeUrls();
    }

    public function configureMenuItems(): iterable
    {
        return [
            MenuItem::linkToDashboard('Dashboard', 'fa fa-home'),

            MenuItem::section('Configuración'),
            MenuItem::linkToCrud('Ticket plan', 'fa fa-tags', TicketPlan::class),
            MenuItem::linkToCrud('Tickets', 'fa fa-ticket', Ticket::class),
            MenuItem::linkToCrud('Invoice', 'fa fa-file-invoice', Invoice::class),
            MenuItem::linkToCrud('Abandoned Tickets', 'fa-solid fa-ticket-simple', TicketAbandoned::class),
            MenuItem::linkToCrud('Paypal', 'fa-brands fa-paypal', PaypalDetails::class),
        ];
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()->showEntityActionsInlined();
    }

    public function configureAssets(): Assets
    {
        return (parent::configureAssets())->addWebpackEncoreEntry('app');
    }
}
