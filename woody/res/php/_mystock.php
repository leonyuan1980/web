<?php
require_once('_stock.php');
require_once('_emptygroup.php');
require_once('_editmergeform.php');
require_once('_editstockoptionform.php');
require_once('/php/stockhis.php');
require_once('/php/benfordimagefile.php');
require_once('/php/ui/referenceparagraph.php');
require_once('/php/ui/stockparagraph.php');
require_once('/php/ui/ahparagraph.php');
require_once('/php/ui/etfparagraph.php');
require_once('/php/ui/hsharesmaparagraph.php');
require_once('/php/ui/etfsmaparagraph.php');
require_once('/php/ui/fundestparagraph.php');
require_once('/php/ui/fundhistoryparagraph.php');
require_once('/php/ui/stockhistoryparagraph.php');
require_once('/php/ui/nvclosehistoryparagraph.php');
require_once('/php/ui/tradingparagraph.php');

function _echoBenfordParagraph($ref)
{
	$sym = $ref->GetSym();
	if ($sym->IsTradable() == false)		return;
//	if ($sym->IsSymbolUS() == false)		return;
	
	$sql = new AnnualIncomeStrSql($ref->GetStockId());
	if ($str = $sql->GetCloseNow())
	{
		if ($str == 'NODATA')		return;
	}
	else
	{
		if ($ar = YahooUpdateFinancials($ref))
		{
			foreach ($ar as $strDate => $strVal)
			{
				$sql->Write($strDate, $strVal);
			}
		}
		else
		{
			$ymd = new NowYMD();
			$sql->Write($ymd->GetYMD(), 'NODATA');
			return;
		}
	}

   	if ($result = $sql->GetAll()) 
   	{
   		$ar = array();
   		while ($record = mysql_fetch_assoc($result)) 
   		{
			$ar = array_merge($ar, explode(',', $record['close']));
    	}
   		@mysql_free_result($result);

    	$jpg = new BenfordImageFile();
    	$jpg->Draw($ar);
    	EchoParagraph($jpg->GetAll());
    }
}

function _echoMyStockTransactions($strMemberId, $ref)
{
    $arGroup = array();
    $strStockId = $ref->GetStockId();
	$sql = new StockGroupSql($strMemberId);
	if ($result = $sql->GetAll()) 
	{
		while ($record = mysql_fetch_assoc($result)) 
		{
		    $strGroupId = $record['id'];
		    if ($strGroupItemId = SqlGroupHasStock($strGroupId, $strStockId, true))
		    {
		        $arGroup[$strGroupId] = $strGroupItemId;
		    }
		}
		@mysql_free_result($result);
	}
	
	$iCount = count($arGroup);
	if ($iCount == 0)    return;
	foreach ($arGroup as $strGroupId => $strGroupItemId)
	{
		EchoTransactionParagraph($strGroupId, $ref);
	}
	
	if ($iCount == 1)
	{
	    StockEditTransactionForm(STOCK_TRANSACTION_NEW, $strGroupId, $strGroupItemId);
	}
	else
	{
	    StockMergeTransactionForm($arGroup);
	}
}

function _hasSmaDisplay($sym)
{
    if ($sym->IsSinaFund())		return false;
    else if ($sym->IsFundA())   	return false;
    else if ($sym->IsForex())   	return false;
    return true;
}

function _getMyStockLinks($sym)
{
	$strSymbol = $sym->GetSymbol();
    $str = GetStockOptionLink(STOCK_OPTION_EDIT, $strSymbol);
   	$str .= ' '.GetStockOptionLink(STOCK_OPTION_SPLIT, $strSymbol);
   	$str .= ' '.GetStockOptionLink(STOCK_OPTION_NETVALUE, $strSymbol);
   	if (SqlGetEtfPair($strSymbol) == false)
   	{
   		$str .= ' '.GetStockOptionLink(STOCK_OPTION_EMA, $strSymbol);
   	}
   	if ($sym->IsSymbolA())
   	{
    	if ($sym->IsFundA())
    	{
    		$str .= ' '.GetStockOptionLink(STOCK_OPTION_ETF, $strSymbol);
    	}
    	else if ($sym->IsTradable())
    	{
    		$str .= ' '.GetStockOptionLink(STOCK_OPTION_AH, $strSymbol);
    	}
   	}
    else if ($sym->IsSymbolH())
    {
    	$str .= ' '.GetStockOptionLink(STOCK_OPTION_HA, $strSymbol);
    	$str .= ' '.GetStockOptionLink(STOCK_OPTION_ADR, $strSymbol);
    }
    else
    {
    	if ($sym->IsTradable())
    	{
    		$str .= ' '.GetStockOptionLink(STOCK_OPTION_ETF, $strSymbol);
    	}
    }
    return $str;
}

