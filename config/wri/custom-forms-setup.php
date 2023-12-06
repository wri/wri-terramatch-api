<?php
return [
    'sections' => [
        'organisation-governance' => [
            'title' => 'Governance  ',
            'description' => 'Please answer the questions below so that we can understand your organization\'s mission and structure. If you need more information on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a>',
            'fields' => [
                [
                    'linked_field_key' => 'org-hq-country',
                    'name' => 'Headquarters address Country',
                    'input_type' => 'select',
                    'label' => 'In what country is your organization’s headquarters located?',
                    'multichoice' => false,
                    'options_list' => 'countries'
                ],
                [
                    'linked_field_key' => 'org-countries',
                    'name' => 'In what countries is your organisation legally registered?',
                    'input_type' => 'select',
                    'label' => 'What countries is your organization legally registered in?',
                    'description' => 'Please select the relevant countries where your organization is legally registered to operate.',
                    'multichoice' => true,
                    'options_list' => 'countries'
                ],
                [
                    'linked_field_key' => 'org-fcol-lgl-reg',
                    'name' => 'Proof of local legal registration, incorporation, or right to operate',
                    'input_type' => 'file',
                    'label' => 'Proof of registration',
                    'description' => 'Please upload a document that proves your registration for each of the countries selected above.',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                        ],
                        'max' => 5,
                    ],
                ],
                [
                    'linked_field_key' => 'org-description',
                    'name' => 'Organization Description',
                    'input_type' => 'long-text',
                    'label' => 'Organization Mission',
                    'description' => 'Please describe your organization\'s mission.',
                ],
                [
                    'linked_field_key' => 'org-fdg-dte',
                    'name' => 'Date organization founded',
                    'input_type' => 'date',
                    'label' => 'On what date was your organisation founded?',
                ],
                [
                    'linked_field_key' => 'org-languages',
                    'name' => 'Organizational Languages',
                    'input_type' => 'select',
                    'label' => 'In what languages can your organisation communicate?',
                    'description' => 'Please select as many as apply. TerraMatch is available only in the languages listed below.',
                    'multichoice' => true,
                    'options' => ['English', 'French', 'Spanish', 'Portugese']
                ],
                [
                    'linked_field_key' => 'org-fcol-ref',
                    'name' => 'Reference Letters',
                    'input_type' => 'file',
                    'label' => 'Please provide at least two letters of reference',
                    'description' => 'Reference letters could come from a verifiable former donor or investor, a former or current project partner, a traditional or community leader, or any other individual or organization that can vouch for your work. We highly recommend that applicants submit an endorsement letter from a local or national government agency. Upload only .pdf files less than 5mb.',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                        ],
                        'max' => 5,
                    ],
                ],
                [
                    'linked_field_key' => 'org-ldr-shp-team',
                    'name' => 'Leadership team (providing your senior leaders by position, gender, and age)',
                    'input_type' => 'long-text',
                    'label' => [
                        'non-profit' => 'List Your Organization’s Board Members',
                        'enterprise' => 'List the people who have a ownership stake in your company'
                    ],
                    'description' => [
                        'non-profit' => 'Please list the members of your organization’s board of directors. For each person listed, indicate their name, title, gender, and age:<br>Name<br>Title<br>Gender<br>Age',
                        'enterprise' => 'Please list the individuals who own your company. For each person listed, please indicate their name, title, gender and age:<br>Name<br>Title<br>Gender<br>Age'
                    ]
                ],
            ],
            'components' => null
        ],
        'organisation-community-engagement' => [
            'title' => 'Past Community Engagement Experience',
            'description' => 'Please answer the questions below so that we can understand how your organization engages with local communities. If you need more information on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a>',
            'fields' => [
                [
                    'name' => 'Engagement: Farmers',
                    'input_type' => 'select',
                    'label' => 'How does your organization engage with farmers? ',
                    'description' => 'Select the statement(s) below that best align with your farmer engagement strategy: ',
                    'multichoice' => true,
                    'options' => ['We provide paid jobs for farmers', 'We directly engage & benefit farmers', 'We provide indirect benefits to farmers', 'We do not engage with farmers'],
                ],
                [
                    'name' => 'Engagement: Women',
                    'input_type' => 'select',
                    'label' => 'How does your organization engage with women?',
                    'description' => 'Select the statement(s) below that best aligns with your engagement strategy for women: ',
                    'multichoice' => true,
                    'options' => [
                        'We provide paid jobs for women',
                        'We directly engage and benefit women',
                        'We provide indirect benefits to women',
                        'We do not engage with women',
                    ]
                ],
                [
                    'name' => 'Engagement: Youth',
                    'input_type' => 'select',
                    'label' => 'How does your organization engage with people younger than 35 years old?',
                    'description' => 'Select the statement(s) below that best aligns with your engagement strategy for people younger than 35 years old',
                    'multichoice' => true,
                    'options' => [
                        'We provide paid jobs for people younger than 35',
                        'We directly engage and benefit younger than 35',
                        'We provide indirect benefits to people younger than 35',
                        'We do not engage with people younger than 35',
                    ]
                ],
            ],
        ],
        'organisation-financial-history' => [
            'title' => 'Financial History ',
            'description' => 'Please answer the questions below so that we can understand your organization’s financial history and current fiscal health. If you need more information on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a>',
            'fields' => [
                [
                    'linked_field_key' => 'org-fin-bgt-3year',
                    'name' => 'Organization Budget in USD for (-3 years from today)',
                    'input_type' => 'number',
                    'label' => [
                        'non-profit' => 'What was your organization\'s total budget in USD in 2020?',
                        'enterprise' => 'What were your organization\'s revenues in USD in 2020?'
                    ],
                    'description' => [
                        'non-profit' => 'Note that the budget denotes the amount of money managed by your organization in the given year, converted into USD.',
                        'enterprise' => 'Note that revenues are the total amount of income generated by the sale of goods or services related to your company\'s primary operations in a given year. Revenues are paid to the company by customers. Revenues do not include grants that paid for any operations. In some countries, revenues are referred to as "turnover."'
                    ],
                ],
                [
                    'linked_field_key' => 'org-fcol-op-bgt-3year',
                    'name' => 'Upload your organization\'s operating budget from 3 years ago',
                    'input_type' => 'file',
                    'label' => [
                        'non-profit' => 'Please upload your organization\'s 2020 budget.',
                        'enterprise' => 'Please upload your organization\'s 2020 income statement and balance sheet.',
                    ],
                    'description' => [
                        'non-profit' => 'We prefer financial statements in a spreadsheet format (.csv, .xls, etc.) but will accept .PDF files. Do not submit files in any other format. Budgets must detail your entire organization\'s expenses. Audited budgets are preferred, if available, but are not required at this stage. Find an example budget here, but we do not expect your submitted budget to match this format. ' .
                            '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a>',
                        'enterprise' => 'We prefer financial statements in a spreadsheet format (.csv, .xls, etc.) but will accept .PDF files. Do not submit files in any other format. Please ensure that you have uploaded all financial information necessary to assess the health of your business. Your upload should include a profit & loss / income statement and a balance sheet.',
                    ],
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'text/csv',
                            'application/xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ],
                        'max' => 20,
                    ],
                ],
                [
                    'linked_field_key' => 'org-fin-bgt-2year',
                    'name' => 'Organization Budget in USD for (-2 years from today)',
                    'input_type' => 'number',
                    'label' => [
                        'non-profit' => 'What was your organization\'s total budget in USD in 2021?',
                        'enterprise' => 'What were your organization\'s revenues in USD in 2021?'
                    ],
                    'description' => [
                        'non-profit' => 'Note that the budget denotes the amount of money managed by your organization in the given year, converted into USD.',
                        'enterprise' => 'Note that revenues are the total amount of income generated by the sale of goods or services related to your company\'s primary operations in a given year. Revenues are paid to the company by customers. Revenues do not include grants that paid for any operations. In some countries, revenues are referred to as "turnover."'
                    ],
                ],
                [
                    'linked_field_key' => 'org-fcol-op-bgt-2year',
                    'name' => 'Upload your organization\'s operating budget from 2 years ago',
                    'input_type' => 'file',
                    'label' => [
                        'non-profit' => 'Please upload your organization\'s 2021 budget.',
                        'enterprise' => 'Please upload your organization\'s 2021 income statement and balance sheet.',
                    ],
                    'description' => [
                        'non-profit' => 'We prefer financial statements in a spreadsheet format (.csv, .xls, etc.) but will accept .PDF files. Do not submit files in any other format. Budgets must detail your entire organization\'s expenses. Audited budgets are preferred, if available, but are not required at this stage. Find an example budget here, but we do not expect your submitted budget to match this format. ' .
                            '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a>',
                        'enterprise' => 'We prefer financial statements in a spreadsheet format (.csv, .xls, etc.) but will accept .PDF files. Do not submit files in any other format. Please ensure that you have uploaded all financial information necessary to assess the health of your business. Your upload should include a profit & loss / income statement and a balance sheet.',
                    ],
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'text/csv',
                            'application/xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ],
                        'max' => 20,
                    ],
                ],
                [
                    'linked_field_key' => 'org-fin-bgt-1year',
                    'name' => 'Organization Budget in USD for (-1 years from today)',
                    'input_type' => 'number',
                    'label' => [
                        'non-profit' => 'What was your organization\'s total budget in USD in 2022?',
                        'enterprise' => 'What were your organization\'s revenues in USD in 2022?'
                    ],
                    'description' => [
                        'non-profit' => 'Note that the budget denotes the amount of money managed by your organization in the given year, converted into USD.',
                        'enterprise' => 'Note that revenues are the total amount of income generated by the sale of goods or services related to your company\'s primary operations in a given year. Revenues are paid to the company by customers. Revenues do not include grants that paid for any operations. In some countries, revenues are referred to as "turnover."'
                    ],
                ],
                [
                    'linked_field_key' => 'org-fcol-op-bgt-1year',
                    'name' => 'Upload your organization\'s operating budget from last year',
                    'input_type' => 'file',
                    'label' => [
                        'non-profit' => 'Please upload your organization\'s 2022 budget.',
                        'enterprise' => 'Please upload your organization\'s 2022 income statement and balance sheet.',
                    ],
                    'description' => [
                        'non-profit' => 'We prefer financial statements in a spreadsheet format (.csv, .xls, etc.) but will accept .PDF files. Do not submit files in any other format. Budgets must detail your entire organization\'s expenses. Audited budgets are preferred, if available, but are not required at this stage. Find an example budget here, but we do not expect your submitted budget to match this format. ' .
                            '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a>',
                        'enterprise' => 'We prefer financial statements in a spreadsheet format (.csv, .xls, etc.) but will accept .PDF files. Do not submit files in any other format. Please ensure that you have uploaded all financial information necessary to assess the health of your business. Your upload should include a profit & loss / income statement and a balance sheet.',
                    ],
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'text/csv',
                            'application/xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ],
                        'max' => 20,
                    ],
                ],
                [
                    'linked_field_key' => 'org-fin-bgt-cur-year',
                    'name' => 'Organization Budget in USD for (this year)',
                    'input_type' => 'number',
                    'label' => [
                        'non-profit' => 'What is your organization\'s projected total budget in USD for 2023?',
                        'enterprise' => 'What are your organization\'s projected revenues in USD for 2023',
                    ],
                    'description' => [
                        'non-profit' => 'Note that the budget denotes the amount of USD managed by your organization in the given year. We understand that this information is not yet available for 2023. Please provide your best estimation. ',
                        'enterprise' => 'Note that revenues are the total amount of income generated by the sale of goods or services related to your company\'s primary operations in a given year. Revenues are paid to the company by customers. Revenues do not include grants that paid for any operations. In some countries, revenues are referred to as "turnover." We understand that this information is not yet available for 2023. Please provide your best estimation. ',
                    ],
                ],
            ],
        ],
        'organisation-restoration-experience' => [
            'title' => 'Past Restoration Experience ',
            'description' => 'Please answer the questions below so that we can understand your organization’s experience in land restoration. If you need more information on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a> ',
            'fields' => [
                [
                    'linked_field_key' => 'org-rel-exp-years',
                    'name' => 'Years of relevant restoration experience',
                    'input_type' => 'number',
                    'label' => 'How many years of restoration experience does your organization have?',
                    'description' => 'Please indicate how many years your organization has been involved in actively restoring degraded land. If your organization began to restore land significantly later than it was founded, make sure to only include the years after you began to work on restoration.',
                ],
                [
                    'linked_field_key' => 'org-ha-rst-tot',
                    'name' => 'Total Hectares Restored',
                    'input_type' => 'number',
                    'label' => 'How many hectares of degraded land has your organisation restored since it was founded?',
                    'description' => 'A hectare of land restored is defined as the total land area measured in hectares that has undergone restoration intervention. The land area under restoration includes more than active tree planting. Some land may not be planted while undergoing restoration. Instead, trees could be naturally regenerated on that land  without active planting. Only count land that has benefitted from tree-based restoration techniques in your total. ',
                ],
                [
                    'linked_field_key' => 'org-ha-rst-3year',
                    'name' => 'Hecatres Restored in the last 3 years',
                    'input_type' => 'number',
                    'label' => 'How many hectares of degraded land has your organization restored in the past 36 months?',
                    'description' => 'A hectare of land restored is defined as the total land area measured in hectares that has undergone restoration intervention. The land area under restoration includes more than active tree planting. Some land may not be planted while undergoing restoration. Instead, trees could be naturally regenerated on that land  without active planting. Only count land that has benefitted from tree-based restoration techniques in your total. ',
                ],
                [
                    'linked_field_key' => 'org-tre-gro-tot',
                    'name' => 'Total Trees Grown',
                    'input_type' => 'number',
                    'label' => 'How many trees has your organization restored or naturally regenerated since it was founded?',
                    'description' => 'A tree is defined as a woody perennial plant, typically having a single stem or trunk growing to 5 meters or higher, bearing lateral branches at some distance from the ground. TerraMatch counts "trees restored," not "planted." Only trees that survive to maturity after they  are planted or naturally regenerated should be counted toward this total. Naturally regenerating trees must attain a verifiable age of over 1 year to be counted as "restored."',
                ],
                [
                    'linked_field_key' => 'org-tre-gro-3yr',
                    'name' => 'Trees Grown in the last 3 years',
                    'input_type' => 'number',
                    'label' => 'How many trees has your organization planted, naturally regenerated or otherwise restored in the past 36 months?',
                    'description' => 'A tree is defined as a woody perennial plant, typically having a single stem or trunk  growing to 5 meters or higher, bearing lateral branches at some distance from the ground. TerraMatch counts "trees restored," not "planted." Only trees that survive to maturity after they are planted or naturally regenerated should be counted toward this total. Naturally regenerating trees must attain a verifiable age of over 1 year to be counted as "restored."',
                ],
                [
                    'linked_field_key' => 'pro-pit-fcol-rest-photos',
                    'name' => 'Photos of Past Restoration Work',
                    'input_type' => 'file',
                    'label' => 'Photos of past restoration work',
                    'description' => 'Please upload as many photos of your past restoration work as possible. Planting photos, before-and-after images, community engagement pictures, geotagged photos, and aerial images are especially valuable.',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'image/png',
                            'image/jpeg'
                        ],
                        'max' => 25,
                    ],
                ],
            ],
        ],
        'project-proposed' => [
            'title' => 'Proposed Project Information',
            'description' => 'Please answer the questions below so that we can understand basic information about the project you are looking to fund through TerraFund for AFR100: Landscapes. ' .
                'Reminder: We are only funding projects that are location in three key geographies: the Ghana Cocoa Belt, the Greater Rusizi Basin of Burundi, the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. ' .
                'If you need more information on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a> ',
            'fields' => [
                [
                    'linked_field_key' => 'pro-pit-name',
                    'name' => 'Project Name',
                    'input_type' => 'text',
                    'label' => 'What is the name of your proposed project?',
                ],
                [
                    'linked_field_key' => 'pro-pit-objectives',
                    'name' => 'Project Objectives',
                    'input_type' => 'long-text',
                    'label' => 'What are the objectives of your proposed project?',
                    'description' => 'Please provide details about your project\'s goals, how you intend to work with communities, how you intend to maintain and monitor your trees, and what your expected impact will be. The more detailed you are, using precise figures, the more accurately our team can screen your application.',
                ],
                [
                    'linked_field_key' => 'pro-pit-country',
                    'name' => 'Location of Restoration Project - Country',
                    'input_type' => 'select',
                    'label' => 'In what country will your project operate?',
                    'description' => 'If your project would span in multiple countries, select the main country of operation and provide more details about the geographic scope in the question below.',
                    'multichoice' => false,
                    'options_list' => 'countries'
                ],
                [
                    'linked_field_key' => 'pro-pit-district',
                    'name' => 'Location of Restoration Project - County/District',
                    'input_type' => 'text',
                    'label' => 'In which subnational jurisdictions would you carry out this project?',
                    'description' => 'Please enter the precise name of the district or sub-counties where your project would operate. This should be an official administrative jurisdiction. Please be as precise as possible.',
                ],
                [
                    'linked_field_key' => 'pro-pit-rst-inv-types',
                    'name' => 'Restoration Intervention Types',
                    'input_type' => 'select',
                    'label' => 'What interventions do you intend to use to restore land?',
                    'description' => 'Please indicate which restoration interventions you intend to deploy in this project. If you intend to use multiple different restoration interventions, check all that apply. Please find definitions below:<br><br>' .

                        'Agroforestry: The intentional mixing and cultivation of woody perennial species (trees, shrubs, bamboos) ' .
                        'alongside agricultural crops in a way that improves the agricultural productivity and ecological ' .
                        'function of a site.<br><br>'.

                        'Applied Nucleation: A form of enrichment planting where trees are planted in groups, clusters, or ' .
                        'even rows, dispersed throughout an area, to encourage natural regeneration in the matrix between ' .
                        'the non-planted areas.<br><br>'.

                        'Assisted Natural Regeneration: The exclusion of threats (i.e. grazing, fire, invasive plants) ' .
                        'that had previously prevented the natural regrowth of a forested area from seeds already present in ' .
                        'the soil, or from natural seed dispersal from nearby trees. This does not include any active tree planting.<br><br>' .

                        'Direct Seeding: The active dispersal of seeds (preferably ecologically diverse, native seed mixes) that ' .
                        'will allow for natural regeneration to occur, provided the area is protected from disturbances. ' .
                        'This may be done by humans or drones- implies active collection and dispersal, not natural dispersal ' .
                        'by natural seed dispersers that is part of natural regeneration processes.<br><br>' .

                        'Enrichment Planting: The strategic re-establishment of key tree species in a forest that is ecologically ' .
                        'degraded due to lack of certain species, without which the forest is unable to naturally sustain itself.<br><br>' .

                        'Mangrove Restoration: Specific interventions in the hydrological flows and/or vegetative cover to create ' .
                        'or enhance the ecological function of a degraded mangrove tree site<br><br>' .

                        'Reforestation: The planting of seedlings over an area with little or no forest canopy to meet specific goals.<br><br>' .

                        'Riparian Restoration: Specific interventions in the hydrological flows and vegetative cover to improve ' .
                        'the ecological function of a degraded wetland or riparian area.<br><br>' .

                        'Silvopasture: The intentional mixing and cultivation of woody perennial species (trees, shrubs, bamboos) ' .
                        'on pastureland where tree cover was absent in a way that improves the agricultural productivity and ' .
                        'ecological function of a site for continued use as pasture.',
                        'multichoice' => true,
                        'options' => [ 'Agroforestry', 'Applied Nucleation', 'Assisted Natural Regeneration',
                            'Direct Seeding', 'Enrichment Planting', 'Mangrove Restoration',
                            'Reforestation', 'Riparian Restoration', 'Silvopasture',
                    ],
                ],
                [
                    'linked_field_key' => 'pro-pit-tot-ha',
                    'name' => 'Total Hectares to be restored',
                    'input_type' => 'number',
                    'label' => 'How many hectares of land do you intend to restore through this project?',
                    'description' => 'A hectare of land restored is defined as the total land area measured in hectares that has undergone restoration intervention. The land area under restoration includes more than active tree planting. Some land may not be planted while undergoing restoration. Instead, trees could be naturally regenerated on that land could without active planting. Only count land that has benefitted from tree-based restoration techniques in your total.',
                ],
                [
                    'linked_field_key' => 'pro-pit-tot-trees',
                    'name' => 'Total number of trees to be grown',
                    'input_type' => 'number',
                    'label' => 'How many trees do you intend to restore through this project?',
                    'description' => 'A tree is defined as a woody perennial plant, typically having a single stem or trunk growing to 5 meters or higher, bearing lateral branches at some distance from the ground. TerraMatch counts "trees restored," not "planted." Only trees that survive to maturity after they are planted or naturally regenerated should be counted toward this total. Naturally regenerating trees must attain a verifiable age of over 1 year to be counted as "restored."',
                ],
                [
                    'linked_field_key' => 'pro-pit-tree-species',
                    'name' => 'Number of Trees by Species',
                    'input_type' => 'treeSpecies',
                    'label' => 'What tree species do you intend to grow through this project?',
                    'description' => 'List the species and the estimated number of trees that you intend to plant using the form below. The scientific names of each species are preferred. Please be precise.',
                    'additional_props' => [
                        'with_numbers' => true,
                    ],
                ],
                [
                    'linked_field_key' => 'pro-pit-bgt',
                    'name' => 'Project Budget ',
                    'input_type' => 'number',
                    'label' => 'What is your proposed project budget in USD? ',
                ],
            ],
        ],
        'additional-information' => [
            'title' => 'Additional Information ',
            'fields' => [
                [
                    'linked_field_key' => 'pro-pit-fcol-add',
                    'name' => 'Additonal Documents',
                    'input_type' => 'file',
                    'label' => 'Upload any additional documents',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'application/msword',
                            'application/xlsx',
                            'text/csv',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'image/png',
                            'image/jpeg'
                        ],
                        'max' => 20,
                    ],
                ],
                [
                    'linked_field_key' => 'pro-pit-cap-bld-needs',
                    'name' => 'Capacity Building Needs',
                    'input_type' => 'select',
                    'label' => 'On which of the following topics would you request technical assistance from a team of experts?',
                    'description' => 'Please select as many as apply. Note that this information remains private. Definitions for each topic can be found here (<a href="https://terramatchsupport.zendesk.com/hc/en-us/sections/13162666535835-EOI-Resources" target="_blank">EOI-Resources</a>)',
                    'multichoice' => true,
                    'options' => [
                        'Site Selection',
                        'Nursery Management',
                        'Species',
                        'Community Engagement',
                        'Narrative',
                        'Field Monitoring',
                        'Remote Sensing',
                        'Accounting',
                        'Proposal',
                        'Government',
                        'Certifications',
                        'Communications',
                        'Social Equity',
                        'Supply Chain Development',
                        'Product Marketing'
                    ]
                ],
                [
                    'linked_field_key' => 'pro-pit-how-discovered',
                    'name' => 'How did you hear about us?',
                    'input_type' => 'select',
                    'label' => 'How did you hear about this opportunity on TerraMatch?',
                    'description' => 'Please select all that apply. If you heard about us from another source, please select other.',
                    'multichoice' => true,
                    'options' => [
                        'Land Accelerator',
                        'AFR100 Partner',
                        'WhatsApp',
                        'LinkedIn',
                        'Funding Partner',
                        'Facebook',
                        'Twitter',
                        'World Resources Institute',
                        'One Tree Planted',
                        'Realize Impact',
                        'Barka Fund',
                        'Government Agency',
                        'Grant opportunity website',
                        'Internet search',
                        'Email'
                    ]
                ],
            ],
        ],
    ]
];



