<?php
require_once('sqltable.php');

// ****************************** StockSql class *******************************************************
class StockSql extends TableSql
{
    function StockSql()
    {
        parent::TableSql(TABLE_STOCK);
        $this->Create();
    }

    function Create()
    {
    	$str = ' `symbol` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,'
         	. ' `name` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL ,'
         	. ' UNIQUE ( `symbol` )';
    	return $this->CreateTable($str);
    }
    
    function Insert($strSymbol, $strName)
    {
    	$strName = UrlCleanString($strName);
    	return $this->InsertData("(id, symbol, name) VALUES('0', '$strSymbol', '$strName')");
    }

	function Update($strId, $strSymbol, $strName)
    {
    	$strName = UrlCleanString($strName);
		return $this->UpdateById("symbol = '$strSymbol', name = '$strName'", $strId);
	}
	
    function Get($strSymbol)
    {
    	return $this->GetSingleData(_SqlBuildWhere('symbol', $strSymbol));
    }
}

// ****************************** Stock table *******************************************************
function SqlInsertStock($strSymbol, $strName)
{
	DebugString('SqlInsertStock '.$strSymbol);
	$sql = new StockSql();
	return $sql->Insert($strSymbol, $strName);
}

function SqlUpdateStock($strId, $strSymbol, $strName)
{
	$sql = new StockSql();
	return $sql->Update($strId, $strSymbol, $strName);
}

function SqlGetAllStock($iStart, $iNum)
{
    return SqlGetTableData(TABLE_STOCK, false, '`symbol` ASC', _SqlBuildLimit($iStart, $iNum));
}

function SqlGetStock($strSymbol)
{
	$sql = new StockSql();
	return $sql->Get($strSymbol);
}

function SqlGetStockId($strSymbol)
{
	$sql = new StockSql();
	if ($strStockId = $sql->GetId($strSymbol))
	{
		return $strStockId;
	}
   	DebugString($strSymbol.' not in stock table');
	return false;
}

function SqlGetStockById($strId)
{
	return SqlGetTableDataById(TABLE_STOCK, $strId);
}

function SqlGetStockSymbol($strId)
{
    if ($record = SqlGetStockById($strId))
    {
		return $record['symbol'];
    }
	return false;
}

function SqlDeleteStock($strId)
{
	SqlDeleteTableDataById(TABLE_STOCK, $strId);
}

// ****************************** Other SQL and stock related functions *******************************************************

function SqlUpdateStockChineseDescription($strSymbol, $strChinese)
{
    $bTemp = false;
    $strChinese = trim($strChinese);
    $str = substr($strChinese, 0, 2); 
    if ($str == 'XD' || $str == 'XR' || $str == 'DR') 
    {
        DebugString($strChinese);
        $strChinese = substr($strChinese, 2);
        $bTemp = true;
    }
    
    if ($record = SqlGetStock($strSymbol))
    {
        if ($bTemp == false)
        {
            if (strlen($strChinese) > strlen($record['name']))
            {
                SqlUpdateStock($record['id'], $strSymbol, $strChinese);
                DebugString('UpdateStock:'.$strSymbol.' '.$strChinese);
            }
        }
    }
    else
    {
        SqlInsertStock($strSymbol, $strChinese);
        DebugString('InsertStock:'.$strSymbol.' '.$strChinese);
    }
    return $bTemp;
}

?>