function _echoMyStockData($ref, $strMemberId, $bAdmin)
{
    $hshare_ref = false;
    $etf_ref = false;
    $sym = $ref->GetSym();
    $strSymbol = $sym->GetSymbol();
    if ($sym->IsFundA())
    {
        $fund = StockGetFundReference($strSymbol);
        $ref = $fund->stock_ref; 
    	$etf_ref = StockGetEtfReference($strSymbol);
    }
    else
    {
    	if ($ref_ar = StockGetHShareReference($sym))				list($ref, $hshare_ref) = $ref_ar;
    	else if ($etf_ref = StockGetEtfReference($strSymbol))	$ref = $etf_ref;
//   		else														$ref = StockGetReference($strSymbol, $sym);
    }
    
    if ($ref->HasData())
    {
    	EchoReferenceParagraph(array($ref));
    	if ($etf_ref)
    	{
    		EchoEtfListParagraph(array($etf_ref));
    		EchoEtfTradingParagraph($etf_ref);
    		EchoEtfHistoryParagraph($etf_ref);
    	}
    	else if ($sym->IsFundA())
    	{
    		if ($fund->fOfficialNetValue)	EchoFundEstParagraph($fund);
    		EchoFundTradingParagraph($fund);
    		EchoFundHistoryParagraph($fund);
    	}
    	else
    	{
    		if ($hshare_ref)
    		{
    			if ($strSymbol != $hshare_ref->GetStockSymbol())	RefSetExternalLinkMyStock($hshare_ref);
    			if ($hshare_ref->a_ref)								EchoAhParagraph(array($hshare_ref));
    			if ($hshare_ref->adr_ref)							EchoAdrhParagraph(array($hshare_ref));
    		}
    		if ($sym->IsSymbolA())
    		{
    			if ($hshare_ref)	EchoAhTradingParagraph($hshare_ref);
    			else 				EchoTradingParagraph($ref);
    		}
    		EchoNvCloseHistoryParagraph($ref);
    	}
    
    	if ($etf_ref)   			EchoEtfSmaParagraph($etf_ref);
    	if (_hasSmaDisplay($sym))
    	{
    		if ($hshare_ref)		EchoHShareSmaParagraph($ref, $hshare_ref);
    		else	        		EchoSmaParagraph($ref);
    	}
    	EchoStockHistoryParagraph($ref);
    	
    	_echoBenfordParagraph($ref);
    
    	if ($strMemberId)		_echoMyStockTransactions($strMemberId, $ref);
    }
    
    if ($bAdmin)
    {
     	$str = GetMyStockLink();
    	if ($strStockId = $ref->GetStockId())
    	{
    		$str .= '<br />id='.$strStockId;
    		$str .= '<br />'._getMyStockLinks($sym);
    		if ($ref->HasData())
    		{
    			$str .= '<br />'.$ref->DebugLink();
    			if ($sym->IsFundA())			$str .= '<br />'.$fund->DebugLink();
    			if (_hasSmaDisplay($sym)) 		$str .= '<br />'.GetTableColumnSma().' '.$ref->DebugConfigLink();
    		}
    	}
    	EchoParagraph($str);
    }
}

function EchoAll()
{
	global $acct;
	
	$bAdmin = $acct->IsAdmin();
    if ($ref = $acct->EchoStockGroup())
    {
    	_echoMyStockData($ref, $acct->GetLoginId(), $bAdmin);
    }
    else if ($bAdmin)
    {
    	EchoStockParagraph($acct->GetStart(), $acct->GetNum());
    }
    $acct->EchoLinks();
}

function EchoMetaDescription()
{
	global $acct;
	
    $str = $acct->GetSymbolDisplay($acct->GetWhoseAllDisplay());
	$str .= '参考数据, AH对比, SMA均线, 布林线, 净值估算等本网站提供的内容. 可以用来按代码查询股票基本情况, 登录状态下还显示相关股票分组中的用户交易记录.';
    EchoMetaDescriptionText($str);
}

function EchoTitle()
{
	global $acct;
	
    $str = $acct->GetSymbolDisplay(ALL_STOCK_DISPLAY);
    echo $str;
}

    $acct = new SymbolAcctStart();
    
?>

