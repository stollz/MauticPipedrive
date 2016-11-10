<?php
/**
 * Created by PhpStorm.
 * User: mayank
 * Date: 8/3/16
 * Time: 2:04 PM
 */

namespace MauticPlugin\PipedriveBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MauticPlugin\PipedriveBundle\Benhawker\Pipedrive\Pipedrive;

/*
 * purpose of this class is to serve as cron job command
 * We needed a way to sync the contact in PD with Mautic at regular interval,
 * if contact is new in pipedrive and we need to sync it with mautic. then you can create a smart list in mautic to prepare newsletter contacts
 * 1. In PD we set a field Newsletter to JA(yes) or NEIN(nein)
 * 2. Create a Filter which returns the Contacts with newsletter = JA(yes)
 * 3. Using that filter's Id here we get the contacts and add to a list in Mautic which can be used in any campaign
 * */
class PipedriveNewsletterCommand extends ContainerAwareCommand
{
    const FILTER_ID = 20; //ID of PIPEDRIVE Filter
    const PD_KEY = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
    protected function configure()
    {
        $this->setName("pipedrive:newsletter:sync")
            ->setDescription("this command sync the newsletter field from pipedrive to mautic.")
            ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pdModel = $this->getContainer()->get("mautic.factory")->getModel("plugin.mauticPipedrive.pipedrive");
        $pipedrive = new Pipedrive(self::PD_KEY);
        $curl = $pipedrive->curl();
        $persons = $curl->get("persons",array("filter_id"=>self::FILTER_ID));
        $output->writeln("please wait..processing..");
        foreach($persons['data'] as $person){
            $result = $pdModel->getLeadsFromDatabase($person);
            if($result){
                $output->writeln("Done: ".$person['email'][0]['value']);
            }
        }
    }

}