<?php

namespace Xigen\Bundle\CurrencyConverterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('currency-converter:sync')
            ->setDescription('Update local currency data')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $repo = $em->getRepository("CurrencyConverterBundle:Exchange");

        $update = $repo->updateLocalData();
        if (true === $update) {
            $output->writeLn('<info>Success:</info> Successfully updated with data source.');

            return 0;
        }
    }
}
