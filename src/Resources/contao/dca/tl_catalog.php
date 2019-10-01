<?php

$GLOBALS['TL_DCA']['tl_catalog']['palettes']['default'] .= ';{export_legend:hide},useExport';
$GLOBALS['TL_DCA']['tl_catalog']['palettes']['modifier'] .= ';{export_legend:hide},useExport';

$GLOBALS['TL_DCA']['tl_catalog']['fields']['useExport'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_catalog']['useExport'],
    'inputType' => 'checkbox',
    'eval' => [
        'tl_class' => 'clr',
    ],
    'exclude' => true,
    'sql' => "char(1) NOT NULL default ''"
];