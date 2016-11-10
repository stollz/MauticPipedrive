<?php
/**
 * Created by PhpStorm.
 * User: mayank
 * Date: 9/3/16
 * Time: 1:27 PM
 */

namespace MauticPlugin\PipedriveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mautic\CategoryBundle\Entity\Category;
use Mautic\CoreBundle\Entity\CommonEntity;

/**
 * Class PipedrivePlugin
 * @ORM\Table(name="pipedrive_plugin")
 * @ORM\Entity(repositoryClass="MauticPlugin\PipedriveBundle\Entity\PipedrivePluginRepository")
*/
class PipedrivePlugin extends CommonEntity
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="\Mautic\LeadBundle\Entity\Lead")
     * @ORM\JoinColumn(name="lead_id", referencedColumnName="id")
     */
    private $lead;

    /**
     * @ORM\Column(name="date",type="date")
     */
    private $date;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }



    /**
     * @return mixed
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \Mautic\LeadBundle\Entity\Lead
     */
    public function getLead()
    {
        return $this->lead;
    }

    /**
     * @param \Mautic\LeadBundle\Entity\Lead $lead
     */
    public function setLead(\Mautic\LeadBundle\Entity\Lead $lead)
    {
        $this->lead = $lead;
    }
}