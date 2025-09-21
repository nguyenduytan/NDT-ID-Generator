<?php
declare(strict_types=1);

namespace ndtan\Symfony\Command;

use ndtan\Manager;
use ndtan\Uuid\UuidV7Generator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class GenerateIdCommand extends Command
{
    protected static $defaultName = 'ndtid:make';

    public function __construct(private Manager $mgr = new Manager([
        'default' => 'uuid7',
        'drivers' => [ 'uuid7' => [ 'class' => UuidV7Generator::class ] ]
    ])) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->setDescription('Generate IDs using NDT ID Generator')
            ->addArgument('driver', InputArgument::OPTIONAL, 'Driver name', 'uuid7')
            ->addArgument('count', InputArgument::OPTIONAL, 'How many IDs to generate', 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $driver = (string)$input->getArgument('driver');
        $count = (int)$input->getArgument('count');
        for ($i=0; $i<$count; $i++) {
            $output->writeln($this->mgr->driver($driver)->generate());
        }
        return Command::SUCCESS;
    }
}
