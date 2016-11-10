<?php
/**
 * Created by PhpStorm.
 * User: mayank
 * Date: 22/3/16
 * Time: 2:26 PM
 */

namespace MauticPlugin\PipedriveBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MauticPlugin\PipedriveBundle\Benhawker\Pipedrive\Pipedrive;
use Mautic\LeadBundle\Entity\Lead;

/*
 * purpose of this class is to serve as cron job command
 * We needed a way to sync the contact in PD with Mautic at regular interval
 * 1. In PD we set a field SendEmail to OK
 * 2. Create a Filter which returns the Contacts with SendEmail = OK
 * 3. Using that filter's Id here we get the contacts and add to a list in Mautic which can be used in any campaign
 * */
class PipedriveEmailSyncCommand extends ContainerAwareCommand
{
    const FILTER_ID = 20; //ID of PIPEDRIVE Filter
    const PD_KEY = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
    const PD_EMAIL_OK_FIELD = "f78f68e3008b2cdd3297328ca727e38535e0a3dc";
    const PD_MAUTIC_ID_FIELD = "9bc9b85f8073812eeeb2519850b11499292b1afe";
    const LIST_ID = 44; //list id in Mautic in which to put contact from PD

    protected function configure()
    {
        $this->setName("pipedrive:email:sync")
            ->setDescription("this command sync the email entries into the mautic list.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pipedrive = new Pipedrive(self::PD_KEY);
        $curl = $pipedrive->curl();
        $data = $curl->get("persons",array("filter_id"=>self::FILTER_ID));


        if(count($data['data'])>0){

            $factory = $this->getContainer()->get("mautic.factory");
            $em = $factory->getEntityManager();
            $leadModel = $factory->getModel("lead");

            foreach($data['data'] as $pdPerson){ //repeat for all pd leads
                if($pdPerson[self::PD_EMAIL_OK_FIELD]==73){ //if email send set to Ja(73)
                    if($pdPerson[self::PD_MAUTIC_ID_FIELD]!=null){ //if mautic Id is set
                        //if lead exists
                        $lead = $leadModel->getEntity($pdPerson[self::PD_MAUTIC_ID_FIELD]);
                        if($lead){ //if lead exists
                            //get lead lists - array
                            $lists = $leadModel->getLists($lead);
                            if(count($lists)>0){ //if lists associated
                                $flag = false; //if true that means lead already in list
                                foreach($lists as $list){
                                    if($list->getId()== self::LIST_ID){
                                        $flag = true;
                                        break;
                                    }
                                }
                                if(!$flag){ //if lead doesn't exists in the list
                                    //add lead to list
                                    $leadModel->addToLists($lead,[self::LIST_ID]);

                                    //update pd with mautic id and email to Nein(74)
                                    $person = $pipedrive->persons()->update(
                                        $pdPerson['id'],
                                        array(
                                            "f78f68e3008b2cdd3297328ca727e38535e0a3dc"=>"",
                                        )
                                    );
                                }
                            }
                            else{
                                //add to list if list isn't associated with lead
                                $leadModel->addToLists($lead,[self::LIST_ID]);

                                //update pd with mautic id and email to Nein(74)
                                $person = $pipedrive->persons()->update(
                                    $pdPerson['id'],
                                    array(
                                        "f78f68e3008b2cdd3297328ca727e38535e0a3dc"=>""
                                    )
                                );
                            }
                        }
                    }
                    else{
                        //create lead
                        $lead = new Lead();
                        $leadFields = array(
                            'firstname' => $pdPerson['first_name'],
                            'lastname'  => $pdPerson['last_name'],
                            'email' => $pdPerson['email'][0]['value'],
                            'phone' => $pdPerson['phone'][0]['value'],
                            //"salutation"=>$pdPerson['422b3de6c84722ac6b4894e9564dc8050337ec95'], //set the salutation field from your PD uncomment to add it
                        );

                        // Set the lead's data
                        $leadModel->setFieldValues($lead, $leadFields);

                        // Save the entity
                        $leadModel->saveEntity($lead);

                        //add to list
                        $leadModel->addToLists($lead,[self::LIST_ID]);

                        //update pd with mautic id and email to Nein(74)
                        $person = $pipedrive->persons()->update(
                            $pdPerson['id'],
                            array(
                                self::PD_EMAIL_OK_FIELD=>"",
                                self::PD_MAUTIC_ID_FIELD=>$lead->getId()
                            )
                        );
                    }
                }
            }
        }
    }
}