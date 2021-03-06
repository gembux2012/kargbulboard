CKEDITOR.editorConfig = function( config ) {
    config.disableNativeSpellChecker = false;
    config.removePlugins = 'liststyle,tabletools,scayt,contextmenu';
    config.removePlugins = 'elementspath';
    config.allowedContent = true;
    config.resize_enabled = false;
    config.toolbar='MyConf';
    config.enterMode = CKEDITOR.ENTER_BR;
    config.toolbar_MyConf = [
        { name: 'document', items : [ 'Undo','Redo'] },
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline', ] },
        { name: 'paragraph', items : [ 'NumberedList','BulletedList','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock' ] },

    ];

    //config.extraPlugins = 'onchange';
   // config.minimumChangeMilliseconds = 100;
   // config.extraPlugins = 'autogrow';

};