<?php

use Contao\DC_Table;
use Contao\Input;
use CatalogManager\ExportBundle\DataContainer\Export;

$GLOBALS['TL_DCA']['tl_catalog_export'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'onload_callback' => [
            ['export.datacontainer.export', 'saveTable'],
            ['export.datacontainer.export', 'callExport']
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
            'filter' => [['destination=?', Input::get('destination')]]
        ],
        'label' => [
            'showColumns' => true,
            'fields' => ['name', 'type']
        ],
        'operations' => [
            'edit' => [
                'href' => 'act=edit',
                'icon' => 'header.svg'
            ],
            'delete' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['delete'],
                'href' => 'act=delete',
                'icon' => 'delete.svg',
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"'
            ],
            'show' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['show'],
                'href' => 'act=show',
                'icon' => 'show.svg'
            ],
            'export' => [
                'label' => &$GLOBALS['TL_LANG']['tl_catalog_export']['export'],
                'href' => 'call=export&pid=' . Input::get('id'),
                'icon' => 'tablewizard.svg'
            ]
        ],
        'global_operations' => [
            'all' => [
                'label' => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href' => 'act=select',
                'class' => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset()" accesskey="e"'
            ],
            'back' => [
                'icon' => 'back.svg',
                'attributes' => '',
                'href' => '',
                'label' => &$GLOBALS['TL_LANG']['MSC']['backBT'],
                'button_callback' => [Export::class, 'generateBackLink'],
            ]
        ]
    ],
    'palettes' => [
        'default' => 'type,name,limit,offset,destination,match,order,columns,includeHeader,parser',
    ],
    'fields' => [
        'id' => [
            'sql' => "int(10) unsigned NOT NULL auto_increment"
        ],
        'tstamp' => [
            'sql' => "int(10) unsigned NOT NULL default '0'"
        ],
        'name' => [
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
            'inputType' => 'select',
            'eval' => [
                'chosen' => true,
                'maxlength' => 12,
                'tl_class' => 'w50',
                'mandatory' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true,
            ],
            'options_callback' => ['export.datacontainer.export', 'getTypes'],
            'reference' => &$GLOBALS['TL_LANG']['tl_catalog_export']['reference']['type'],
            'filter' => true,
            'exclude' => true,
            'sql' => "varchar(12) NOT NULL default ''"
        ],
        'includeHeader' => [
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr',
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'parser' => [
            'inputType' => 'checkbox',
            'eval' => [
                'tl_class' => 'clr',
            ],
            'exclude' => true,
            'sql' => "char(1) NOT NULL default ''"
        ],
        'destination' => [
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
            'inputType' => 'catalogTaxonomyWizard',
            'eval' => [
                'tl_class' => 'clr',
                'dcTable' => 'tl_catalog_export',
                'taxonomyTable' => [Export::class, 'getTable'],
                'taxonomyEntities' => [Export::class, 'getFields']
            ],
            'exclude' => true,
            'sql' => "blob NULL"
        ],
        'order' => [
            'inputType' => 'catalogDuplexSelectWizard',
            'eval' => [
                'chosen' => true,
                'blankOptionLabel' => '-',
                'includeBlankOption' => true,
                'mainLabel' => 'catalogManagerFields',
                'dependedLabel' => 'catalogManagerOrder',
                'mainOptions' => [Export::class, 'getSortableFields'],
                'dependedOptions' => [Export::class, 'getOrderItems']
            ],
            'exclude' => true,
            'sql' => "blob NULL"
        ],
        'columns' => [
            'inputType' => 'checkboxWizard',
            'eval' => [
                'multiple' => true,
                'tl_class' => 'clr'
            ],
            'options_callback' => [Export::class, 'getColumns'],
            'exclude' => true,
            'sql' => "blob NULL"
        ],
        'limit' => [
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