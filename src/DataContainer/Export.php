<?php

namespace CatalogManager\ExportBundle\DataContainer;

use Alnv\CatalogManagerBundle\CatalogFieldBuilder;
use Alnv\CatalogManagerBundle\Toolkit;
use Contao\Input;
use Contao\System;
use Contao\Database;
use Contao\Environment;
use Contao\Controller;
use CatalogManager\ExportBundle\Library\Export as Ex;

class Export
{

    public function generateBackLink($strHref, $strLabel, $strTitle, $strClass, $strIcon, $strTable)
    {

        $strRequestToken = System::getContainer()->get('contao.csrf.token_manager')->getDefaultTokenValue();
        return '<a href="/contao?do=' . Input::get('do') . '&rt=' . $strRequestToken . '" class="' . $strClass . '" title="" ' . $strIcon . ' onclick="Backend.getScrollOffset()">' . $strLabel . '</a>';
    }

    public function getTypes(): array
    {
        return ['xls', 'xlsx', 'csv'];
    }

    public function getTable()
    {
        return Input::get('destination') ?: '';
    }

    public function getColumns($objDataContainer = null)
    {

        $arrReturn = [];

        if (!Input::get('destination')) {
            return $arrReturn;
        }

        $objDatabase = Database::getInstance();
        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize(Input::get('destination'));
        $arrFields = $objCatalogFieldBuilder->getCatalogFields();

        foreach ($arrFields as $strFieldname => $arrField) {

            if (!$objDatabase->fieldExists($strFieldname, Input::get('destination'))) continue;

            $arrReturn[$strFieldname] = is_array($arrField['_dcFormat']['label']) && isset($arrField['_dcFormat']['label'][0]) ? $arrField['_dcFormat']['label'][0] : $strFieldname;
        }


        return $arrReturn;
    }

    public function getFields($objDataContainer, $strTable)
    {

        $arrReturn = [];
        $objDatabase = Database::getInstance();
        $arrForbiddenTypes = ['upload', 'textarea'];

        if (!$strTable) {
            return $arrReturn;
        }

        $objCatalogFieldBuilder = new CatalogFieldBuilder();
        $objCatalogFieldBuilder->initialize($strTable);
        $arrFields = $objCatalogFieldBuilder->getCatalogFields();

        foreach ($arrFields as $strFieldname => $arrField) {

            if (!$objDatabase->fieldExists($strFieldname, $strTable)) continue;
            if (in_array($arrField['type'], Toolkit::excludeFromDc())) continue;
            if (in_array($arrField['type'], $arrForbiddenTypes)) continue;

            $arrReturn[$strFieldname] = $arrField['_dcFormat'];
        }

        return $arrReturn;
    }

    public function getSortableFields($objWidget)
    {

        $arrReturn = [];
        $objDatabase = Database::getInstance();
        $objModule = $objDatabase->prepare(sprintf('SELECT * FROM %s WHERE id = ?', $objWidget->strTable))->limit(1)->execute($objWidget->currentRecord);
        $arrFields = $this->getFields(null, $objModule->destination);

        if (is_array($arrFields) && !empty($arrFields)) {
            foreach ($arrFields as $strFieldname => $arrField) {
                $arrReturn[$strFieldname] = isset($arrField['label'][0]) ? $arrField['label'][0] : $strFieldname;
            }
        }

        return $arrReturn;
    }

    public function getOrderItems()
    {
        return ['ASC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['asc'], 'DESC' => &$GLOBALS['TL_LANG']['MSC']['CATALOG_MANAGER']['desc']];
    }

    public function saveTable()
    {

        $strId = Input::get('id');
        $objDatabase = Database::getInstance();
        $strTable = Input::get('destination');

        if (!$strId || !$strTable) {

            return null;
        }

        $objDatabase->prepare("UPDATE tl_catalog_export SET `destination` = ? WHERE id = ?")->execute($strTable, $strId);
    }

    public function callExport()
    {

        if (!Input::get('call')) {
            return null;
        }

        $objDatabase = Database::getInstance();
        $objExport = $objDatabase->prepare('SELECT * FROM tl_catalog_export WHERE id = ?')->limit(1)->execute(Input::get('id'));

        if ($objExport->numRows) {
            $objExport = new Ex($objExport->row());
            $objExport->initialize();
        }

        Controller::redirect(preg_replace('/&(amp;)?call=[^&]*/i', '', preg_replace('/&(amp;)?' . preg_quote("1", '/') . '=[^&]*/i', '', Environment::get('request'))));
    }
}