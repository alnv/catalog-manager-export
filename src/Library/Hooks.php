<?php

namespace CatalogManager\ExportBundle\Library;


class Hooks {


    public function setExport( $strName ) {

        if ( TL_MODE !== 'BE' ) {

            return null;
        }

        if ( !isset( $GLOBALS['TL_CATALOG_MANAGER'] ) || !is_array( $GLOBALS['TL_CATALOG_MANAGER'] ) ) {

            return null;
        }

        if ( isset( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'] ) && isset( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strName ] ) ) {

            if ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strName ]['useExport'] ) {

                array_insert( $GLOBALS['TL_DCA'][ $strName ]['list']['global_operations'], 0, [

                    'export' => [

                        'icon' => 'header.svg',
                        'label' => [ 'Export', '' ], // @todo
                        'attributes' => 'onclick="Backend.getScrollOffset()"',
                        'href' => 'table=tl_catalog_export&destination=' . $strName
                    ]
                ]);
            }
        }
    }


    public function modifyBackendModule( &$arrModule, $arrCatalog ) {

        if ( $arrCatalog['useExport'] ) {

            $arrModule['tables'][] = 'tl_catalog_export';
        }
    }
}