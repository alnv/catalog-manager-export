<?php

use CatalogManager\ExportBundle\Library\Hooks;

$GLOBALS['TL_HOOKS']['catalogManagerModifyBackendModule'][] = [Hooks::class, 'modifyBackendModule'];
$GLOBALS['TL_HOOKS']['loadDataContainer'][] = ['export.library.hooks', 'setExport'];