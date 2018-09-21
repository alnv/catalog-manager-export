<?php

$GLOBALS['TL_DCA']['tl_catalog_export'] = [

    'config' => [

        'dataContainer' => 'Table',
        'enableVersioning' => true,

        'onload_callback' => [

            [ 'export.datacontainer.export', 'saveTable' ],
            [ 'export.datacontainer.export', 'callExport' ]
        ],

        'sql' => [

            'keys' => [

                'id' => 'primary',
                'destination' => 'index'
            ]
        ]
    ],

    'list' => [

        'sorting' => [

            'mode' => 2,
            'flag' => 1,
            'fields' => ['name'],
            'panelLayout' => 'filter;sort,search,limit',
            'filter' => [ [ 'destination=?', \Input::get('destination') ] ]
        ],

        'label' => [

            'showColumns' => true,
            'fields' => [ 'name', 'type' ]
        ],

        'operations' => [

            'edit' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['edit'],
                'href' => 'act=edit',
                'icon' => 'header.gif'
            ],


            'delete' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.gif',
                'attributes' => 'onclick="if(!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\'))return false;Backend.getScrollOffset()"'
            ],

            'show' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['show'],
                'href' => 'act=show',
                'icon' => 'show.gif'
            ],

            'export' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['export'],
                'href' => 'call=export',
                'icon' => 'tablewizard.gif'
            ]
        ],

        'global_operations' => [

            'all' => [

                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ]
        ]
    ],

    'palettes' => [

        'default' => 'type,name,limit,offset,destination,match,order,includeHeader,parser',
    ],

    'fields' => [

        'id' => [

            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],

        'tstamp' => [

            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],

        'name' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['name'],
            'inputType' => 'text',

            'eval' => [

                'mandatory' => true,
                'tl_class' => 'w50',
                'maxlength' => 128
            ],

            'search' => true,
            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'type' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['type'],
            'inputType' => 'select',

            'eval' => [

                'chosen' => true,
                'maxlength' => 12,
                'tl_class' => 'w50',
                'mandatory' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true,
            ],

            'options_callback' => [ 'export.datacontainer.export', 'getTypes' ],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_export']['reference']['type'],

            'filter' => true,
            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],

        'includeHeader' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['includeHeader'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'clr',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'parser' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['parser'],
            'inputType' => 'checkbox',

            'eval' => [

                'tl_class' => 'clr',
            ],

            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],

        'destination' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['destination'],
            'inputType' => 'text',

            'eval' => [

                'readonly' => true,
                'maxlength' => 128,
                'tl_class' => 'w50',
                'mandatory' => true
            ],

            'exclude' => true,
            'sql' => "varchar(128) NOT NULL default ''"
        ],

        'match' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['match'],
            'inputType' => 'catalogTaxonomyWizard',

            'eval' => [

                'tl_class' => 'clr',
                'dcTable' => 'tl_catalog_export',
                'taxonomyTable' => [ 'CatalogManager\ExportBundle\DataContainer\Export', 'getTable' ],
                'taxonomyEntities' => [ 'CatalogManager\ExportBundle\DataContainer\Export', 'getFields' ]
            ],

            'exclude' => true,
            'sql' => "blob NULL"
        ],

        'order' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['order'],
            'inputType' => 'catalogDuplexSelectWizard',

            'eval' => [

                'chosen' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true,
                'mainLabel' => 'catalogManagerFields',
                'dependedLabel' => 'catalogManagerOrder',
                'mainOptions' => [ 'CatalogManager\ExportBundle\DataContainer\Export', 'getSortableFields' ],
                'dependedOptions' => [ 'CatalogManager\ExportBundle\DataContainer\Export', 'getOrderItems' ]
            ],

            'exclude' => true,
            'sql' => "blob NULL"
        ],

        'limit' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['limit'],
            'inputType' => 'text',
            'default' => 0,

            'eval' => [

                'minval' => 0,
                'maxval' => 1000,
                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ],

        'offset' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['offset'],
            'inputType' => 'text',
            'default' => 0,

            'eval' => [

                'minval' => 0,
                'maxval' => 1000,
                'tl_class' => 'w50'
            ],

            'exclude' => true,
            'sql' => "smallint(5) unsigned NOT NULL default '0'"
        ]
    ]
];