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

                        'icon' => 'tablewizard.svg',
                        'attributes' => 'onclick="Backend.getScrollOffset()"',
                        'href' => 'table=tl_catalog_export&destination=' . $strName,
                        'label' => &$GLOBALS['TL_LANG']['MOD']['catalog-manager-export']
                    ]
                ]);
            }
        }
    }


    public function modifyBackendModule( &$arrModule, $arrCatalog ) {

        if ( $arrCatalog['useExport'] || $this->exportUsedInChildrenTables( $arrCatalog['cTables'] ) ) {

            $arrModule['tables'][] = 'tl_catalog_export';
        }
    }


    protected function exportUsedInChildrenTables( $arrTables ) {

        if ( empty( $arrTables ) ) {

            return false;
        }

        foreach ( $arrTables as $strTable ) {

            if ( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['useExport'] ) {

                return true;
            }

            if ( !empty( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['cTables'] ) ) {

                return $this->exportUsedInChildrenTables( $GLOBALS['TL_CATALOG_MANAGER']['CATALOG_EXTENSIONS'][ $strTable ]['cTables'] );
            }
        }

        return false;
    }
}