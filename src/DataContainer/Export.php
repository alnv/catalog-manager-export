<?php

namespace CatalogManager\ExportBundle\DataContainer;

use CatalogManager\CatalogFieldBuilder as CatalogFieldBuilder;
use CatalogManager\Toolkit as Toolkit;


class Export {


    public function generateBackLink( $strHref, $strLabel, $strTitle, $strClass, $strIcon, $strTable ) {

        return '<a href="/contao?do='.\Input::get('do') . '&rt='. REQUEST_TOKEN . '" class="'. $strClass .'" title="" '. $strIcon .' onclick="Backend.getScrollOffset()">'. $strLabel .'</a>';
    }


    public function getTypes() {

        return [ 'xls', 'xlsx', 'csv' ];
    }


    public function getTable() {

        return \Input::get( 'destination' );
    }

    public function getColumns( \DataContainer $objDataContainer ) {

        $arrReturn = [];

        if ( !\Input::get( 'destination' ) ) {

            return $arrReturn;
        }

        $objDatabase = \Database::getInstance();
        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( \Input::get( 'destination' ) );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !$objDatabase->fieldExists( $strFieldname, \Input::get( 'destination' ) ) ) continue;

            $arrReturn[ $strFieldname ] = is_array( $arrField['_dcFormat']['label'] ) && isset( $arrField['_dcFormat']['label'][0] ) ? $arrField['_dcFormat']['label'][0] : $strFieldname;
        }


        return $arrReturn;
    }


    public function getFields( \DataContainer $objDataContainer = null, $strTable ) {

        $arrReturn = [];
        $objDatabase = \Database::getInstance();
        $arrForbiddenTypes = [ 'upload', 'textarea' ];

        if ( !$strTable ) {

            return $arrReturn;
        }

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize( $strTable );
        $arrFields = $objCatalogFieldBuilder->getCatalogFields( true, null );

        foreach ( $arrFields as $strFieldname => $arrField ) {

            if ( !$objDatabase->fieldExists( $strFieldname, $strTable ) ) continue;
            if ( in_array( $arrField['type'], Toolkit::excludeFromDc() ) ) continue;
            if ( in_array( $arrField['type'], $arrForbiddenTypes ) ) continue;

            $arrReturn[ $strFieldname ] = $arrField['_dcFormat'];
        }

        return $arrReturn;
    }


    public function getSortableFields( $objWidget ) {

        $arrReturn = [];
        $objDatabase = \Database::getInstance();
        $objModule = $objDatabase->prepare( sprintf( 'SELECT * FROM %s WHERE id = ?', $objWidget->strTable ) )->limit(1)->execute( $objWidget->currentRecord );
        $arrFields = $this->getFields( null, $objModule->destination );

        if ( is_array( $arrFields ) && !empty( $arrFields ) ) {

            foreach ( $arrFields as $strFieldname => $arrField ) {

                $arrReturn[ $strFieldname ] = isset( $arrField['label'][0] ) ? $arrField['label'][0] : $strFieldname;
            }
        }

        return $arrReturn;
    }


    public function getOrderItems() {

        return [ 'ASC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['asc'], 'DESC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['desc'] ];
    }


    public function saveTable() {

        $strId = \Input::get( 'id' );
        $objDatabase = \Database::getInstance();
        $strTable = \Input::get( 'destination' );

        if ( !$strId || !$strTable ) {

            return null;
        }

        $objDatabase->prepare("UPDATE tl_catalog_export SET `destination` = ? WHERE id = ?")->execute( $strTable, $strId );
    }


    public function callExport() {

        if ( !\Input::get( 'call' ) ) {

            return null;
        }

        $objDatabase = \Database::getInstance();
        $objExport = $objDatabase->prepare('SELECT * FROM tl_catalog_export WHERE id = ?')->limit(1)->execute( \Input::get('id') );

        if ( $objExport->numRows ) {

            $objExport = new \CatalogManager\ExportBundle\Library\Export( $objExport->row() );
            $objExport->initialize();
        }

        \Controller::redirect( preg_replace( '/&(amp;)?call=[^&]*/i', '', preg_replace( '/&(amp;)?' . preg_quote( "1", '/' ) . '=[^&]*/i', '', \Environment::get('request') ) ) );
    }
}