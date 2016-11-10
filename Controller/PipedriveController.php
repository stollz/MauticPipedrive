<?php
namespace MauticPlugin\PipedriveBundle\Controller;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMapping;
use Mautic\CoreBundle\Controller\CommonController;
use Mautic\LeadBundle\Entity\Lead;
use MauticPlugin\PipedriveBundle\Entity\PipedrivePlugin;
use Symfony\Component\HttpFoundation\Response;
use MauticPlugin\PipedriveBundle\Benhawker\Pipedrive\Pipedrive;
use DateTime;
/**
* 
*/
class PipedriveController extends CommonController
{

	const THRASHHOLD_POINTS = 7; //if points greater than this passon else reject
	const PIPEDRIVEKEY = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
	const PIPEDRIVE_OWNER_ID = 392127; //your your id in pipedrive which becomes owner of person, org, activity
	/**
	* List the persons moved to the Pipedrive fetch from database and display online.
	* @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
	*/
	public function indexAction(){
		$em = $this->factory->getEntityManager();
		$repository = $em->getRepository("PipedriveBundle:PipedrivePlugin");
		$entries = $repository->findAll();
		$leadModel = $this->factory->getModel("lead");

		return $this->delegateView(array(
			'contentTemplate' => 'PipedriveBundle:Default:index.html.php',
			'viewParameters' => array("entities"=>$entries,'leadModel'=>$leadModel),
			));
	}

