<?php

$percentageValidationRules = json_encode(['required' => false, 'min' => 0, 'max' => 100]);
return [
    'sections' => [
        'organisation-governance' => [
            'title' => 'Governance',
            'description' => 'Please answer the questions below so that we can understand your organization’s mission and structure. If you need more information on any of the ' .
                'questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837503399707" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837503399707</a>',
            'fields' => [
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
                    'linked_field_key' => 'org-leadership-team',
                    'name' => 'Leadership team (providing your senior leaders by position, gender, and age)',
                    'input_type' => 'leadershipTeam',
                    'label' => [
                        'non-profit' => 'List Your Organization’s Board Members',
                        'enterprise' => 'List the people who have a ownership stake in your company',
                    ],
                    'description' => [
                        'non-profit' => 'Please list the members of your organization’s board of directors. For each person listed, indicate their name, title, gender, and age: Name, Title, Gender, Age',
                        'enterprise' => 'Please list the individuals who own your company. For each person listed, please indicate their name, title, gender and age: Name, Title, Gender, Age',
                    ],
                ],
                [
                    'name' => 'Your employees',
                    'input_type' => 'tableInput',
                    'table_headers' => ['Employee type', 'Employee Count'],
                    'label' => 'How many employees does your organisation currently employ?',
                    'description' => 'Total number of employees are the total number of people currently being paid for working with your organisation.<br>' .
                        'Full-time employees are paid employees of your organization that work more than 30 hours per week throughout the entire year. Do not include volunteers or ' .
                        'other project beneficiaries in this total.<br>' .
                        'Part-time employees are paid employees of your organization that either 1) work throughout the year but less than 30 hours per week or 2) work only during ' .
                        'certain seasons of the year and work any number of hours. These employees are variously called “part-time,” “seasonal,” or “temporary” workers. Do not include ' .
                        'volunteers or other project beneficiaries in this total.<br>' .
                        'Indicate how many of your organization’s employees, both part-time and full-time, are women. If you employ any people who do not identify as either women or ' .
                        'men, include them in this tally. Do not include volunteers or other project beneficiaries in this total.<br>' .
                        'Indicate how many of your organization’s employees, both part-time and full-time, are men. Do not include volunteers or other project beneficiaries in this total.<br>' .
                        'Indicate how many of your organization’s employees, both part-time and full-time, are between and including the ages of 18 and 35. Do not include volunteers or other ' .
                        'project beneficiaries in this total.<br>' .
                        'Indicate how many of your organization’s employees, both part-time and full-time, are older than 35 years of age. Do not include volunteers or other project ' .
                        'beneficiaries in this total.',
                    'children' => [
                        [
                            'linked_field_key' => 'org-female-employees',
                            'name' => 'Number of female employees',
                            'input_type' => 'number',
                            'label' => 'Number of female employees?',
                        ],
                        [
                            'linked_field_key' => 'org-male-employees',
                            'name' => 'Number of male employees',
                            'input_type' => 'number',
                            'label' => 'Number of male employees?',
                        ],
                        [
                            'linked_field_key' => 'org-young-employees',
                            'name' => 'Number of employees between and including ages 18 and 35',
                            'input_type' => 'number',
                            'label' => 'Number of employees between and including ages 18 and 35?',
                        ],
                        [
                            'linked_field_key' => 'org-over-35-employees',
                            'name' => 'Number of employees older than 35 years of age',
                            'input_type' => 'number',
                            'label' => 'Number of employees older than 35 years of age?',
                        ],
                        [
                            'linked_field_key' => 'org-ft-perm-employees',
                            'name' => 'Number of full-time permanent employees',
                            'input_type' => 'number',
                            'label' => 'Number of full-time permanent employees?',
                        ],
                        [
                            'linked_field_key' => 'org-pt-perm-employees',
                            'name' => 'Number of part-time permanent employees',
                            'input_type' => 'number',
                            'label' => 'Number of part-time permanent employees?',
                        ],
                        [
                            'linked_field_key' => 'org-temp-employees',
                            'name' => 'Number of temporary employees',
                            'input_type' => 'number',
                            'label' => 'Number of temporary employees?',
                        ],
                    ],
                ],
                [
                    'linked_field_key' => 'org-fcol-additional',
                    'name' => 'Other additional documents',
                    'input_type' => 'file',
                    'label' => 'Upload any additional documents you would like to share about your organization or project ',
                    'description' => 'We will accept PDFs, Word Documents, Excel and CSV files, PNGS, JPGs that are less than 10MB',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'text/csv',
                            'application/xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ],
                        'max' => 10,
                    ],
                ],
            ],
        ],
        'organisation-community-experience' => [
            'title' => 'Past Community Engagement Experience',
            'description' => 'Please answer the questions below so that we can understand how your organization engages with local communities. If you need more information on any ' .
                'of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14843749441819" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14843749441819</a>',
            'fields' => [
                [
                    'linked_field_key' => 'org-engagement-farmers',
                    'name' => 'Engagement: Farmers',
                    'input_type' => 'select',
                    'label' => 'How does your organization engage with farmers?',
                    'description' => 'Select the statement(s) below that best align with your farmer engagement strategy: ',
                    'multichoice' => true,
                    'options' => [
                        'We provide paid jobs for farmers',
                        'We directly engage & benefit farmers',
                        'We provide indirect benefits to farmers',
                        'We do not engage with farmers'],
                ],
                [
                    'linked_field_key' => 'org-engagement-women',
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
                    'linked_field_key' => 'org-engagement-youth',
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
                [
                    'linked_field_key' => 'org-community-experience',
                    'name' => 'Past Community Engagement Experience',
                    'input_type' => 'long-text',
                    'label' => 'How has your organization mobilized and engaged with community members to restore land?',
                    'description' => 'Describe in detail the strategy that you used to engage the people that live in and around the landscapes that your organization has restored. ' .
                        'Cite specific examples of the communities that you mobilized, how you incentivized them to restore land, and how you have improved your approach over time. ' .
                        'Explain how you directly engage women, youth, smallholder farmers, traditional leaders, and other important stakeholders. ',
                ],
                [
                    'name' => 'Community Engagement Numbers',
                    'input_type' => 'tableInput',
                    'table_headers' => ['Member Type', 'Member Count'],
                    'label' => 'How many community members has your organization benefitted over the past 36 months?',
                    'description' => 'A “beneficiary” is defined as anyone who has derived a direct or indirect benefit from your organization’s projects, excluding your organization’s employees.<br>' .
                        'Note that each beneficiary is either a man or a woman, as well as under and equal to or over 35 years of age. Each beneficiary should be counted in two tallies. ' .
                        'People who do not identify as either a man or a woman should be counted in the “women” category. Note that you must separately enter the total number of beneficiaries ' .
                        'to ensure that the sum is correct.',
                    'children' => [
                        [
                            'linked_field_key' => 'org-tot-eng-comty-mbrs-3yr',
                            'name' => 'Total # of community members engaged over the last 3 years',
                            'input_type' => 'number',
                            'label' => 'Total # of community members engaged over the last 3 years',
                        ],
                        [
                            'linked_field_key' => 'org-pct-engaged-women-3yr',
                            'name' => '% of community members engaged over the last 3 years that were women',
                            'input_type' => 'number',
                            'label' => '% of community members engaged over the last 3 years that were women',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'org-pct-engaged-men-3yr',
                            'name' => '% of community members engaged over the last 3 years that were men',
                            'input_type' => 'number',
                            'label' => '% of community members engaged over the last 3 years that were men',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'org-pct-engaged-young-3yr',
                            'name' => '% of community members engaged over the last 3 years that were younger than 35 years',
                            'input_type' => 'number',
                            'label' => '% of community members engaged over the last 3 years that were younger than 35 years',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'org-pct-engaged-old-3yr',
                            'name' => '% of community members engaged over the last 3 years that were older than 35 years',
                            'input_type' => 'number',
                            'label' => '% of community members engaged over the last 3 years that were older than 35 years',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'org-pct-engaged-smallholder-3yr',
                            'name' => '% of community members engaged over the last 3 years that were smallholder farmers',
                            'input_type' => 'number',
                            'label' => '% of community members engaged over the last 3 years that were smallholder farmers',
                            'validation' => $percentageValidationRules,
                        ],
                    ],
                ],
            ],
        ],
        'organisation-financial-history' => [
            'title' => 'Financial History ',
            'description' => 'Please answer the questions below so that we can understand your organization’s financial history and current fiscal health. If you need more ' .
                'information on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14843860919195" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14843860919195</a>',
            'fields' => [
                [
                    'linked_field_key' => 'org-fin_start_month',
                    'name' => 'Start of financial year (month)',
                    'input_type' => 'select',
                    'label' => 'When does the fiscal year begin for your organisation?',
                    'description' => 'Please select the month your fiscal year begins so we can understand the time frame for the following questions',
                    'multichoice' => false,
                    'options_list' => 'months'
                ],
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
                [
                    'only' => 'enterprise',
                    'linked_field_key' => 'org-fcol-bank-statments',
                    'name' => 'Other additional documents',
                    'input_type' => 'file',
                    'label' => 'Upload your organization\'s bank statements',
                    'description' => 'A bank statement is a list of all transactions for a bank account over a set period. If your organization uses multiple bank accounts, include statements from all relevant accounts.',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'text/csv',
                            'application/xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ],
                        'max' => 10,
                    ],
                ],
                [
                    'linked_field_key' => 'org-funding-types',
                    'name' => 'Breakdown of Recent (past 4 years) Funding History by Funding Type + $$',
                    'input_type' => 'fundingType',
                    'label' => 'What is the breakdown of your organisation\'s recent (2020, 2021, 2022, 2023*) funding history by funding type in USD?',
                    'description' => 'For each of the funders that provided you more than $10,000 USD in the past 3 years/36 months, list the organization or individual\'s name, the amount ' .
                        'that you received converted to USD (use the exchange rate from January 2023), and the type of financing that the funder provided.<br>' .
                        'Definitions for each of these types of financing can be found below.<br>' .
                        'Grant from Foundation/Donor: This is a financial donation given to an organization by a foundation, non-profit, corporation, or other non-government funder. <br>' .
                        'Public Grant from Government: This is a financial donation given to an organization by a government agency or institution. <br>' .
                        'Loan/Credit Finance from Private Bank or Investor: This is an agreement between parties involving a financial contract in which the lender provides the borrower an ' .
                        'amount of capital that must be repaid, usually at a specified interest rate.<br>' .
                        'Equity from Private Investor: Equity is an ownership interest in business and is a way of raising capital by selling shares to investors. Equity finance gives ' .
                        'shareholders a stake in the company\'s long-term growth and performance. <br>' .
                        'Product Offtake Contract: This type of contract guarantees the purchase of future goods between the seller of a product and a buyer (the \'offtaker\').    <br>' .
                        'Carbon Credit Contract: This is a contract between a carbon credit seller and a buyer to purchase verified carbon credits from a tree-growing project that ' .
                        'sequesters carbon dioxide. <br>' .
                        'Public/Private Payments for Ecosystem Services: Payments for ecosystem services (PES) are financial incentives offered to farmers or landowners for managing their ' .
                        'land to provide ecological services. For example, this can include payments from governments or private sector funders for watershed control and erosion prevention. <br>' .
                        'Other: This is any additional source of financing not defined in the above categories.',
                ],
                [
                    'linked_field_key' => 'org-add-fund-details',
                    'name' => 'Additional details about funding structure, organisational support, etc.',
                    'input_type' => 'long-text',
                    'label' => 'How did you raise the funding indicated in the previous question?',
                    'description' => 'Explain your organization’s funding history since 2020, based on the information provided in the question above. Provide extensive details of how you ' .
                        'raised that funding, who provided it, and how your fundraising strategy has shifted over time.',
                ],
            ],
        ],
        'organisation-restoration-experience' => [
            'title' => 'Past Restoration Experience ',
            'description' => 'Please answer the questions below so that we can understand your organization’s experience in land restoration. If you need more information on ' .
                'any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14843930116251" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14843930116251</a>',
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
                    'name' => 'Hectares Restored in the last 3 years',
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
                    'linked_field_key' => 'org-tree-species',
                    'name' => 'Tree Species Grown',
                    'input_type' => 'treeSpecies',
                    'label' => 'Which tree species has your organization grown',
                    'description' => 'Enter the names of all the tree species that your organization has grown in the past. The scientific name of each species is preferred to their ' .
                        'local or common names, but the latter are accepted.<br>' .
                        'If your organization has planted more than 25 species of trees, input the 25 most numerous species that you have grown.',
                ],
                [
                    'linked_field_key' => 'org-avg-tree-survival-rate',
                    'name' => 'Average Tree Survival Rate',
                    'input_type' => 'number',
                    'label' => 'What was the average survival rate of the trees that your organization has grown?',
                    'description' => 'To calculate this number, divide the total number of trees your organization has grown to maturity (at least 5 years) by the total number of trees that ' .
                        'your organization has planted or begun to naturally regenerate. Then, multiply by 100. Please be accurate; we understand that survival rates are naturally lower in ' .
                        'some landscapes than in others.',
                ],
                [
                    'linked_field_key' => 'org-avg-tree-surv-rate-proof',
                    'name' => 'Proof of Average Tree Survival Rate',
                    'input_type' => 'file',
                    'label' => 'Please upload any document that tracks the average tree survival rate from a current (greater than 5 years) or past restoration project.',
                    'description' => 'If you have records of the total number of trees your organization has grown to maturity (at least 5 years) and the total number of trees that your ' .
                        'organization has planted or begun to naturally regenerate for a restoration project, ' .
                        'please upload it here in one of the following formats: PDF, Word Document, Excel, CSV, PNG, and/or JPG.<br>' .
                        'Note: Tree survival rate is calculated by, dividing the total number of trees your organization has grown to maturity (at least 5 years) by the total number of trees ' .
                        'that your organization has planted or begun to naturally regenerate. Then, multiply by 100. Please be accurate; we understand that survival rates are naturally lower in ' .
                        'some landscapes than in others.',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'image/png',
                            'image/jpeg',
                            'application/pdf',
                            'application/msword',
                            'text/csv',
                            'application/xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ],
                        'max' => '25',
                    ],
                ],
                [
                    'linked_field_key' => 'org-restoration-types-implemented',
                    'name' => 'Restoration Intervention Types Implemented',
                    'input_type' => 'select',
                    'label' => 'Which interventions has your organization used to restore land?',
                    'description' => 'Please indicate all of the restoration interventions you have used in previous projects. If you intend to use multiple different restoration interventions, ' .
                        'check all that apply. Please find definitions below: <br><br>'.

                        'Agroforestry: The intentional mixing and cultivation of woody perennial species (trees, shrubs, bamboos) alongside agricultural crops in a way that improves the ' .
                        'agricultural productivity and ecological function of a site. <br><br>'.

                        'Applied Nucleation: A form of enrichment planting where trees are planted in groups, clusters, or even rows, dispersed throughout an area, to encourage natural ' .
                        'regeneration in the matrix between the non-planted areas. <br><br>'.

                        'Assisted Natural Regeneration: The exclusion of threats (i.e. grazing, fire, invasive plants) that had previously prevented the natural regrowth of a forested area ' .
                        'from seeds already present in the soil, or from natural seed dispersal from nearby trees. This does not include any active tree planting. <br><br>'.

                        'Direct Seeding: The active dispersal of seeds (preferably ecologically diverse, native seed mixes) that will allow for natural regeneration to occur, provided the area ' .
                        'is protected from disturbances. This may be done by humans or drones- implies active collection and dispersal, not natural dispersal by natural seed dispersers that ' .
                        'is part of natural regeneration processes. <br><br>'.

                        'Enrichment Planting: The strategic re-establishment of key tree species in a forest that is ecologically degraded due to lack of certain species, without which the ' .
                        'forest is unable to naturally sustain itself. <br><br>'.

                        'Mangrove Restoration: Specific interventions in the hydrological flows and/or vegetative cover to create or enhance the ecological function of a degraded mangrove ' .
                        'tree site <br><br>'.

                        'Reforestation: The planting of seedlings over an area with little or no forest canopy to meet specific goals. <br><br>'.

                        'Riparian Restoration: Specific interventions in the hydrological flows and vegetative cover to improve the ecological function of a degraded wetland or riparian area. <br><br>'.

                        'Silvopasture: The intentional mixing and cultivation of woody perennial species (trees, shrubs, bamboos) on pastureland where tree cover was absent in a way that ' .
                        'improves the agricultural productivity and ecological function of a site for continued use as pasture.',
                    'multichoice' => true,
                    'options' => [ 'Agroforestry', 'Applied Nucleation', 'Assisted Natural Regeneration',
                        'Direct Seeding', 'Enrichment Planting', 'Mangrove Restoration',
                        'Reforestation', 'Riparian Restoration', 'Silvopasture',
                    ]
                ],
                [
                    'linked_field_key' => 'org-tree-maint-aftercare-aprch',
                    'name' => 'Tree Maintenance & After Care Approach',
                    'input_type' => 'long-text',
                    'label' => 'What strategies have you used to maintain the trees that you have grown?',
                    'description' => 'Describe in detail the specific strategies that you have deployed to protect and nurture the saplings that your organization has grown. Indicate how ' .
                        'long you check on trees after planting or regeneration and how you work with communities to ensure their long-term upkeep or sustainable use.',
                ],
                [
                    'linked_field_key' => 'org-restored-areas-desc',
                    'name' => 'Description of areas restored ',
                    'input_type' => 'long-text',
                    'label' => 'In which areas of this country have you worked in the past, and what are their characteristics of these landscapes?',
                    'description' => 'Within the country associated with this application, list the specific landscapes, communities, or administrative areas in which you have worked. ' .
                        'Describe each of the areas and what you have done to tailor your approach to suit the needs of the local people and biodiversity. ',
                ],
                [
                    'linked_field_key' => 'org-mon-eval-exp',
                    'name' => 'Monitoring and evaluation experience',
                    'input_type' => 'long-text',
                    'label' => 'How have you monitored and evaluated the progress of your past projects?',
                    'description' => 'Describe your organization’s approach to reporting, monitoring, and verifying the progress of your restoration projects. Be specific about the techniques ' .
                        'and tools that you have used. Describe which indicators you track, how you measure them, and how you apply data-driven insights to improve your work. Where possible, ' .
                        'include examples of past statistics and data that you have gathered. ',
                ],
                [
                    'linked_field_key' => 'org-historic-monitoring-geojson',
                    'name' => 'Historic monitoring shapefile upload (optional)',
                    'input_type' => 'mapInput',
                    'label' => 'Please upload a geospatial polygon that identifies the location of a past restoration project.',
                    'description' => 'Once funded, TerraMatch requires each project developer to submit precise geospatial polygons that correspond to the exact parcels of land that the project ' .
                        'is restoring. If you have no experience working with geospatial systems, we will provide that support if you are selected.<br>' .
                        'If you do have an example of a precise polygon that you have created for a past restoration project, please upload it here in one of the following three ' .
                        'formats: .geojson, .kml, or .zip (containing .dbf, .shp, .shx and .prj files). This polygons should only include areas where you directly led the work. Do not upload a ' .
                        'polygon for a proposed project. <br>' .
                        'For more information about how we assess the quality of these polygons – and how to create them – see this guidance: ' .
                        '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/15160781169691" target="_blank">"Add zendesk link: https://terramatchsupport.zendesk.com/hc/en-us/articles/15160781169691</a>',
                ],
                [
                    'linked_field_key' => 'org-fcol-prev-annual-rpts',
                    'name' => 'Previous Annual Reports for Monitored Restoration Projects (optional)',
                    'input_type' => 'file',
                    'label' => 'Please upload monitoring reports from past projects.',
                    'description' => 'You can upload up to 5 examples of previous monitoring reports that you have produced for past restoration projects. Reports that you submitted to funders, ' .
                        'government agencies, or technical partners are especially welcome.',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'text/csv',
                            'application/xlsx',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ],
                        'max' => 10,
                    ],
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
        'project-information' => [
            'title' => 'Proposed Project Information ',
            'description' => 'Please answer the questions below so that we can understand basic information about the project you are looking to fund through ' .
                'TerraFund for AFR100: Landscapes. Reminder: We are only funding projects that are location in three key geographies: the Ghana Cocoa Belt, ' .
                'the Greater Rusizi Basin of Burundi, the Democratic Republic of the Congo, and Rwanda, and the Great Rift Valley of Kenya. If you need more information ' .
                'on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837545925147" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837545925147</a>',
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
                    'linked_field_key' => 'pro-pit-bgt',
                    'name' => 'Project Budget',
                    'input_type' => 'number',
                    'label' => 'What is your proposed project budget in USD?',
                ],
                [
                    'linked_field_key' => 'pro-pit-fcol-detail-proj-bdgt',
                    'name' => 'Detailed Project Budget',
                    'input_type' => 'file',
                    'label' => [
                        'non-profit' => 'Please upload a detailed budget for this project.',
                        'enterprise' => 'Please upload a detailed budget for this loan application.',
                    ],
                    'description' => [
                        'non-profit' => 'Non-profit organizations must submit a budget that details how they intend to use this funding to complete the proposed scope of work. Download the template, ' .
                            'complete it with the required information, and reupload it to in the field below. We will only accept budgets submitted in the correct format. Access the budget template ' .
                            'along with guidance on how to prepare and submit this budget here :' .
                            '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/15120920064283" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/15120920064283</a>',
                        'enterprise' => 'For-profit organizations must submit a budget that details how they intend to use the funding associated with this loan application. Download the template ' .
                            'below, complete it with the required information, and reupload it to in the field below. We will only accept budgets submitted in the correct format. Access the budget ' .
                            'template along with guidance on how to prepare and submit this budget here: ' .
                            '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/15120920064283" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/15120920064283</a>',
                    ],
                ],
                [
                    'linked_field_key' => 'pro-pit-expected-active-rest-start-date',
                    'name' => 'Expected active restoration start date',
                    'input_type' => 'date',
                    'label' => 'When do you expect to this project to begin actively restoring land?',
                    'description' => 'Indicate the date when you expect the first preparation activity to occur on one of the project’s restoration sites. This should include the first time that ' .
                        'the land is actively improved by the project’s employees or volunteers. This will usually occur during the site preparation phase, after communities are mobilized and ' .
                        'sites are selected but before planting or natural regeneration begins. For this opportunity, we expect this to be no earlier than January 2024.',
                ],
                [
                    'linked_field_key' => 'pro-pit-expected-active-rest-end-date',
                    'name' => 'Expected active restoration end date',
                    'input_type' => 'date',
                    'label' => 'When do you expect this project’s final restoration activity to occur?',
                    'description' => 'Indicate the date when you expect the last active restoration activity to occur. This usually indicates the date when the last tree will be planted, or '.
                        'the date when the last natural regeneration area will be treated. You should not include years in which only monitoring, maintenance, and evaluation are conducted. ',
                ],
                [
                    'linked_field_key' => 'pro-pit-desc-of-proj-timeline',
                    'name' => 'Description of Project Timeline',
                    'input_type' => 'long-text',
                    'label' => 'What are the key stages of this project’s implementation?',
                    'description' => 'Describe in detail each of the stages of project and when the months and years in which they will occur. These stages can include community mobilization, ' .
                        'site preparation, planting, maintenance, and monitoring. You should not propose that additional land be brought under restoration or trees planted in any year after 2025. ',
                ],
                [
                    'linked_field_key' => 'pro-pit-proj-partner-info',
                    'name' => 'Proposed Project Partner Information',
                    'input_type' => 'long-text',
                    'label' => 'Which partner organizations do you intend to engage during this project?',
                    'description' => 'If your project is delivered in conjunction with an additional non-profit or for-profit project developer, a government agency, a technical partner, a ' .
                        'university, or any other partner, list them all and explain each of their proposed roles. You are encouraged to submit letters of recommendation from each of these ' .
                        'partners before submitting this application. ',
                ],
                [
                    'linked_field_key' => 'pro-pit-land-tenure-proj-area',
                    'name' => 'Land Tenure of Project Area',
                    'input_type' => 'select',
                    'multichoice' => true,
                    'label' => 'Which of the following land tenure arrangements govern your project area?',
                    'description' => 'Indicate which of the following land tenure arrangements govern the proposed project area. If there are multiple types of land use or ownership systems in ' .
                        'this areas, select all that apply.<br>' .
                        'The land tenure types are defined as follows:<br>' .
                        'Private land is owned and managed by a private landowner or company.<br>' .
                        'Public land is managed or owned by a government body (except for national parks or reserves).<br>' .
                        'Indigenous land is governed by indigenous customary tenure and other community agreements.<br>' .
                        'Communal land is acquired, possessed, and transferred under community-based regimes and is typically under customary tenure systems.<br>' .
                        'National protected areas are protected areas, parks, or reserves managed by the corresponding national body. These lands typically have regulations on access and use and ' .
                        'are managed for the purpose of conserving nature and natural resources.<br>' .
                        'Other land is any land that does not fall under the categories mentioned above.',
                    'options' => [
                        'Private land',
                        'Public land',
                        'Indigenous land',
                        'Communal land',
                        'National protected area',
                        'Other',
                    ],
                ],
                [
                    'linked_field_key' => 'pro-pit-proof-of-land-tenure-mou',
                    'name' => 'Proof of Land Tenure MOU',
                    'input_type' => 'file',
                    'label' => 'Please upload any documentation that explains the project area’s land tenure.',
                    'description' => 'If available, upload any documentation that describes the land tenure arrangement in your project area. This can include information collected by your own ' .
                        'organization, a government body, or an independent assessment. If you have a memorandum of understanding (MOU) with any communities, the government, or traditional ' .
                        'authorities within the proposed project area, you are highly encouraged to upload it. Do not include any documentation that is not related to the proposed project area.',
                    'multichoice' => true,
                    'additional_props' => [
                        'accept' => [
                            'application/pdf',
                            'application/msword',
                        ],
                        'max' => 25,
                    ],
                ],
                [
                    'linked_field_key' => 'pro-pit-landholder-comm-engage',
                    'name' => 'Landholder & Community Engagement Strategy',
                    'input_type' => 'long-text',
                    'label' => 'How does the land tenure system operate in your project area?',
                    'description' => 'Describe in detail how the land tenure system operates within your project area, including any changes that have occurred within the past decade. ' .
                        'Outline who owns, leases, and uses the land and any current disputes. Within that context, indicate how you plan to select sites for restoration within your ' .
                        'proposed project area, and how you will ensure that this project will not exacerbate inequalities related to land tenure.',
                ],
                [
                    'linked_field_key' => 'pro-pit-proj-success-risks',
                    'name' => 'What are the biggest risks to your project\'s success and how do you intend to mitigate them?',
                    'input_type' => 'long-text',
                    'label' => 'What risks could your project will face, and how do you intend to reduce their likelihood and mitigate their effects?',
                    'description' => 'Describe in detail the specific environmental, social, and economic risks that could jeopardize the success of your project. Identify the steps that you ' .
                        'will take to reduce the likelihood of their occurrence. If they were to occur, despite your best planning, what steps does your organization take to lessen the ' .
                        'long-term impact of the harmful event after it occurs. Describe the specific risk mitigation policies that your organization has put in place. Restoration is inherently ' .
                        'a risky proposition, and this is considered when assessing applications.',
                ],
                [
                    'linked_field_key' => 'pro-pit-monitor-eval-plan',
                    'name' => 'Monitoring and evaluation plan',
                    'input_type' => 'long-text',
                    'label' => 'How would you report on, monitor, and verify the impact of your project?',
                    'description' => 'Describe how you intend to track and protect the long-term impact of your proposed project through a concrete monitoring and evaluation plan. Indicate ' .
                        'the specific metrics that your organization uses to denote “success” and how you intend to gather and assure the quality of the relevant data. Identify what ' .
                        'assistance you would need to carry out this plan.',
                ],
                [
                    'linked_field_key' => 'pro-pit-proj-boundary',
                    'name' => 'Project Boundary',
                    'input_type' => 'mapInput',
                    'label' => 'Please draw or upload a geospatial polygon of your proposed restoration area.',
                    'description' => 'TerraMatch requires applicants to indicate where they will restore land. If your project is selected for funding, you would be required to precisely ' .
                        'identify the locations of each of your restoration sites with a geospatial polygon. At this stage, you must submit the approximate location of your proposed project ' .
                        'area. Do not submit the location of one of your past projects.<br>TerraMatch has several built-in ways for you to geospatially define your project area:<br>' .
                        '1. Draw your project sites on TerraMatch below; or<br>2. Upload your project\'s polygon that you have created outside of TerraMatch, please upload it here in one of ' .
                        'the following three formats: .geojson, .kml, or .zip (containing .dbf, .shp, .shx and .prj files)..<br>For more information about how to create a polygon or to see ' .
                        'if your proposed project area fits within the eligible project areas, consult this article: ' .
                        '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/15160781169691" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/15160781169691</a>',
                ],
                [
                    'linked_field_key' => 'pro-pit-sustainable-dev-goals',
                    'name' => 'Sustainable Development Goals',
                    'input_type' => 'select',
                    'multichoice' => true,
                    'label' => 'Which Sustainable Development Goals (SDGs) would your project support?',
                    'description' => 'Select the SDGs that your project will support. Before selecting, consult this webpage to understand the definition of each goal: https://sdgs.un.org/goals',
                    'options' => [
                        'No Poverty',
                        'Zero Hunger',
                        'Good Health and Well-being',
                        'Quality Education',
                        'Gender Equality',
                        'Clean Water and Sanitation',
                        'Affordable and Clean Energy',
                        'Decent Work and Economic Growth',
                        'Industry, Innovation, and Infrastructure',
                        'Reduced Inequalities',
                        'Sustainable Cities and Communities',
                        'Responsible Consumption and Production',
                        'Climate Action',
                        'Life Below Water',
                        'Life on Land',
                        'Peace, Justice, and Strong Institutions',
                        'Partnerships for the Goals',
                    ]
                ],
            ],
        ],
        'project-environmental-impact' => [
            'title' => 'Environmental Impact',
            'description' => 'Please answer the questions below so that we can understand basic information about the environmental impact of your proposed project for ' .
                'TerraFund for AFR100 Landscapes. Remember that funding is only available to projects that are located in three landscapes: the Ghana Cocoa Belt; the Lake Kivu & '.
                'Rusizi River Basin of Burundi, the Democratic Republic of the Congo, and Rwanda; and the Greater Rift Valley of Kenya. If you need more information on any of ' .
                'the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837602803355" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837602803355</a>',
            'fields' => [
                [
                    'linked_field_key' => 'pro-pit-proj-area-desc',
                    'name' => 'Description of Project Area',
                    'input_type' => 'long-text',
                    'label' => 'What are the biophysical characteristics of the project area?',
                    'description' => 'Describe in detail the physical and biological characteristics of the landscape that you intend to restore. Indicate the flora and fauna species that ' .
                        'are common to the area, along with the physical characteristics and precipitation patterns. Provide as many details as necessary to paint a picture of the ' .
                        'landscape today. Wherever possible, include figures to illustrate your points.',
                ],
                [
                    'linked_field_key' => 'pro-pit-main-degradation_causes',
                    'name' => 'Main causes of degradation',
                    'input_type' => 'long-text',
                    'label' => 'What are the main causes of degradation in the project area?',
                    'description' => 'Explain how and why the landscape has been degraded, with a focus on the past 10 years. Describe the impact of degradation on biodiversity and ecosystem ' .
                        'services and the specific challenges that it has created for the vitality of the landscape. Wherever possible, include figures to illustrate your points.',
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
                    ]
                ],
                [
                    'linked_field_key' => 'pro-pit-tot-ha',
                    'name' => 'Total Hectares to be restored',
                    'input_type' => 'number',
                    'label' => 'How many hectares of land do you intend to restore through this project?',
                    'description' => 'A hectare of land restored is defined as the total land area measured in hectares that has undergone restoration intervention. The land area under ' .
                        'restoration includes more than active tree planting. Some land may not be planted while undergoing restoration. Instead, trees could be naturally regenerated on ' .
                        'that land could without active planting. Only count land that has benefitted from tree-based restoration techniques in your total.',
                ],
                [
                    'linked_field_key' => 'pro-pit-tot-trees',
                    'name' => 'Total number of trees to be grown',
                    'input_type' => 'number',
                    'label' => 'How many trees do you intend to restore through this project?',
                    'description' => 'A tree is defined as a woody perennial plant, typically having a single stem or trunk growing to 5 meters or higher, bearing lateral branches at some ' .
                        'distance from the ground. TerraMatch counts "trees restored," not "planted." Only trees that survive to maturity after they are planted or naturally regenerated ' .
                        'should be counted toward this total. Naturally regenerating trees must attain a verifiable age of over 1 year to be counted as "restored."',
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
                    'linked_field_key' => 'pro-pit-proposed-num-sites',
                    'name' => 'Proposed Number of Sites',
                    'input_type' => 'number',
                    'label' => 'How many geographically separate locations would your project restore?',
                    'description' => 'Identify the approximate number of geographically separate locations where restoration activities will take place. If you work with hundreds of ' .
                        'individual smallholder farmers, for example, please provide your best, rounded guess. This preliminary information is important to help us understand how to ' .
                        'support your organization if you are selected and are required to create precise geospatial polygons of your restoration sites.',
                ],
                [
                    'linked_field_key' => 'pro-pit-environmental-goals',
                    'name' => 'Environmental Goals',
                    'input_type' => 'long-text',
                    'label' => 'What would be the ecological benefits of your project?',
                    'description' => 'Describe in detail the projected how this proposed project would restore the landscape’s ecosystem services and biodiversity, focusing on the degradation ' .
                        'that you highlighted above. Specify how the project’s proposed tree species and restoration interventions would lead to the desired change. Wherever possible, ' .
                        'include figures to illustrate your points.',
                ],
                [
                    'linked_field_key' => 'pro-pit-seedlings-source',
                    'name' => 'Source of Seedlings',
                    'input_type' => 'long-text',
                    'label' => 'What would be the sources of tree seedlings for your project?',
                    'description' => 'Describe how your proposed project would source the seedlings used to restore your restoration sites. If you know the names of specific nurseries or ' .
                        'seedling producers, such as government agencies, include them in your response. <br>' .
                        'If you plan to include natural regeneration in your proposal, describe the state of the existing root stock in the project area.',
                ],
                [
                    'linked_field_key' => 'pro-pit-proposed-num-nurseries',
                    'name' => 'Proposed Number of Nurseries',
                    'input_type' => 'number',
                    'label' => 'How many tree nurseries would this project establish or expand?',
                    'description' => 'Indicate the approximate number of tree nurseries that your proposed project would establish or expand. Include only nurseries that your organization or ' .
                        'an affiliated community group operates. If you source seedlings from any government-run or privately operated nurseries, do not include them in this tally. ',
                ],
            ],
        ],
        'project-socioeconomic-impact' => [
            'title' => 'Socioeconomic Impact',
            'description' => 'Please answer the questions below so that we can understand basic information on the socioeconomic impact of your project that you are looking to ' .
                'fund through TerraFund for AFR100 Landscapes. Remember that funding is only available to projects that are located in three landscapes: the Ghana Cocoa Belt; ' .
                'the Lake Kivu & Rusizi River Basin of Burundi, the Democratic Republic of the Congo, and Rwanda; and the Greater Rift Valley of Kenya. If you need more information ' .
                'on any of the questions outlined below, please visit our resources page: ' .
                '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419</a>',
            'fields' => [
                [
                    'linked_field_key' => 'pro-pit-curr-land-degradation',
                    'name' => 'Current Impact of Land Degradation in Proposed Project Area',
                    'input_type' => 'long-text',
                    'label' => 'How has land degradation impacted the livelihoods of the communities living in the project area?',
                    'description' => 'Explain how the degradation of the landscape that you described earlier has affected the livelihoods of local people, including their crop yields, income, ' .
                        'health, and education. Wherever possible, include figures to illustrate your points.',
                ],
                [
                    'linked_field_key' => 'pro-pit-proj-impact-socieconom',
                    'name' => 'Potential project impact: socioeconomic ',
                    'input_type' => 'long-text',
                    'label' => 'How would restoring the project area improve the livelihoods of local people and their communities?',
                    'description' => 'Describe in detail how this proposed project would restore economic vitality to local communities. Specify how the project’s proposed tree species and ' .
                        'restoration interventions would lead to the desired change. Include information about the supply chains or value chains that your project would improve and the steps ' .
                        'that the project would take to support local livelihoods over the next 20 years. Wherever possible, include figures to illustrate your points and explain how your ' .
                        'project would affect each relevant demographic category of people, such as women and youth. If you differentiate between “direct” and “indirect” beneficiaries, ' .
                        'define each of those terms. ',
                ],
                [
                    'linked_field_key' => 'pro-pit-proj-impact-foodsec',
                    'name' => 'Potential project impact: food security',
                    'input_type' => 'long-text',
                    'label' => 'How would the project impact local food security?',
                    'description' => 'Identify the specific ways that this proposed project would affect the provision, availability, or quality of food in the landscape. For more information, follow this link: ' .
                        '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419</a><br>' .
                        'If your project has no direct food security impact, answer with “No direct impact.” It is understood that some restoration projects do not directly improve food security.',
                ],
                [
                    'linked_field_key' => 'pro-pit-proj-impact-watersec',
                    'name' => 'Potential project impact: water security',
                    'input_type' => 'long-text',
                    'label' => [
                        'non-profit' => 'Are there any connections between the proposed project and improved water availability, quality, or flow? If so, please describe:',
                        'enterprise' => 'How would the project impact local water security?',
                    ],
                    'description' => [
                        'non-profit' => 'Identify the specific ways that this proposed project would affect water quantity, quality, stability, or accessibility in the landscape. For more information, ' .
                            'follow this link: <a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419</a>' .
                            'If your proposed project has no direct impact on improving local hydrological conditions, answer with “No direct impact.” It is understood that some restoration projects may not directly improve ' .
                            'hydrological conditions or may be difficult to assess.',
                        'enterprise' => 'Identify the specific ways that this proposed project would affect water quantity, quality, stability, or accessibility in the landscape. For more information, ' .
                            'follow this link: <a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419</a>' .
                            'If your proposed project has no direct impact on improving local hydrological conditions, answer with “No direct impact.” It is understood that some restoration projects may not directly ' .
                            'improve hydrological conditions or may be difficult to assess.',
                    ],
                ],
                [
                    'linked_field_key' => 'pro-pit-proj-impact-jobtypes',
                    'name' => 'Potential project impact: types of jobs created',
                    'input_type' => 'long-text',
                    'label' => 'What kind of new jobs would this project create?',
                    'description' => 'Restoration projects require many different skills, from nursery management to monitoring and evaluation. Describe the types of paid jobs that this project would create, ' .
                        'how employees would be compensated, and what safeguards would be put in place to ensure that workers are protected. You are encouraged to explain how this project would create ' .
                        'lasting jobs in the landscape.<br>' .
                        'TerraMatch expects its partners to abide by the principles of ethical engagement and employment to ensure fair compensation, prevent land grabbing and the use of coercion for accessing ' .
                        'land, exclude forced labor and child labor in their operations, and prevent harassment, including sexual harassment.',
                ],
                [
                    'linked_field_key' => 'pro-pit-num-jobs-created',
                    'name' => 'Number of jobs created',
                    'input_type' => 'number',
                    'label' => 'How many new paid jobs would your proposed project create?',
                    'description' => 'A “job” is defined as a person aged 18 years or older who has worked for pay, profit, or benefit for at least one hour during a given week. In this tally, include all ' .
                        'proposed full-time and part-time jobs that would work directly on this project and that your organization would pay. Do not include volunteers or project beneficiaries that are not ' .
                        'paid directly by your organization.<br>',
                ],
                [
                    'name' => 'Jobs Created',
                    'input_type' => 'tableInput',
                    'table_headers' => ['Jobs', 'Percentage'],
                    'label' => 'What is the breakdown of new paid jobs your proposed project will create?',
                    'description' => 'A “job” is defined as a person aged 18 years or older who has worked for pay, profit, or benefit for at least one hour during a given week. In this tally, include all ' .
                        'proposed full-time and part-time jobs that would work directly on this project and that your organization would pay. Do not include volunteers or project beneficiaries that are not ' .
                        'paid directly by your organization.<br>' .
                        'Estimate the percentage of the total jobs that will be full-time, part-time, held by women, and held by people between and including the ages of 18 and 35. Note that TerraMatch does ' .
                        'not support projects that directly employ people under 18 years of age.<br>' .
                        'To access more comprehensive definitions of each of these categories, consult this page: ' .
                        '<a href="https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/articles/14837599179419</a>',
                    'children' => [
                        [
                            'linked_field_key' => 'pro-pit-pct-employees-men',
                            'name' => '% of total employees that would be men',
                            'input_type' => 'number',
                            'label' => '% of total employees that would be men?',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'pro-pit-pct-employees-women',
                            'name' => '% of total employees that would be women',
                            'input_type' => 'number',
                            'label' => '% of total employees that would be women?',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'pro-pit-pct-employees-18to35',
                            'name' => '% of total employees that would be between the ages of 18 and 35',
                            'input_type' => 'number',
                            'label' => '% of total employees that would be between the ages of 18 and 35?',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'pro-pit-pct-employees-older35',
                            'name' => '% of total employees that would be older than 35 years of age',
                            'input_type' => 'number',
                            'label' => '% of total employees that would be older than 35 years of age?',
                            'validation' => $percentageValidationRules,
                        ],
                    ]
                ],
                [
                    'linked_field_key' => 'pro-pit-beneficiaries',
                    'name' => 'Project Beneficiaries',
                    'input_type' => 'number',
                    'label' => 'How many people would this project benefit?',
                    'description' => 'A "beneficiary" is defined as anyone who would derive a direct or indirect benefit from your proposed project, excluding your organization\'s employees.',
                ],
                [
                    'name' => 'Project Beneficiaries',
                    'input_type' => 'tableInput',
                    'table_headers' => ['Beneficiary type', 'Beneficiary Count'],
                    'label' => 'What is the breakdown of people that this project would benefit?',
                    'description' => 'Estimate the percentage of the total beneficiaries that are women, younger than 35 years of age, smallholder farmers and large-scale farmers. ' .
                        'TerraMatch does not support jobs destined for people under 18 years old. Smallholder farmers operate on less than 2 hectares of land. Large-scale farmers ' .
                        'operate on more than 2 hectares of land.',
                    'children' => [
                        [
                            'linked_field_key' => 'pro-pit-pct-beneficiaries-women',
                            'name' => '% of project beneficiaries that would be women',
                            'input_type' => 'number',
                            'label' => '% of project beneficiaries that would be women',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'pro-pit-pct-beneficiaries-small',
                            'name' => '% of project beneficiaries that would be smallholder farmers',
                            'input_type' => 'number',
                            'label' => '% of project beneficiaries that would be smallholder farmers',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'pro-pit-pct-beneficiaries-large',
                            'name' => '% of project beneficiaries that would be large-scale farmers',
                            'input_type' => 'number',
                            'label' => '% of project beneficiaries that would be large-scale farmers',
                            'validation' => $percentageValidationRules,
                        ],
                        [
                            'linked_field_key' => 'pro-pit-pct-beneficiaries-35below',
                            'name' => '% of project beneficiaries that would be be younger than 35 years old',
                            'input_type' => 'number',
                            'label' => '% of project beneficiaries that would be be younger than 35 years old',
                            'validation' => $percentageValidationRules,
                        ],
                    ],
                ],
            ],
        ],
        'project-additional-info' => [
            'title' => 'Additional Information ',
            'description' => 'If you need more information on any of the questions outlined, please visit our knowledge base: ' .
            '<a href="https://terramatchsupport.zendesk.com/hc/en-us/categories/13162555518491-TerraFund-for-AFR100-Landscapes" target="_blank">https://terramatchsupport.zendesk.com/hc/en-us/categories/13162555518491-TerraFund-for-AFR100-Landscapes</a>',
            'fields' => [
                [
                    'linked_field_key' => 'pro-pit-fcol-add',
                    'name' => 'Additional Documents',
                    'input_type' => 'file',
                    'label' => 'Upload any additional documents',
                    'multichoice' => true,
                    'validation' => json_encode(['required' => false]),
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
                        'Email',
                        'Other',
                    ]
                ],
            ],
        ],
    ],
];



