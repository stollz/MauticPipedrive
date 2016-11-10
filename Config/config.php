<?php
return array(

	"name"	=>	"Pipedrive",
	"description"	=>	"Stores the mautic point based event into the pipedrive and creates contact and activity",
	"author"	=>	"Stoerkens GmbH, Munich, Germany",
	"version"	=>	"1.0.0",

	"routes"	=> array(
		'main' => array(
            'plugin_pipedrive_index'  => array(
                'path'       => '/pipedrive',
                'controller' => 'PipedriveBundle:Pipedrive:index'
            )
        ),

		"public"	=>	array(
			"plugin_pipedrive_person"	=>	array(
				"path"	=>	"/pipedrive/person",
				"controller"	=>	"PipedriveBundle:Pipedrive:person"
				),
			)
		),

		'menu'   => array(
			'main' => array(
				'priority' => 24,
				'items'    => array(
					'Pipedrive' => array(
						'route'     => 'plugin_pipedrive_index',
						'iconClass' => 'fa-check-square'
					)
				)
			)
		),
	);