	/**
	* the person creation in pipedrive accessable at /pipedrive/person
	* @return \Symfony\Component\HttpFoundation\Response
	*/
	public function personAction(){
		$em = $this->getDoctrine()->getManager();
		$leadRepo = $em->getRepository("Mautic\LeadBundle\Entity\Lead");

		$rawData = file_get_contents("php://input");
		//test data to check if it works
		//$rawData = '{"mautic.lead_points_change":[{"lead":{"isPublished":true,"dateAdded":"2016-03-04T10:54:04+00:00","createdBy":null,"createdByUser":null,"dateModified":"2016-03-04T10:54:07+00:00","modifiedBy":null,"modifiedByUser":null,"id":99990,"points":1,"color":null,"fields":{"core":{"completeaddress":{"id":"30","label":"Complete Address","alias":"completeaddress","type":"text","group":"core","field_order":"0","value":null},"note":{"id":"30","label":"Note","alias":"note","type":"text","group":"core","field_order":"0","value":"Something about the note"},"full_salutation":{"id":"29","label":"Full Salutation","alias":"full_salutation","type":"text","group":"core","field_order":"2","value":null},"category":{"id":"28","label":"Category","alias":"category","type":"text","group":"core","field_order":"3","value":null},"source":{"id":"27","label":"Source","alias":"source","type":"text","group":"core","field_order":"4","value":null},"salutation":{"id":"23","label":"Salutation","alias":"salutation","type":"lookup","group":"core","field_order":"8","value":"Herr"},"title":{"id":"1","label":"Title","alias":"title","type":"lookup","group":"core","field_order":"9","value":null},"firstname":{"id":"2","label":"First Name","alias":"firstname","type":"text","group":"core","field_order":"10","value":"Demofirstname"},"lastname":{"id":"3","label":"Last Name","alias":"lastname","type":"text","group":"core","field_order":"11","value":"Demolastname"},"company":{"id":"4","label":"Company","alias":"company","type":"lookup","group":"core","field_order":"12","value":"Explainr GmbH"},"position":{"id":"5","label":"Position","alias":"position","type":"text","group":"core","field_order":"13","value":null},"email":{"id":"6","label":"Email","alias":"email","type":"email","group":"core","field_order":"14","value":"andreas.stoll@explainr.de"},"phone":{"id":"7","label":"Phone","alias":"phone","type":"tel","group":"core","field_order":"15","value":"9582595522"},"mobile":{"id":"8","label":"Mobile","alias":"mobile","type":"tel","group":"core","field_order":"16","value":null},"fax":{"id":"9","label":"Fax","alias":"fax","type":"text","group":"core","field_order":"17","value":null},"address1":{"id":"10","label":"Address Line 1","alias":"address1","type":"text","group":"core","field_order":"18","value":"Address1"},"address2":{"id":"11","label":"Address Line 2","alias":"address2","type":"text","group":"core","field_order":"19","value":null},"city":{"id":"12","label":"City","alias":"city","type":"lookup","group":"core","field_order":"20","value":"DemoCity"},"state":{"id":"13","label":"State","alias":"state","type":"region","group":"core","field_order":"21","value":null},"zipcode":{"id":"14","label":"Zipcode","alias":"zipcode","type":"lookup","group":"core","field_order":"22","value":"123456"},"country":{"id":"15","label":"Country","alias":"country","type":"country","group":"core","field_order":"23","value":"Germany"},"website":{"id":"16","label":"Website","alias":"website","type":"text","group":"core","field_order":"24","value":null}},"professional":{"newslettersubscription":{"id":"26","label":"Newsletter","alias":"newslettersubscription","type":"boolean","group":"professional","field_order":"5","value":"Ja"},"contactme":{"id":"25","label":"Contact me","alias":"contactme","type":"boolean","group":"professional","field_order":"6","value":null}},"social":{"twitter":{"id":"17","label":"Twitter","alias":"twitter","type":"text","group":"social","field_order":"25","value":null},"facebook":{"id":"18","label":"Facebook","alias":"facebook","type":"text","group":"social","field_order":"26","value":null},"googleplus":{"id":"19","label":"Google+","alias":"googleplus","type":"text","group":"social","field_order":"27","value":null},"skype":{"id":"20","label":"Skype","alias":"skype","type":"text","group":"social","field_order":"28","value":null},"instagram":{"id":"21","label":"Instagram","alias":"instagram","type":"text","group":"social","field_order":"29","value":null},"foursquare":{"id":"22","label":"Foursquare","alias":"foursquare","type":"text","group":"social","field_order":"30","value":null}},"personal":[]},"lastActive":null,"owner":null,"ipAddresses":{"46.59.174.218":{"ipDetails":{"city":"","region":"","zipcode":"","country":"","latitude":"","longitude":"","isp":"","organization":"","timezone":"","extra":"","connector":[],"message":"Missing Mashape application key. Go to http:\/\/docs.mashape.com\/api-keys to learn how to get your API application key."}}},"tags":[],"dateIdentified":null,"preferredProfileImage":null},"points":{"old_points":0,"new_points":10},"timestamp":"2016-03-04T10:54:07+00:00"}]}';
		$data = json_decode($rawData,true);
		//var_dump($data);

		//obtain field values from Mautic
		$leadid = $data['mautic.lead_points_change'][0]['lead']['id']; //lead id from mautic
		$points = $data['mautic.lead_points_change'][0]['points']['new_points']; //points after point change
		$firstName = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['firstname']['value']; //firstName of Lead in Mautic
		$lastName = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['lastname']['value']; //lastName of Lead in Mautic
		$name = $lastName.', '.$firstName; //join firstName and lastName to form name in Pipedrive format(lname, fname)
		$email = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['email']['value']; //email of lead
		$phone = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['phone']['value']; //phone number of lead if available
		$salutation = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['salutation']['value']; //salutation value
		$newsletter = $data['mautic.lead_points_change'][0]['lead']['fields']['professional']['newslettersubscription']['value']; //assuming you have newsletter field(if lead has newslettersubscription
		$company = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['company']['value']; //company name of lead
		//create address from lead fields
		$address = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['address1']['value'].', '.$data['mautic.lead_points_change'][0]['lead']['fields']['core']['city']['value'].', '.$data['mautic.lead_points_change'][0]['lead']['fields']['core']['zipcode']['value'].', '.$data['mautic.lead_points_change'][0]['lead']['fields']['core']['country']['value'];
		//note if you have any note field in mautic
		$note = $data['mautic.lead_points_change'][0]['lead']['fields']['core']['note']['value'];

		if(isset($points) && $points > self::THRASHHOLD_POINTS && (isset($email))){ //check thrashhold points and email existance

			//prepare the fields for pipedrive
			$person['4600dbed1da0264bf1b4469052a61db51c020387'] = $firstName;
			$person['a183005e74286f31101bca3b955bad50afc74f63'] = $lastName;
			$person['name'] = $name;
			$person['email'] = $email;
			$person['phone'] = $phone;
			$person['422b3de6c84722ac6b4894e9564dc8050337ec95'] = $salutation;//salutation
			$person['90ef89ba6bb8edd624667e3a18144d3dcfb9fdb9'] = $address;//Address
			$person['9bc9b85f8073812eeeb2519850b11499292b1afe'] = $leadid; //mautic id

			/*
			 * in pipedrive choice(radio) have a number as its option, manage it here accordingly.
			 * */
			if($newsletter=="Ja"){
				$newsletter = 61;
			}
			elseif($newsletter=="Nein"){
				$newsletter = 62;
			}
			else{
				$newsletter = "";
			}
			$person['179d3fa90ddac5cdcbe85f88cc803b5360f1f546'] = $newsletter;//newsletter

			if(trim($salutation)=="Herr"){
				$salutation = 7;
			}
			elseif(trim($salutation)=="Frau"){
				$salutation = 8;
			}
			else{
				$salutation="";
			}

			try{
				$leadMP = $em->getRepository("MauticPlugin\PipedriveBundle\Entity\PipedrivePlugin");
				$leadMauticPD = $leadMP->findBy(array("lead"=>$leadid));
				//var_dump($leadMauticPD);
				if(count($leadMauticPD)>0){
				}
				else{
					echo "Lead: ".$firstName.' '.$lastName.' | Email: '.$email.'<br/>';
					$pipedrive = new Pipedrive(self::PIPEDRIVEKEY);

					/*
					 * performing a email based search to check if person already exists.
					 * if it does then create activity and assign to the person else proceed to person creation
					 * */
					$curl = $pipedrive->curl();
					$data = $curl->get("searchResults",array("item_type"=>"person","term"=>$email));
					//var_dump($data['data'][0]);

					/*
					 * if person
					 * */
					if(count($data['data'])>0){
						$activity = array(
							'subject' => "ACTIVITY SUBJECT GOES HERE", //setting any subject to the activity
							'type' => 'call',
							'person_id' => $data['data'][0]['id'],
							'user_id' => self::PIPEDRIVE_OWNER_ID,
							'due_date' => date('Y-m-d'), //configure as per your choice
							'org_id'=> $data['data'][0]['details']['org_id']
						);
						echo "Lead Exists.. creating activity..</br>";
						$activity = $pipedrive->activities()->add($activity);
						echo "Activity: ".$activity['data']['id']."<br/>";
					}
					else{
						echo "new Lead..creating Org, Person and Activity<br/>";

						/*
						 * check if ogranization exists, if not then create one.
						 * */
						$orgId = false;
						if(trim($company)!=="" && trim($company)!==null){
							$org = $pipedrive->organizations()->getByName($company);
							if(count($org['data'])>0){
								$orgId = $org['data'][0]['id'];
							}
							else{
								$org = $pipedrive->organizations()->add(
									array(
										"name"=>$company,
										"owner_id"=>self::PIPEDRIVE_OWNER_ID
									)
								);
								$orgId = $org['data']['id'];
								echo "Org: ".$org['data']['id']."<br/>";
							}
						}
						if($orgId!==false){
							$person['org_id']= $orgId;
						}



						try{
							$person = $pipedrive->persons()->add($person);
							echo "Person: ".$person['data']['id']."</br>";

							$note = $pipedrive->notes()->add(array("content"=>$note,"person_id"=>$person['data']['id']));

							echo "Note: ".$note['data']['id']."<br/>";

							$activity = array(
								'subject' => "ACTIVITY SUBJECT GOES HERE", //setting any subject to the activity
								'type' => 'call',
								'person_id' => $data['data'][0]['id'],
								'user_id' => self::PIPEDRIVE_OWNER_ID,
								'due_date' => date('Y-m-d'), //configure as per your choice
								'org_id'=> $data['data'][0]['details']['org_id']
							);
							$activity = $pipedrive->activities()->add($activity);

							echo "Activity: ".$activity['data']['id']."<br/>";
						}
						catch(Exception $e){
							echo $e;
						}
					}

					//var_dump($activity);

					/*
					 * add entry in database to keep track of Lead and avoid duplicacy next time
					 * might get filter in the section where we check for person's existsnce in PD but just additional precuation
					 * */
					try{
						$lead = $leadRepo->findOneById($leadid);
						$pdLead = new PipedrivePlugin();
						$pdLead->setLead($lead);
						$pdLead->setDate(new DateTime("now"));
						$em->persist($pdLead);
						$em->flush();
					}
					catch(\Exception $e){
						$logger = $this->get("logger");
						$logger->error($e);
					}
				}
			}
			catch(\Exception $e){
				$logger = $this->get("logger");
				$logger->error($e);
			}
			unset($pipedrive);
			unset($person);
			unset($note);
			unset($org);
			unset($activity);
		}
		return new Response("OK");
	}
}