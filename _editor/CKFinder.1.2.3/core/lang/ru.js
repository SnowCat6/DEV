/*
 * CKFinder
 * ========
 * http://www.ckfinder.com
 * Copyright (C) 2007-2008 Frederico Caldeira Knabben (FredCK.com)
 *
 * The software, this file and its contents are subject to the CKFinder
 * License. Please read the license.txt file before using, installing, copying,
 * modifying or distribute this file or part of its contents. The contents of
 * this file is part of the Source Code of CKFinder.
 *
 * ---
 * English language file.
 */

var CKFLang =
{

Dir : 'ltr',
HelpLang : 'ru',

// Date Format
//		d    : Day
//		dd   : Day (padding zero)
//		m    : Month
//		mm   : Month (padding zero)
//		yy   : Year (two digits)
//		yyyy : Year (four digits)
//		h    : Hour (12 hour clock)
//		hh   : Hour (12 hour clock, padding zero)
//		H    : Hour (24 hour clock)
//		HH   : Hour (24 hour clock, padding zero)
//		M    : Minute
//		MM   : Minute (padding zero)
//		a    : Firt char of AM/PM
//		aa   : AM/PM
DateTime : 'd.m.yyyy H:MM',
DateAmPm : ['AM','PM'],

// Folders
FoldersTitle	: 'Папки',
FolderLoading	: 'Загрузка...',
FolderNew		: 'Пожалуйста введите имя новой папки: ',
FolderRename	: 'Пожалуйста введите новое имя папки: ',
FolderDelete	: 'Вы согласны удалить папку "%1"?',
FolderRenaming	: ' (Переименование...)',
FolderDeleting	: ' (Удаление...)',

// Files
FileRename		: 'Пожалуйста введите новое имя файла: ',
FileRenameExt	: 'Вы уверены изменить расширение файла? Фйал может быть испорчен',
FileRenaming	: 'Переименование...',
FileDelete		: 'Вы согласны удалить файл "%1"?',

// Toolbar Buttons (some used elsewhere)
Upload		: 'Загрузка',
UploadTip	: 'Загрузка нового файла',
Refresh		: 'Обновить',
Settings	: 'Настройки',
Help		: 'Помощь',
HelpTip		: 'Помощь',

// Context Menus
Select		: 'Выбрать',
View		: 'Посмотреть',
Download	: 'Загрузить',

NewSubFolder	: 'Новая папка',
Rename			: 'Переименовать',
Delete			: 'Удалить',

// Generic
OkBtn		: 'OK',
CancelBtn	: 'Отменить',
CloseBtn	: 'Закрыть',

// Upload Panel
UploadTitle			: 'Загрузить новый файл',
UploadSelectLbl		: 'Выберите файл для загрузки',
UploadProgressLbl	: '(Загрузка продолжается, подождите...)',
UploadBtn			: 'Загрузить выбранный файл',

UploadNoFileMsg		: 'Пожалуйста, выберите файл на Вашем компютере',

// Settings Panel
SetTitle		: 'Настройки',
SetView			: 'Вид:',
SetViewThumb	: 'Эскизы',
SetViewList		: 'Список',
SetDisplay		: 'Отображение:',
SetDisplayName	: 'Имя файла',
SetDisplayDate	: 'Дата',
SetDisplaySize	: 'Размер файла',
SetSort			: 'Сортировать:',
SetSortName		: 'По имени',
SetSortDate		: 'По дате',
SetSortSize		: 'По размеру',

// Status Bar
FilesCountEmpty : '<Пустая папка>',
FilesCountOne	: '1 файл',
FilesCountMany	: '%1 файлов',

// Connector Error Messages.
ErrorUnknown : 'Невозможно завершить запрос. (Error %1)',
Errors : 
{
 10 : 'Invalid command.',
 11 : 'The resource type was not specified in the request.',
 12 : 'The requested resource type is not valid.',
102 : 'Invalid file or folder name.',
103 : 'It was not possible to complete the request due to authorization restrictions.',
104 : 'It was not possible to complete the request due to file system permission restrictions.',
105 : 'Invalid file extension.',
109 : 'Invalid request.',
110 : 'Unknown error.',
115 : 'A file or folder with the same name already exists.',
116 : 'Folder not found. Please refresh and try again.',
117 : 'File not found. Please refresh the files list and try again.',
201 : 'A file with the same name is already available. The uploaded file has been renamed to "%1"',
202 : 'Invalid file',
203 : 'Invalid file. The file size is too big.',
204 : 'The uploaded file is corrupt.',
205 : 'No temporary folder is available for upload in the server.',
206 : 'Upload cancelled for security reasons. The file contains HTML like data.',
500 : 'The file browser is disabled for security reasons. Please contact your system administrator and check the CKFinder configuration file.',
501 : 'The thumbnails support is disabled.'
},

// Other Error Messages.
ErrorMsg :
{
FileEmpty		: 'Имя файла не может быть пустым',
FolderEmpty		: 'Имя папки не может быть пустым',

FileInvChar		: 'Имя файла не может содержать знаки: \n\\ / : * ? " < > |',
FolderInvChar	: 'Имя папки не может содержать знаки: \n\\ / : * ? " < > |',

PopupBlockView	: 'Невозможно открыть окно просмотра. Настройте Ваш браузер на разрешение открытия всплывающих окон.'
}

} ;
