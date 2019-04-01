#Mautic Pipedrive Plugin

This plugin works with Mautic and connects contacts with pipedrive.
Stores the mautic point based event into the pipedrive and creates contact and activity

Author: Stoerkens GmbH, Munich, Germany
Version: 1.0.0

## Introduction
[Mautic](https://www.mautic.org/): Open source CRM with easy to work interface and extremly customizable code(see [docs](https://developer.mautic.org/) for customization flexiblity), created in Symfony framework(PHP). 

[Pipedrive](https://www.pipedrive.com): Sales Management Application to manage, leads and activities associated with those leads.

Pipedrive Plugin: This plugin acts as a bridge between Mautic and Pipedrive. Using this plugins leads are exported to Pipedrive and activities are created, also it provides ways to sync contacts from PD to Mautic.

## Why we created it ?
We use Mautic for our marketting campaign and Pipedrive to contact the leads generated from those campaigns, we needed a way through which we could check mautic point thrashhold and identify leads which could then be exported to PD for our call center people to contact them. So we created this plugin, it tracks any point change and sends the lead crossing a point thrashhold to PD.

##Installation
1. place PluginBundle directory in the plugins directory of your mautic installation.
e.g. Linux: `~your-mautic/plugins/PipedriveBundle`
2. go to console, clear mautic cache
~your-mautic/app$ php console cache:clear
3. Go to plugins In Mautic
4. Click Install new plugins, Any new plugin(or new version) will be installed. In this case Pipedrive Plugin. You will see Pipedrive menu at the left. This is only to keep track of leads which were exported to PD.
5. Set Up Webhook for point change. Use `https://www.your-mautic-url/pipedrive/person` as webhook URL.

After this setup, There need some doing in the Code, It doesn't need much of knowledge, if you have very basic knowledge that would be good enough, we try to write following content in a way that you don't face any problem customizing as per your requirements.

## File Structure:
### PipedriveBundle(/)
**Config** - Plugin Config file(no need to mess with it untill you know about mautic plugin development)

**Controller** -  Plugin Controller File, Main File

**Command** - Cron Command Files
- PipedriveNewsletterCommand
- PipedriveEmailSyncCommand

**Entity** - Database related files
	
**Views** - View/UI related files
	
**Benhawker** - Pipedrive API Files(No need to do anything here) - This library we have taken from [Pipedrive API Php](https://github.com/TTRGroup/pipedrive-api-php) by @github/benhawker1

## Configuration
### General Mautic Fields Preconfigured:
	Id – default
	points – default
	firstname – default
	lastname – default
	email – default
	phone – default
	salutation – default
	newsletter – custom(professional) – need for tracking if newsletter subscribed by contact
	company – default
	address1 – default
	city – default
	zipcode – default
	country – default
	note – custom(core type)
    
### Pipedrive Fields:
	firstName – obtain field hashcode
	lastName – obtain field hashcode
	name – default(comibnition of “lastName, firstName”)
	email 
	phone
	salutation – obtain hashcode
	address – obtain hashcode
	mautic_id – custom – create and obtain hash code
	newsletter – custom – obtain hashcode(values: Ja, Nein)
	sendEmail – custom – obtain hashcode(Ja or Okay)

### What to Configure:
#### Controller
**PipedriveController**

In the Controller class at the top are three constants which are required as per your needs, replace default


`const THRASHHOLD_POINTS = 7;`

Thrash hold points are limits which if crossed contact would be exported to Pipedrive.

`const PIPEDRIVEKEY = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";`

The [Pipedrive API](https://developers.pipedrive.com/docs/api/v1/) key required to connect to Pipedrive.

`const PIPEDRIVE_OWNER_ID = xxxxxx;`

Your your id in pipedrive which becomes owner of person, org, activity created through Plugin.
Also if you have More custom field in Pipedrive or Mautic to map together refer code documentation in the code file.

####Command
**PipedriveNewsletter**

`const FILTER_ID = 20;`

ID of PIPEDRIVE Filter
Id of the filter Check section below to know how to obtain this Id

`const PD_KEY = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";`

The Pipedrive API key required to connect to Pipedrive

If you are making changes in this section you will also need to make mapping modification in `Model/PipedriveModel` for the mapping of the fields.

#### Command
**PipedriveEmailSync**

`const FILTER_ID = 20; //ID of PIPEDRIVE Filter`

Id of the filter Check section below to know how to obtain this Id

`const PD_KEY = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";`

The Pipedrive API key required to connect to Pipedrive

`const PD_EMAIL_OK_FIELD = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";`

The custom Email field as mentioned in required field, place its hashcode here.

`const PD_MAUTIC_ID_FIELD = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";`

the custom mautic id field as mentioned in required field, plece its code here.

`const LIST_ID = 44; //list id in Mautic in which to put contact from PD`

Id of the List in Mautic, which will hold these Email contacts. Which are set to send email, through pipedrive.
When to Use Newsletter Cron?

#### Command 
**pipedrive:newsletter:sync**

This cron ought to be used when you want to set some lead in Pipedrive and have to use that Lead to add that contact in mautic for sending out newsletter. You need to create a filter and configure Plugin to use this filter, see section above for the same.

####Command
**Pipedrive:email:sync**

First set a custom field as mentioned in the sections above and use its Id to configure the plugin. This is there if you want to collect this lead in the Mautic List for some specific email sending.
           
### How to obtain Hashcode for fields and Filter Id for Crons?
1. While you create the field you will get it at the time of generation.
2. If you want to get field hashcode and multi option value Id, go to specific person, on the left you will find the fields, press F12 on your keyboard, Developer Option will open up. User selector to select and click the field on screen for which you want to see the hashcode. Once you do, you will see the hashcode for corresponding field, if it is multi select type, you select that option and you will find option value in the developer window.
3. To find Filter Id, if you didn't get at the time of generation, Go to Contact Listing, on the right top you will find User Filter and other filter dropdown menu. Click it once. Press F12 to open developer console, user Selector to choose your filter, there you will find the Filter Id.

## Note:
This Documentation, assumes you have at least basic knowledge about Mautic and Pipedrive and little bit of code. If any problem arises feel free to contact us, In future we will try to make it more user friendly.