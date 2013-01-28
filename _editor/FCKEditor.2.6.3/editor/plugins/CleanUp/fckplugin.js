
FCKCommands.RegisterCommand( 'CleanUp', new FCKDialogCommand( 'CleanUp', FCKLang.CleanUpDlgTitle, FCKPlugins.Items['CleanUp'].Path + 'CleanUp.html',540,440));
var oCleanUpItem = new FCKToolbarButton( 'CleanUp', FCKLang.CleanUpDlgTitle ) ;
oCleanUpItem.IconPath = FCKPlugins.Items['CleanUp'].Path + 'CleanUp.gif' ;
FCKToolbarItems.RegisterItem( 'CleanUp', oCleanUpItem ) ;
