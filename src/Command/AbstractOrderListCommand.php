<?php

namespace App\Command;

use App\Entity\LineItem;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\Bundle\DoctrineBundle\Middleware\BacktraceDebugDataHolder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Stopwatch\Stopwatch;

abstract class AbstractOrderListCommand extends Command
{
    public function __construct(
        protected readonly OrderRepository $orderRepository,
        private readonly Stopwatch $stopwatch,
        #[Autowire(service: 'doctrine.debug_data_holder')]
        private readonly BacktraceDebugDataHolder $debugDataHolder,
    ) {
        parent::__construct();
    }

    abstract protected function isShowTotal(): bool;

    /**
     * @return list<Order>
     */
    abstract protected function findOrders(InputInterface $input): array;

    /**
     * @return iterable<LineItem>
     */
    abstract protected function extractLineItems(Order $order): iterable;

    protected function configure(): void
    {
        $this->addOption('show-orders', null, InputOption::VALUE_NONE, '注文情報を表示する');
        $this->addOption('show-sql', null, InputOption::VALUE_NONE, 'SQLログを表示する');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->stopwatch->start('orders');
        $orders = $this->findOrders($input);
        $this->stopwatch->stop('orders');

        $this->stopwatch->start('line_items');
        $renderingItems = [];
        foreach ($orders as $order) {
            $renderingItem = [
                'orderId' => $order->getId(),
                'orderedAt' => $order->getOrderedAt()->format('Y-m-d H:i:s'),
            ];

            if ($this->isShowTotal()) {
                $renderingItem['total'] = $order->getTotal();
            }

            $lineItems = $this->extractLineItems($order);

            $lineItemsTable = [];
            foreach ($lineItems as $lineItem) {
                $lineItemsTable[] = [
                    'Line Number' => $lineItem->getLineNumber(),
                    'Product Name' => $lineItem->getProduct()->getName(),
                    'Quantity' => $lineItem->getQuantity(),
                    'Unit Price' => $lineItem->getUnitPrice(),
                    'Subtotal' => $lineItem->getSubtotal(),
                ];
            }
            $renderingItem['lineItemsTable'] = $lineItemsTable;
            $renderingItems[] = $renderingItem;
        }
        $this->stopwatch->stop('line_items');

        if ($input->getOption('show-orders')) {
            $this->renderOrders($renderingItems, $io);
        }

        $this->renderSql($io, $input->getOption('show-sql'));

        $this->renderStopwatchData($io);

        return Command::SUCCESS;
    }

    /**
     * @param list<array<string, mixed>> $renderingItems
     */
    private function renderOrders(array $renderingItems, SymfonyStyle $io): void
    {
        foreach ($renderingItems as $item) {
            $io->section(sprintf('Order ID: %d', $item['orderId']));
            $io->text(sprintf('Ordered At: %s', $item['orderedAt']));

            if (isset($item['total'])) {
                $io->text(sprintf('Total: %d', $item['total']));
            }

            $io->table(
                ['Line Number', 'Product Name', 'Quantity', 'Unit Price', 'Subtotal'],
                $item['lineItemsTable']
            );
        }
    }

    private function renderSql(SymfonyStyle $io, bool $showSql): void
    {
        $sqlTable = [];
        $number = 0;
        $totalMsec = 0.0;
        foreach ($this->debugDataHolder->getData() as $manager => $data) {
            foreach ($data as $datum) {
                $sql = $datum['sql'];
                $msec = $datum['executionMS'];
                $sqlTable[] = [
                    '#' => ++$number,
                    'Time (µs)' => round($msec * 1000, 4),
                    'SQL' => $sql,
                ];
                $totalMsec += $msec;
            }
        }

        $io->section('Executed SQL Statements');

        if ($showSql) {
            $io->table(
                ['#', 'Time (µs)', 'SQL'],
                $sqlTable
            );
        } else {
            $io->text(sprintf('Total SQL Statements Executed: %d', $number));
        }

        $io->text(sprintf('Total Execution Time: %.4f µs', $totalMsec * 1000));
    }

    private function renderStopwatchData(SymfonyStyle $io): void
    {
        $events = [
            'orders' => $this->stopwatch->getEvent('orders'),
            'line_items' => $this->stopwatch->getEvent('line_items'),
        ];

        $stopwatchTable = [];
        $totalDuration = 0;

        foreach ($events as $name => $event) {
            $stopwatchTable[] = [
                'Name' => $name,
                'Duration (ms)' => $event->getDuration(),
                'Memory (MB)' => round($event->getMemory() / 1024 / 1024, 4),
            ];
            $totalDuration += $event->getDuration();
        }

        $io->section('Stopwatch Data');
        $io->table(['Name', 'Duration (ms)', 'Memory (MB)'], $stopwatchTable);
        $io->text(sprintf('Total Duration: %d ms', $totalDuration));
    }
}
