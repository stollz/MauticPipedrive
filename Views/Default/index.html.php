<?php
$view->extend("MauticCoreBundle:Default:content.html.php");
?>
<div class="">
    <h1>Mautic Pipedrive Plugin</h1>
    <table class="table">
        <thead>
        <tr>
            <th>Name(Last Name, First Name)</th>
            <th>Email</th>
            <th>Date Added</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach($entities as $entity){
            $lead = $leadModel->getLeadDetails($entity->getLead());
            ?>
            <tr>
                <td><a href="<?php echo $view['router']->generate('mautic_lead_action',array('objectAction'=>'view','objectId'=>$entity->getLead()->getId()))?>"> <?php echo $lead['core']['lastname']['value'].' '.$lead['core']['firstname']['value']; ?></a></td>
                <td><?php echo $lead['core']['email']['value']; ?></td>
                <td><?php echo $entity->getDate()->format("Y M, d"); ?></td>
            </tr>
            <?php

        } ?>
        </tbody>
    </table>
</div>
