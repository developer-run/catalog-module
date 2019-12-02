<?php
/**
 * This file is part of devrun.
 * Copyright (c) 2019
 *
 * @file    UpdateCommand.php
 * @author  Pavel Paulík <pavel.paulik@support.etnetera.cz>
 */

namespace Devrun\CatalogModule\Commands;

use Devrun\CmsModule\CatalogModule\Facades\FeedFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateCommand extends Command
{

    /** @var FeedFacade */
    private $feedFacade;

    /**
     * CreateCommand constructor.
     * @param FeedFacade $feedFacade
     */
    public function __construct(FeedFacade $feedFacade)
    {
        parent::__construct();
        $this->feedFacade = $feedFacade;
    }


    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('devrun:catalog:update')
//            ->addArgument('module', InputArgument::REQUIRED, 'Module name')
            ->setDescription('Update catalog.');
    }


    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->feedFacade->update();
            $output->writeln(sprintf('<info>Reader</info> <comment>%s</comment> <info>has been updated.</info>', "catalog"));

        } catch (\Exception $e) {
            $output->writeln("<error>Error: '" . $e->getMessage() . "' .</error>");

        }
    }


}