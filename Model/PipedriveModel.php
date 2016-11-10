<?php
/**
 * Created by PhpStorm.
 * User: mayank
 * Date: 8/3/16
 * Time: 12:16 PM
 */

namespace MauticPlugin\PipedriveBundle\Model;


use Mautic\CoreBundle\Model\CommonModel;
use Mautic\LeadBundle\Entity\Lead;

class PipedriveModel extends CommonModel
{
    public function getLeadsFromDatabase($pdPerson){
        $leadRepo = $this->em->getRepository("Mautic\LeadBundle\Entity\Lead");

        $leadModel = $this->factory->getModel("lead");

        $result = $leadRepo->getLeadByEmail($pdPerson['email'][0]['value']);

        if(count($result)>0){
            return false;
        }
        else{
            // OR generate a completely new lead with
            $lead = new Lead();
            $lead->setNewlyCreated(true);
            $leadId = null;

            //IP address of the request
            $ipAdddress = $this->factory->getIpAddress();

            // Updated/new fields
            $leadFields = array(
                'firstname' => $pdPerson['first_name'],
                'lastname'  => $pdPerson['last_name'],
                'email' => $pdPerson['email'][0]['value'],
                'phone' => $pdPerson['phone'][0]['value'],
                //"salutation"=>$pdPerson['422b3de6c84722ac6b4894e9564dc8050337ec95'], //add hash from your salutation field
            );

            // Optionally check for identifier fields to determine if the lead is unique
            $uniqueLeadFields    = $this->factory->getModel('lead.field')->getUniqueIdentiferFields();
            $uniqueLeadFieldData = array();

            $fieldModel = $this->factory->getModel('lead.field');
            $availableLeadFields     = $fieldModel->getFieldList(true, false);

            // Check if unique identifier fields are included
            $inList = array_intersect_key($leadFields, $availableLeadFields);
            foreach ($inList as $k => $v) {
                if (empty($query[$k])) {
                    unset($inList[$k]);
                }

                if (array_key_exists($k, $uniqueLeadFields)) {
                    $uniqueLeadFieldData[$k] = $v;
                }
            }

            // If there are unique identifier fields, check for existing leads based on lead data
            if (count($inList) && count($uniqueLeadFieldData)) {
                $existingLeads = $this->em->getRepository('MauticLeadBundle:Lead')->getLeadsByUniqueFields(
                    $uniqueLeadFieldData,
                    $leadId // If a currently tracked lead, ignore this ID when searching for duplicates
                );
                if (!empty($existingLeads)) {
                    // Existing found so merge the two leads
                    $lead = $leadModel->mergeLeads($lead, $existingLeads[0]);
                }

                // Get the lead's currently associated IPs
                $leadIpAddresses = $lead->getIpAddresses();

                // If the IP is not already associated, do so (the addIpAddress will automatically handle ignoring
                // the IP if it is set to be ignored in the Configuration)
                if (!$leadIpAddresses->contains($ipAdddress)) {
                    $lead->addIpAddress($ipAdddress);
                }
            }

            // Set the lead's data
            $leadModel->setFieldValues($lead, $leadFields);

            // Save the entity
            $leadModel->saveEntity($lead);
            return true;
        }
   }

}