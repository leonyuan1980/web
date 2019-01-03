var _iTotalMenus = 6;
var _arMenus = new Array("ar1688", "entertainment", "pa1688", "pa3288", "pa6488", "palmmicro"); 
var _arNames = new Array("AR1688", "Entertainment", "PA1688", "PA3288", "PA6488", "Palmmicro"); 
var _arCnNames = new Array("AR1688", "娱乐", "PA1688", "PA3288", "PA6488", "Palmmicro"); 

function BlogMenu()
{
    var iLevel = 1;
    
	NavBegin();
	WoodyMenuItem(iLevel, "blog");
	NavContinueNewLine();
    NavMenuSet(_iTotalMenus, _arMenus, _arNames, _arCnNames);
	NavContinueNewLine();
    NavSwitchLanguage(iLevel + 1);
    NavEnd();
}

function BlogMenuItem(iLevel, strItem)
{
	var i;
	
    for (i = 0; i < _iTotalMenus; i ++)
    {
        if (strItem == _arMenus[i])
        {
            if (FileIsEnglish())
            {
            	NavWriteItemLink(iLevel, strItem, FileTypeHtml(), _arNames[i]);
            }
            else
            {
            	NavWriteItemLink(iLevel, strItem, FileTypeCnHtml(), _arCnNames[i]);
            }
        	break;
        }
    }
}


var _arBlogPhotos = new Array("photo2006", "photo2007", "photo2008", "photo2009", "photo2010", "photo2011", "photo2012", "photo2013", "photo2014", "photo2016"); 
var _iTotalBlogPhotos = 10;

function NavLoopBlogPhoto()
{
    var iLevel = 1;
    
	NavBegin();
	WoodyMenuItem(iLevel, "blog");
	NavContinue();
	WoodyMenuItem(iLevel, "image");
	NavContinueNewLine();
    NavDirLoop(_iTotalBlogPhotos, _arBlogPhotos);
	NavContinueNewLine();
    NavSwitchLanguage(iLevel + 1);
    NavEnd();
}
