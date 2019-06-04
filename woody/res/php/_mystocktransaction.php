<?php
require_once('_stock.php');

function EchoAll()
{
    if ($strGroupId = UrlGetQueryValue('groupid'))
    {
        $arSymbol = SqlGetStocksArray($strGroupId, true);
        StockPrefetchArrayData($arSymbol);
   		EchoStockGroupParagraph();

        $iStart = UrlGetQueryInt('start');
        $iNum = UrlGetQueryInt('num', DEFAULT_NAV_DISPLAY);
        if ($strSymbol = UrlGetQueryValue('symbol'))
        {   // Display transactions of a stock
            $strAllLink = StockGetAllTransactionLink($strGroupId);
            $strStockLinks = StockGetGroupTransactionLinks($strGroupId, $strSymbol);
            EchoParagraph($strAllLink.' '.$strStockLinks);
           	EchoTransactionParagraph($strGroupId, new MyStockReference($strSymbol), $iStart, $iNum);
        }
        else
        {   // Display transactions of the whole group
            $strCombineLink = GetPhpLink(STOCK_PATH.'combinetransaction', 'groupid='.$strGroupId, '合并记录');
            $strStockLinks = StockGetGroupTransactionLinks($strGroupId);
            EchoParagraph($strCombineLink.' '.$strStockLinks);
           	EchoTransactionParagraph($strGroupId, false, $iStart, $iNum);
        }
    }
    EchoPromotionHead('transaction');
    EchoStockCategory();
}

function EchoMetaDescription()
{
    $str = _GetWhoseStockGroupDisplay(false, UrlGetQueryValue('groupid'));
    $strStock = _GetAllDisplay(UrlGetQueryValue('symbol'));
    $str .= STOCK_GROUP_DISPLAY.$strStock.'交易记录管理页面. 提供现有股票交易记录和编辑删除链接, 主要用于某组股票交易记录超过一定数量后的显示. 少量的股票交易记录一般直接显示在该股票页面而不是在这里.';
    EchoMetaDescriptionText($str);
}

function EchoTitle()
{
    $str = _GetWhoseStockGroupDisplay(AcctIsLogin(), UrlGetQueryValue('groupid'));
    $strStock = _GetAllDisplay(UrlGetQueryValue('symbol'));
    $str .= STOCK_GROUP_DISPLAY.$strStock.'交易记录';
    echo $str;
}

    AcctAuth();

?>

