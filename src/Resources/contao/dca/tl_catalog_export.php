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

                'id' => 'primary'
            ]
        ]
    ],

    'list' => [

        'sorting' => [

            'mode' => 0,
        ],

        'label' => [

            'showColumns' => true,
            'fields' => [ 'type', 'name' ]
        ],

        'operations' => [

            'export' => [

                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['export'],
                'href' => 'call=export',
                'icon' => 'edit.gif'
            ],

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

        'default' => 'type,name,limit,offset,table,match,order,includeHeader,parser',
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

        'table' => [

            'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['table'],
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